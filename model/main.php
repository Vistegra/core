<?php if ( !defined('MAIN_ACCESS')) die('access denied!');

/**
 * @var $main - global
 */
extract($_REQUEST);
$result = [];
$mode = $mode ?? 'noMode';
$dbAction = $dbAction ?? 'noAction';

if ($main->checkAction($dbAction) || $mode === 'auth') {
  try {
    switch ($mode) {
      case 'auth':
        require_once 'auth.php';
        break;
      case 'load':
      case 'DB':
        require_once 'db.php';
        break;
      case 'docs':
        require_once 'docs.php';
        break;
      case 'FM':
        require_once 'fileManager.php';
        break;
      case 'setting':
        require_once 'setting.php';
        break;
    }

    $result['error'] = checkError($result);
    $result['status'] = empty($result['error']);
    if ($result['status']) unset($result['error']);

  } catch (\mysql_xdevapi\Exception $e) {
    echo $e->getMessage();
  }
} else $result['error'] = ['Auth no passing!'];

echo json_encode($result);
