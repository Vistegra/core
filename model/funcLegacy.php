<?php
/**
 * Функции старого ядра, которых нет в новом func.php.
 * Без них не работает вход в админку и её разделы (PHP 8.4).
 */

if (!function_exists('reDirect')) {
  /**
   * Редирект внутри сайта.
   * @param bool   $status - true: успех (по умолчанию на главную), false: на страницу входа
   * @param string $target - страница, можно с параметрами: 'login?status=error', '404&dbError=true'
   */
  function reDirect(bool $status = true, string $target = ''): void {
    if ($target === '') $target = $status ? '' : 'login';

    // '404&dbError=true' -> '404?dbError=true'
    if (!str_contains($target, '?') && str_contains($target, '&')) {
      $target = preg_replace('/&/', '?', $target, 1);
    }

    // 'page?x=1' -> 'page/?x=1' (как требует .htaccess)
    $parts = explode('?', ltrim($target, '/'), 2);
    $path = $parts[0];
    if ($path !== '' && !str_ends_with($path, '/') && !preg_match('/\.\w+$/', $path)) $path .= '/';
    $url = SITE_PATH . $path . (isset($parts[1]) ? '?' . $parts[1] : '');

    if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
    header('Location: ' . $url);
    exit;
  }
}

if (!function_exists('checkError')) {
  /**
   * Ошибки из результата ajax-запроса.
   * @return array пустой массив, если ошибок нет
   */
  function checkError(array $result): array {
    $error = $result['error'] ?? [];
    if ($error === true || $error === false || $error === null || $error === '' || $error === 0) return [];
    return is_array($error) ? array_values(array_filter($error)) : [$error];
  }
}

if (!function_exists('getSettingFile')) {
  /**
   * Настройки из shared/settingSave.json
   * @param bool $assoc true - массив, false - json-строка
   */
  function getSettingFile(bool $assoc = true): array|string {
    $json = file_exists(SETTINGS_PATH) ? file_get_contents(SETTINGS_PATH) : '';
    $data = $json ? json_decode($json, true) : null;
    if (!is_array($data)) $data = [];
    return $assoc ? $data : json_encode($data, JSON_UNESCAPED_UNICODE);
  }
}

if (!function_exists('setSettingFile')) {
  function setSettingFile(array|string $setting): bool {
    if (is_string($setting)) $setting = json_decode($setting, true) ?? [];
    return file_put_contents(SETTINGS_PATH, json_encode($setting, JSON_UNESCAPED_UNICODE)) !== false;
  }
}

if (!function_exists('loadCVS')) {
  /** Старое имя (с опечаткой) для loadCSV */
  function loadCVS(array $dict, string $filename, bool $oneRang = false, bool $strict = false): array|string {
    return loadCSV($dict, $filename, $oneRang, $strict);
  }
}

if (!function_exists('findingFile')) {
  /**
   * Найти файл в папке (рекурсивно, без учета регистра).
   * @param string $dir  - путь от корня сайта (как PATH_IMG без последнего /)
   * @param string $name - имя файла
   * @return string|false путь от корня сайта или false
   */
  function findingFile(string $dir, string $name): string|false {
    if ($name === '') return false;
    $absDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . ltrim($dir, '/');
    if (!is_dir($absDir)) $absDir = ABS_SITE_PATH . ltrim(str_replace(SITE_PATH, '', $dir . '/'), '/');
    if (!is_dir($absDir)) return false;

    $name = mb_strtolower(basename($name));
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($absDir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
      if ($file->isFile() && mb_strtolower($file->getFilename()) === $name) {
        $rel = substr(str_replace('\\', '/', $file->getPathname()), strlen(rtrim($absDir, '/')));
        return rtrim($dir, '/') . $rel;
      }
    }
    return false;
  }
}

if (!function_exists('doHook')) {
  function doHook(string $hookName, ...$args) {
    global $main;
    return $main->execAction($hookName, ...$args);
  }
}
