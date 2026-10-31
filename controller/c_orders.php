<?php if (!defined('MAIN_ACCESS')) die('access denied!');
/**
 * @var object $main
 * @var array $dbConfig
 * @var string $pathTarget
 */

$field = ['pageTitle' => 'Заказы'];

// получить конфиг текущего пользователя
$setting = $main->db->getUserSetting();
if (!$setting) $setting = json_decode('{}');

if (!isset($setting->ordersColumnsSort)) {
  $columns = array_keys($main->db->loadOrder(0, 1)[0] ?? []);

  $setting->ordersColumnsSort = array_map(function ($item) {
    return [
      'dbName' => $item,
      'name'   => gTxtDB('orders', $item),
    ];
  }, $columns);

  $param['ordersColumns'] = $setting->ordersColumnsSort;
}

if (USERS_ORDERS && !isset($setting->ordersVisitorColumnsSort)) {
  $columns = array_keys($main->db->loadVisitorOrder(0, 1)[0] ?? []);

  $setting->ordersVisitorColumnsSort = array_map(function ($item) {
    return [
      'dbName' => $item,
      'name'   => gTxtDB('visitorOrders', $item),
    ];
  }, $columns);

  $param['ordersVisitorColumns'] = $setting->ordersVisitorColumnsSort;
}

$main->exist('orderTemplate') && $field = doHook('orderTemplate', $field);
require $pathTarget;
$html = template('base', $field);
