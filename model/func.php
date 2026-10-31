<?php

require_once __DIR__ . '/funcLegacy.php';

function cmsAutoloader(string $class): void
{
  // Public classes
  $path = ABS_SITE_PATH . 'public/model/classes/' . $class . '.php';
  $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
  if (file_exists($path)) {
    require_once $path;
  }

  // Core classes
  else {
    $path = __DIR__ . '/classes/' . $class . '.php';
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $path);
    if (file_exists($path)) require_once $path;
  }
}

function addCpNumber($number, string $importantValue): bool|string
{
  global $main;
  return $main->fireHook(VC::HOOKS_SAVE_ORDER, $number, $importantValue);
}

/**
 * alias for $main->addControllerField('cssLinks')
 *
 * @param string $cssLink - from index.php directory
 * @return Main|bool - false or Main object
 */
function addCssLink(string $cssLink) {
  global $main;

  if ($main instanceof Main) {
    $cssLink = $main->url->getUri(true) . ltrim($cssLink, '/');
    return $main->addControllerField(VC::BASE_CSS_LINKS, $cssLink);
  }

  return false;
}

/**
 * alias for $main->addControllerField('jsLinks')
 *
 * @param string $jsLink - from index.php directory
 * @param ?mixed $position [optional] <p>
 * before - if string - prepend to the beginning. if array - prepend elements to the beginning of an array<br>
 * after - if string - append to the end. if array - append elements to the end of an array<br>
 * @return Main|bool - false or Main object
 */
function addJsLink(string $jsLink, string $position = 'after') {
  global $main;

  if ($main instanceof Main) {
    $jsLink = $main->url->getUri(true) . ltrim($jsLink, '/');
    return $main->addControllerField(VC::BASE_JS_LINKS, $jsLink, $position);
  }

  return false;
}

/**
 * Alias for $main->addHook();
 */
function addHook(string $hookName, callable $callable): void
{
  global $main;
  if (is_object($main) && method_exists($main, 'addHook')) $main->addHook($hookName, $callable);
  else if (is_object($main) && method_exists($main, 'addAction')) $main->addAction($hookName, $callable);
}

function boolValue(mixed $var): bool {
  if (is_bool($var)) return $var;
  if (is_string($var)) {
    return !(empty($var) || $var === '-' || $var === 'false');
  }
  if (is_numeric($var)) {
    return boolval($var);
  }
  return !empty($var);
}

/**
 * For param by load csv
 */
function csvTypeConvert(string $type, mixed $value): float|bool
{
  switch ($type) {
    default: return $value;
    case 'int': case 'integer': return intval($value);
    case 'float': case 'double': return floatval(str_replace(',', '.', $value));
  }
}

function convertToArray(mixed $value): array {
  if (is_array($value)) return $value;
  if (is_string($value)) {
    return array_map(function ($item) { return trim($item); }, explode(',', $value));
  }
  return [];
}

if (!function_exists('compareFiles')) {
  function compareFiles(string $file1, string $file2): bool {
    return file_exists($file1) && file_exists($file2)
      && md5_file($file1) === md5_file($file2);
  }
}

if (!function_exists('de')) {
  function de($var, bool $die = true): void
  {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    if ($die) die();
  }
}

if (!function_exists('def')) {
  function def($var, bool $die = true): void
  {
    if (is_array($var) || is_object($var)) $var = json_encode($var);
    file_put_contents(ABS_SITE_PATH . 'shared/debug.json', $var);
    if ($die) die();
  }
}

function includes(array|string $hayStack, string $search, bool $strict = false): bool {
  if (is_array($hayStack)) {
    foreach ($hayStack as $item) {
      if (includes($item, $search, $strict)) return true;
    }
  } else {
    return $strict ? $hayStack === $search
                   : stripos($hayStack, $search) !== false;
  }
  return false;
}

function getPageAsString(array $data): string {
  $id = 'wrapCalcNode' . uniqid();
  $initJs = $data['initJs'];
  unset($data['initJs']);

  $html = "<div id='$id'><script>window.node = '#$id';";
  $html .= 'window.data = ' . json_encode($data) . '</script>';
  $html .= '<script>' . $initJs . '</script></div>';

  return $html;
}

/**
 * Translate text
 */
function gTxt(string $str): string {
  global $main;
  static $txt;

  if (!$txt) {
    $txt = $main->getDictionary();
  }

  return $txt[$str] ?? $str;
}

/**
 * Translate dataBase text
 */
function gTxtDB(string $db, string $str): string {
  global $main;
  static $txt;

  if (!$txt) {
    $txt = $main->getDbDictionary();
  }

  return $txt[$db][$str] ?? $str;
}


/**
 * Find index of Levenshtein
 *
 * @param string $input - when searched?
 * @param array  $row   - what search? arr of string
 * @param bool   $index - if true return word, default return index position
 *
 * @return int|string - int: return index of position keyword in array
 */
function findWord(string $input, array $row, bool $index = false, bool $strict = false): int|string
{
  $gc = false;
  $shortest = -1;
  $nearestWord = null;

  $input = mb_strtolower($input, 'UTF-8');
  $limit = iconv_strlen($input);
  $limit = $limit <= 3 ? $limit : ceil($limit / 2); // Pass level Limit

  foreach ($row as $key => $cell) {
    $word = trim(mb_strtolower($cell, 'UTF-8'));
    $lev = levenshtein($input, $word);

    if ($lev === 0) {
      $gc = $key;
      $nearestWord = $word;
      break;
    }
    if (!$strict && $lev < $limit && ($lev <= $shortest || $shortest < 0)) {
      $gc = $key;
      $shortest = $lev;
      $nearestWord = $word;
    }
  }

  if ($index) return $nearestWord;

  return $gc;
}

/**
 * Find csv file
 */
function findCsvFile(string $filename): string {
  global $main;

  $path = ABS_SITE_PATH . 'shared/csv/' . $filename; // Main path
  if (file_exists($path)) return $path;

  return '';
}

/**
 * Determines whether a string can be considered JSON or not.
 */
function isJSON(string $value): bool {
  return (
    is_array(json_decode($value, true)) &&
    (json_last_error() == JSON_ERROR_NONE)
  );
}

/**
 * Load csv to array$_FILES['pictureHead']['error']
 *
 * @param array  $dict     - dictionary for search on the key. example: ['name' => 'Имя'].
 * @param string $filename - csv filename with path
 * @param bool   $oneRang  - if true that return one rang array
 * @param bool   $strict   - strict checking of column names
 */
function loadCSV(array $dict, string $filename, bool $oneRang = false, bool $strict = false): array|string
{
  $filename = findCsvFile($filename);
  $result = [];

  if (!count($dict)) return loadFullCSV($filename);

  if (strlen($filename) && ($handle = fopen($filename, "rt")) !== false) {
    if (($data = fgetcsv($handle, CSV_STRING_LENGTH, CSV_DELIMITER, "\"", "\\"))) {
      $keyIndex = [];

      foreach ($dict as $key => $word) {
        if (is_numeric($key)) $key = $word;

        $isArr = is_array($word);
        $i = findWord($isArr ? $word[0] : $word, $data, false, $strict);

        if ($i !== false) $keyIndex[$key] = $isArr ? [$i, $word[1]] : $i;
      }

      if ($oneRang) {
        $item = end($keyIndex);
        $addPos = function ($data) use ($item) { return $data[$item]; };
      } else {

        $addPos = function ($data) use ($keyIndex) {
          $arr = [];
          foreach ($keyIndex as $key => $item) {
            $arr[$key] = is_array($item)
              ? csvTypeConvert($item[1], trim($data[$item[0]]))
              : preg_replace('/^d_/', '', trim($data[$item]));
          }
          return $arr;
        };

      }

      while (($data = fgetcsv($handle, CSV_STRING_LENGTH, CSV_DELIMITER, "\"", "\\")) !== false) {
        $result[] = $addPos($data);
      }
    }
    fclose($handle);
  }
  else return 'File is not exist';

  return $result;
}

function loadFullCSV(string $path): array {
  if ($path !== '' && ($handle = fopen($path, "rt")) !== false) {
    $result = [];

    while (($data = fgetcsv($handle, CSV_STRING_LENGTH, CSV_DELIMITER, "\"", "\\"))) {
      $result[] = array_map(function ($cell) {
        return preg_replace('/^d_/', '', $cell);
      }, $data);
    }
    fclose($handle);
  } else $result['error'] = 'File is not exist';

  return $result;
}

/**
 * Remove folder recursive
 */
if (!function_exists('removeFolder')) {
  function removeFolder(string $dir): bool {
    if (!is_dir($dir)) return false;

    $files = array_diff(scandir($dir), ['.', '..']);

    foreach ($files as $file) {
      if (is_dir("$dir/$file")) removeFolder("$dir/$file");
      else unlink("$dir/$file");
    }

    return rmdir($dir);
  }
}

function setUserLocale(string $lang = 'ru_RU') {
  putenv('LANG=ru_RU.UTF8');
  putenv('LANGUAGE=ru_RU.UTF8');
  setlocale(LC_ALL, $lang . '.UTF8');

  bindtextdomain($lang, './lang');
  textdomain($lang);
}

/**
 * Get template from view directory
 */
function template(string $path = 'base', array $vars = []): string {
  global $main;
  $path .= '.php';
  extract($vars);
  ob_start();

  if (file_exists($path)) include $path; // Absolute path
  else if (file_exists(ABS_SITE_PATH . "public/views/$path")) include ABS_SITE_PATH . "public/views/$path"; // In root public
  else if (file_exists(CORE . "views/$path")) include CORE . "views/$path"; // In core
  else echo 'Template not found: ' . $path;

  return ob_get_clean();
}

function translit(string $value): string {
  $converter = [
    'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
    'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
    'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
    'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
    'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
    'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '',
    'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
  ];

  return strtr(mb_strtolower($value), $converter);
}

/**
 * @param array $config - 'method', 'json' => true (as default) or any, 'json_assoc', 'login', 'password', 'contentType', 'timeout'
 */
function httpRequest(string $url, array $config = [], array $params = []): array|string
{
  $myCurl = curl_init();

  $curlConfig = [
    CURLOPT_URL => $url,
    CURLOPT_SSL_VERIFYPEER => false, // Добавить: Определять есть ли ssl
    CURLOPT_SSL_VERIFYHOST => false, // Добавить: Определять есть ли ssl
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [],
    CURLOPT_TIMEOUT => $config['timeout'] ?? 45,
  ];

  if (isset($config['auth'])) {
    $curlConfig[CURLOPT_HTTPHEADER][] = 'Authorization: ' . $config['auth'];
  }

  else if (isset($config['login']) && isset($config['password'])) {
    $curlConfig[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
    $curlConfig[CURLOPT_USERPWD]  = $config['login'] . ':' . $config['password'];
  }

  if (strtolower($config['method'] ?? 'get') === 'get') {
    $curlConfig[CURLOPT_HTTPGET] = true;
    if (!empty($params)) $curlConfig[CURLOPT_URL] .= '?' . http_build_query($params);
  } else {
    $contentType = $config['contentType'] ?? 'application/json; charset=utf-8';

    $curlConfig[CURLOPT_HTTPGET] = false;
    $curlConfig[CURLOPT_POST] = true;
    $curlConfig[CURLOPT_HTTPHEADER][] = 'Content-Type: ' . $contentType;
    if (is_string($params)) $curlConfig[CURLOPT_HTTPHEADER][] = 'Content-Length: ' . strlen($params);
    $curlConfig[CURLOPT_POSTFIELDS]   = $params;
  }

  if (isset($config['headers'])) {
    $curlConfig[CURLOPT_HTTPHEADER] = array_merge($curlConfig[CURLOPT_HTTPHEADER], $config['headers']);
  }

  curl_setopt_array($myCurl, $curlConfig);
  $response = curl_exec($myCurl);

  if ($error = $response === false) {
    $response = [
      'code' => curl_getinfo($myCurl, CURLINFO_HTTP_CODE),
      'error' => curl_error($myCurl),
    ];
  }

  curl_close($myCurl);

  if ($error === false && ($config['json'] ?? true) === true) {
    $res = json_decode($response, $config['json_assoc'] ?? true);

    return json_last_error() === JSON_ERROR_NONE ? $res : 'Json error: ' . $response;
  }

  return $response;
}

/**---------------------------------------------------------------------------------------------------------------------
 * PHP8 polyfills
 *--------------------------------------------------------------------------------------------------------------------*/

if (!function_exists('str_starts_with')) {
  function str_starts_with(string $haystack, string $needle): bool {
    return strncmp($haystack, $needle, strlen($needle)) === 0;
  }
}

if (!function_exists('trigger_deprecation')) {
  /**
   * Triggers a silenced deprecation notice.
   *
   * @param string $package The name of the Composer package that is triggering the deprecation
   * @param string $version The version of the package that introduced the deprecation
   * @param string $message The message of the deprecation
   * @param mixed  ...$args Values to insert in the message using printf() formatting
   *
   * @author Nicolas Grekas <p@tchwork.com>
   */
  function trigger_deprecation(string $package, string $version, string $message, ...$args): void
  {
    @trigger_error(($package || $version ? "Since $package $version: " : '').($args ? vsprintf($message, $args) : $message), E_USER_DEPRECATED);
  }
}

if (!function_exists('array_find')) {
  /**
   * @param array $array - The array to search.
   * @param callable $callback - The callback to run for each element.
   */
  function array_find(array $array, callable $callback): mixed { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.arrayFound
    foreach ( $array as $key => $value ) {
      if ($callback($value, $key)) return $value;
    }

    return null;
  }
}

date_default_timezone_set('Europe/Moscow');
spl_autoload_register('cmsAutoloader');
