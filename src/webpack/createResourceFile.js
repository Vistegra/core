const fs = require('fs');

const absPath = '../../', // может можно как-то по другому?
      coreBuildPath = '../assets/',
      publicSrcPath = absPath + 'public/src/',
      coreSrcCssModulePath = './css/module/',
      coreSrcJsModulePath = './js/module/',
      resFileName = 'webpackModule.json',
      configName = 'config.php';

/*let json = {
  js: Object.create(null),
  css: Object.create(null),
};*/

//let content = 'const entry = {\n';
let content = Object.create(null);

function isDir(path) {
  return fs.lstatSync(path).isDirectory();
}

function addImport(path, mName, js = false) {
  if (fs.existsSync(path + mName + '/' + mName + '.js')) {
    let dir = fs.readdirSync(path + mName + '/');
    dir.forEach(file => {
      if (isDir(`${path + mName}/${file}`)) addImport(path + mName + '/', file);
      else {
        let str = `${mName}: '${path + mName}/${file}',\n`;
        //if (js) str = str.replace('./js', ''); // непомню зачем
        content += str;
      }
    });
  }
}

function addEntry(path, mName, js = false) {
  if (fs.existsSync(path + mName + '/' + mName + '.js')) {
    content[mName] = `${path + mName}/${mName}.js`;
  }
}

function addModule(mName) {
  if (menu.includes(mName) || mName === 'setting') {
    //addImport(coreSrcCssModulePath, mName);
    addEntry(coreSrcJsModulePath, mName, true);
    console.log('Added module: ' + mName);
  }
}

const config = fs.readFileSync(absPath + configName, {encoding: 'utf8'}),
      configRows = config.split('\n');

let acceptMenu = false, menu;

if (!configRows.length) throw new Error('Not Found config');
acceptMenu = configRows.filter((r) => r.includes('\'ACCESS_MENU\''));
menu = acceptMenu[0].toLowerCase();

// Администрирование БД
let modules = ['admindb', 'calendar', 'catalog', 'customers', 'filemanager', 'orders', 'setting', 'statistic', 'users'];
modules.forEach((name) => {
  addModule(name);
});

fs.writeFileSync(absPath + 'public/' + resFileName, JSON.stringify(content));
console.log('complete');
