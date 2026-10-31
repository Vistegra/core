<?php if (!defined('MAIN_ACCESS')) die('access denied!');

/**
 * @var string $pathTarget
 */

$field = [
  'pageTitle' => 'Администрирование',
];

$field['cssLinks'] = [CORE_CSS . 'module/admindb.css'];

if (DB_TABLE_IN_SIDEMENU) {
  if (isset($_GET['tableName'])) $tableActive = $_GET['tableName'];
  else {
    global $dbTables;
    // Первая таблица (csv может лежать во вложенных папках)
    $findFirst = function ($tables, $link = '') use (&$findFirst) {
      foreach ((array)$tables as $key => $item) {
        if (!is_numeric($key)) {
          if ($r = $findFirst($item, $link . '/' . $key)) return $r;
        } else if (is_array($item)) {
          return $link . '/' . ($item['fileName'] ?? $item['dbTable'] ?? $item['name']);
        }
      }
      return '';
    };
    $firstTable = $findFirst($dbTables);
    $firstTable !== '' && reDirect(false, 'admindb?tableName=' . $firstTable);
  }
}

if (PATH_LEGEND && file_exists(PATH_LEGEND)) require PATH_LEGEND;

require $pathTarget;
$html = template('base', $field);
