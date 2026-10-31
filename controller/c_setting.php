<?php if (!defined('MAIN_ACCESS')) die('access denied!');

/**
 * @var object $main
 * @var string $pathTarget
 */

$field = [
  'pageTitle' => 'Настройки',
  'footerContent' => '',
];
$param = [];

if ($main->getSettings('admin')) {
  $fileSetting = getSettingFile(false);
  $field['footerContent'] .= "<input type='hidden' id='userSetting' value='$fileSetting'>";

  if (USE_DATABASE) {
    $permissions = $main->db->loadTable('permission');
    $permIds = [];

    $permissions = array_map(function ($row) use (&$permIds) {
      $permIds[] = $row['ID'];
      $row['name'] = gTxt($row['name']);
      $row['accessVal'] = json_decode($row['access_val'], true);
      unset($row['access_val']);
      return $row;
    }, $permissions);

    $param['permIds'] = implode(',', $permIds);
    $param['permStatus'] = $permissions;
    $permissions = json_encode($permissions);
    $field['footerContent'] .= "<input type='hidden' id='permissionSetting' value='$permissions'>";

    // if available orders
    if ($main->availablePage('orders') && false) {
      $status = json_encode($main->db->loadTable('order_status'));
      $field['footerContent'] .= "<input type='hidden' id='dataOrdersStatus' value='$status'>";
    }

    unset($permIds, $permissions, $status);
  }
}

require $pathTarget;
$html = template('base', $field);
