<?php  if ( !defined('MAIN_ACCESS')) die('access denied!');

/**
 * @var object $main
 * @var string $pathTarget
 */

$db = $main->getDB();

function prepareData($data) {
  return json_encode(array_map(function ($item) {
    $item['id'] = $item['ID'];
    unset($item['ID']);
    return $item;
  }, array_values($data)));
}

$field = [
  'pageTitle' => 'Каталог',
  'footerContent' => '',
  'sideRight' => '',
  'cssLinks'      => [CORE_CSS . 'module/catalog.css'],
];

// Загрузка настройки столбцов
$setting = getSettingFile();
$value = $setting['optionsColumn'] ?? 'ID,C.name,E.name,activity,sort,lastEditDate';
$field['footerContent'] .= "<input type='hidden' id='elementsColumn' value='$value'>";
$value = $setting['elementsColumn'] ?? 'ID,images,name,unitId,activity,sort,lastEditDate,moneyInputId,inputPrice,moneyOutputId,outputPercent,outputPrice';
$field['footerContent'] .= "<input type='hidden' id='optionsColumn' value='$value'>";

// Типы товаров
$value = $db->selectQuery('codes', ['symbolCode', 'name']);
$field['footerContent'] .= "<input type='hidden' id='dataCodes' value='" . json_encode($value) . "'>";
// Единиц измерения
$value = $db->selectQuery('units', ['ID', 'name', 'shortName']);
$field['footerContent'] .= "<input type='hidden' id='dataUnits' value='" . prepareData($value) . "'>";
// Валюта
$value = $db->getMoney();
$field['footerContent'] .= "<input type='hidden' id='dataMoney' value='" . prepareData($value) . "'>";

// Все свойства
$value = $main->getSettings('propertySetting');
foreach ($db->getTables('prop') as $table) {
  $name = isset($value[$table['dbTable']]) ? $value[$table['dbTable']]['name'] : $table['name'];

  $value[$table['dbTable']] = [
    'name' => $name,
    'values' => array_map(function ($row) {
      $row['id'] = $row['ID'];
      return $row;
    }, $db->loadTable($table['dbTable'])),
  ];
}
$field['footerContent'] .= "<input type='hidden' id='dataProperties' value='" . json_encode($value) . "'>";

unset($value, $propSetting, $mess);

ob_start();
include $pathTarget;
$field['content'] = ob_get_clean();
$html = template('base', $field);
