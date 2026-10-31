/** ----------- Инструкция ------------ */
/**
 * Шрифты
 * для ускорения сборки шрифты прописывать в отдельном файле.
 * перед строкой с правилом содержащим url вставить как комментарий " webpackIgnore: true ",
 * путь относительно собранного файла css
 */

// Константы Денис -----------------------------------------------------------------------------------------------------
/** Поправить описание
 *
 */
const dataMain = './src/templates/main/main.html';

/**-------------------------------------------------------------------------------------------------------------------*/

const path    = require('path'),
      webpack = require('webpack'), // Используется для сборки VUE
      MiniCssExtractPlugin = require('mini-css-extract-plugin');

// Конфиг Дениса
const fs         = require('fs'),
      glob       = require('glob'),
      CopyPlugin = require('copy-webpack-plugin'),

      //HtmlWebpackPlugin = require('html-webpack-plugin');           // В чем разница?
      HtmlMinimizerPlugin = require('html-minimizer-webpack-plugin'),
      CssMinimizerPlugin  = require('css-minimizer-webpack-plugin'),
      ImageminPlugin      = require('imagemin-webpack'),              //
      ImageminWebpWebpackPlugin = require('imagemin-webp-webpack-plugin'), // Конвертер в webp
      TerserPlugin        = require("terser-webpack-plugin"),

      // Утилиты
      { CleanWebpackPlugin } = require('clean-webpack-plugin');

/**
 * Получить результирующую папку в зависимости от расположения папки src
 * @returns string
 */
const getOutputPath = () => {
  const outputPath = '../../' + (path.basename(path.resolve(__dirname, '../../')) !== 'public' ? 'public/' : '');
  return path.resolve(__dirname, outputPath);
}

/**
 * Стандартные
 * @return {object}
 */
const getEntryOutputPoint = () => ({
  entry: {calculator: './js/calculator.js'}, //function: './js/function.js',
  output: {
    path         : getOutputPath(),
    filename     : 'js/[name].js',
    chunkFilename: 'js/[name].chunk.js',
    scriptType   : 'module',
    module       : true,
    libraryTarget: 'module',
  },
})

// Скрипты Денис
/** Поправить описание
 * автоматическая перезагрузка страницы
 */
const liveReloadInit = () => {
  const liveReload = require('livereload');
  liveReload.createServer()
            .watch(path.resolve(__dirname, 'dist/pages/').replace(/\\/g, '/'));
}

/** Поправить описание
 * Читать файл template
 */
const getTemplateData = (pathMain = dataMain) => {
  try {
    return fs.readFileSync(pathMain, 'utf-8');
  } catch (err) { console.error(err); }
}


/** Поправить описание
 * собираем точки входа/выхода
 */
const accumulateEntryOutputPoint = () => {
  const entry = {};
  const outputsPoints = {};

  glob.sync(`./src/pages/**/*_base.js`).forEach(e => {
    const key = e.split('/').pop().split('.')[0];
    //вход
    entry[key] = e;
    //выход
    outputsPoints[key] = e.split('/')
                          .reduce((p, c, i, a) => p + (c === 'src' ? '' : i > a.length - 3 ? '' : '/' + c))
  });
  entry.main = dataMain;

  return {
    entry,
    output: {
      path: `${__dirname}/dist`,
      filename: (name) => {
        if (outputsPoints[name.chunk.name]) {
          return `js/${outputsPoints[name.chunk.name].split('/').slice(-2, -1)[0]}/${name.chunk.name.split('_')[0]}.js`;
        } else return `js/${name.chunk.name}.js`;
      },
    },
  };
}

module.exports = env => {
  const dev = !env.production;
  //process.env.NODE_ENV = dev ? 'development' : 'production'; // зачем это

  dev && liveReloadInit();

  /**
   * Точки входа
   */
  const points = getEntryOutputPoint(); // Обычный
  //const points = accumulateEntryOutputPoint(); // Не обычный

  return {
    mode        : dev ? 'development' : 'production',
    watch       : dev, // слежка за изменениями файлов
    watchOptions: {aggregateTimeout: 300}, // задержка оценки изменений в мс

    entry : points.entry,
    output: points.output,

    experiments: {outputModule: true},

    resolve: {
      alias: {
        vue: dev ? 'vue/dist/vue.esm-bundler.js' : 'vue/dist/vue.esm-browser.prod.js', // VUE alias (возможно стоит писать полностью для редактора)
      }
    },

    devtool: dev ? 'source-map' : false, //source mapping
    optimization: {
      minimize : !dev,
      minimizer: [
        `...`,

        // Плагины Денис
        //new HtmlMinimizerPlugin(),
        //new CssMinimizerPlugin(),
        //new TerserPlugin({extractComments: false,}),

        //сжатие картинок
        /*new ImageminPlugin({
         bail: false, // Ignore errors on corrupted images
         filter: (source, sourcePath) => !sourcePath,
         loader: false,
         imageminOptions: {
         plugins: [
         ['gifsicle', { interlaced: true }],
         ['jpegtran', { progressive: true }],
         ['optipng', { optimizationLevel: 7 }],
         ['svgo', { plugins: [{ removeViewBox: false, },],},],
         ],
         },
         }),*/
        // преобразование png/jpg -> webp
        /*new ImageminWebpWebpackPlugin({
         silent: false, //информация в консоли
         config: [{
         test: /\.(jpe?g|png)/,
         options: {quality:  60}
         }],
         }),*/
      ],
    },
    plugins: [
      new MiniCssExtractPlugin({
        filename: "css/style.css",
        //filename: name => name.chunk.name !== 'main' ? `${outputsPoints[name.chunk.name]}/css/${name.chunk.name.split('_')[0]}.css` : `/css/${name.chunk.name}.css`,
      }),
      //getCopyPlugin(dev),


      new webpack.DefinePlugin({
        // Drop Options API from bundle
        __VUE_OPTIONS_API__  : 'true',
        __VUE_PROD_DEVTOOLS__: 'false',
      }),
    ],
    module: {
      rules: [
        getVueRules(),
        getScssRules(),
        getCssRules(),
        getImageRules(),
        getSVGRules(),
        getFontsRules(),
      ]
    },
  };
};

// ---------------------------------------------------------------------------------------------------------------------
// Правила / Rules
// ---------------------------------------------------------------------------------------------------------------------

// asset/resource - file-loader - в отдельный файл
// asset/inline - url-loader - инлайном базе64
// asset/source - raw-loader - ?
// asset - автоматический выбор от размера по умолчанию 8к

/**
 * Vue
 * @returns Object
 */
const getVueRules = () => ({
  test  : /\.vue$/,
  loader: "vue-loader"
});

/**
 * Scss
 * @returns Object
 */
const getScssRules = () => ({
  test: /\.s[ac]ss$/i,
  use : [
    MiniCssExtractPlugin.loader,
    'css-loader',
    //'postcss-loader',
    'sass-loader',
  ],
});

/**
 * Css
 * @returns Object
 */
const getCssRules = () => ({
  test: /\.css$/i,
  use : [
    MiniCssExtractPlugin.loader,
    'css-loader',
  ],
});

/**
 * Image
 * loader: 'svgo-loader', - какой-то инлайн лоадер
 * @returns Object
 */
const getImageRules = () => ({
  test   : /\.(png|jpe?g|gif|webp)$/i,
  type   : 'asset',
  generator: {
    filename: 'image/[name][ext]',
  },
  parser: {
    dataUrlCondition: {
      maxSize: 8196, // 8kb
    }
  },
});

/**
 * SVG
 * inline
 * @returns Object
 */
const getSVGRules = () => ({
  test: /\.(svg)$/,
  type: 'asset',
  generator: {
    filename: 'svg/[name][ext]',
  },
  parser: {
    dataUrlCondition: {
      maxSize: 8196, // 8kb
    }
  },
});

/**
 * Шрифты
 * @returns Object
 */
const getFontsRules = () => ({
  test   : /\.(ttf|woff|woff2|eot)$/,
  type   : "asset/resource",
  generator: {
    filename: 'fonts/[name][ext]',
  },
});

/** Поправить описание
 * @param dev
 * @return {*}
 */
const getCopyPlugin = dev => new CopyPlugin({
  patterns: [
    {
      from: '**/*.tpl',
      to: '[path][name].html',
      context: 'src/',
      info: {minimized: dev},  //минификация только в прод
      transform(content, dir) {
        const dirName = path.dirname(dir).replace(/\\/g, '/'),
              key = path.basename(dir).split('.')[0],
              oName = `${dirName}/${key}.html`.replace(/src/, 'dist'),
              scr = `${path.relative(dirName, './dist/js').replace(/\\/g, '/')}/`,
              style = `${path.relative(dirName, './dist/css').replace(/\\/g, '/')}/`,
              dataHTML = {};

        //добавить скрипты
        dataHTML['scripts'] = `<script src="${scr}main.js"></script>`;
        dataHTML['scripts'] += `<script defer src="${scr}${path.dirname(oName).split('/').slice(-2, -1)[0]}/${key}.js"></script>`;
        //стили
        dataHTML['styles'] = `<link rel="stylesheet" href="${style}main.css">`;
        dataHTML['styles'] += `<link rel="stylesheet" href="css/${key}.css">`;
        //из файла tpl в объект
        content.toString().replace(/\r\n/g, '').split(';').forEach(item => {
          if (item !== '') {
            item = item.split('::');
            dataHTML[item[0]] = item[1].trim();
          }
        });

        content = dataMain;

        Object.keys(dataHTML).forEach(item => {
          const regexp = new RegExp(`{${item}}`, 'g');
          if (content.includes(`{${item}`)) content = content.replace(regexp, dataHTML[item]);
        })
        return content;
      },
    },
  ]
})
