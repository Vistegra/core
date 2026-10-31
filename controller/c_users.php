<?php  if ( !defined('MAIN_ACCESS')) die('access denied!');
/**
 * @var object $main global
 * @var string $pathTarget
 */

$field = [ 'pageTitle' => 'Пользователи' ];

if(!isset($setting)) {
  $columns = $main->db->loadUsers(0, 1);

	if(count($columns) === 1) {
    unset($columns[0]['permission_id']);
    $columns = array_keys($columns[0]) ?: [];
    $columns = array_map(function ($item) {
      $dbName = $item;

      $item = [
        'dbName' => $dbName,
        'name' => gTxtDB('users', $dbName),
      ];

      return $item;
    }, $columns);
	}
}

$permission = $main->db->selectQuery('permission', ['ID', 'name']);

$param['permission'] = $permission = implode('', array_map(function ($item) {
  return "<option value=" . $item['ID'] . ">" . gTxt($item['name']) . "</option>";
}, $permission));

$managerField = $main->getSettings('managerSetting');
if (!$managerField) $managerField = [];

$param['columns'] = $columns ?? '';
require $pathTarget;
$html = template('base', $field);
