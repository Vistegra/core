/**
 * @var process - global process
 */

const path    = require('path'),
      fs      = require('fs'),
      crypto = require('crypto'),

      publicFileName = 'public.php',
      publicDirName = 'public';

/**
 * Получить результирующую папку в зависимости от расположения папки src
 * @returns string
 */
const getPublicPath = () => {
  let cDir    = __dirname,
      scanDir = fs.readdirSync(cDir);

  for (let i = 0; i < 5; i ++) {

    if (scanDir.includes(publicFileName)) {
      return path.resolve(cDir, publicFileName);
    } else if (scanDir.includes(publicDirName)) {
      cDir = path.resolve(cDir, publicDirName);
    } else {
      cDir = path.resolve(cDir, '../');
    }

    scanDir = fs.readdirSync(cDir);
  }

  return '';
}

/**
 * Обновить версию ссылок
 * @param str
 * @returns string
 */
const setVersion = str => {
  let rand = crypto.createHash('md5')
                   .update(Math.random().toString())
                   .digest('hex')
                   .slice(0, 10);
  return str.replace(/\.(css|js)(|.+)'/i, `.$1?ver=${rand}\'`);
}

let publicFilePath = getPublicPath();
if (!publicFilePath) process.exit();

let publicFileContent = fs.readFileSync(publicFilePath, {encoding: 'utf8'}),
    publicFileRow = publicFileContent.split('\n');

publicFileRow = publicFileRow.map((row, i) => {
  if (row.includes('cssLinks') || row.includes('jsLinks')) {
    return setVersion(row);
  }
  return row;
});

fs.writeFileSync(publicFilePath, publicFileRow.join('\n'));
console.log('Version update complete');
