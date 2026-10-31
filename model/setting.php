<?php if (!defined('MAIN_ACCESS')) die('access denied!');

/**
 * @var $main - global main object
 *
 * @var $setAction - fromQuery
 * @var $orderMail - fromQuery
 * @var $orderMailCopy - fromQuery
 * @var $login - fromQuery
 * @var $password - fromQuery
 * @var $passwordRepeat - fromQuery
 * @var $customization - fromQuery
 */

/**
 * Set manager custom field from Request
 */
function setOrderMailField() {
  define('KEY' , 'orderMail');

  $result = [];

  array_map(function ($key) use (&$result) {
    if (stripos($key, KEY) !== false) {
      $id = str_replace(KEY, '', $key);

      //$fieldKey = preg_replace('/\D\S/g', '', trim($_REQUEST[$key]));
      $fieldKey = trim($_REQUEST[$key]);
      $fieldIndex = translit($fieldKey);
      $fieldType = isset($_REQUEST[TYPE . $id]) ? $_REQUEST[TYPE . $id] : 'string';

      $result[$fieldIndex] = ['name' => $fieldKey, 'type' => $fieldType];
    }
  }, array_keys($_REQUEST));

  return $result;
}

/**
 * Set manager custom field from Request
 */
function setManagerCustomField() {
  define('KEY' , 'mCustomFieldKey');
  define('TYPE' , 'mCustomFieldType');

  $result = [];

  array_map(function ($key) use (&$result) {
    if (stripos($key, KEY) !== false) {
      $id = str_replace(KEY, '', $key);

      //$fieldKey = preg_replace('/\D\S/g', '', trim($_REQUEST[$key]));
      $fieldKey = trim($_REQUEST[$key]);
      $fieldIndex = translit($fieldKey);
      $fieldType = isset($_REQUEST[TYPE . $id]) ? $_REQUEST[TYPE . $id] : 'string';

      $result[$fieldIndex] = ['name' => $fieldKey, 'type' => $fieldType];
    }
  }, array_keys($_REQUEST));

  return $result;
}

$db = $main->getDB();

if (isset($setAction)) {
  switch ($setAction) {
    case 'save':
      $usersId = $priority ?? false;

      // Change Setting
      if (USE_DATABASE && $usersId) {
        $columns = $db->getColumnsTable('users');
        $param[$usersId] = [
          'login'         => $login,
          'customization' => $customization,
        ];

        if ($password && $password === $passwordRepeat) $param[$usersId]['password'] = password_hash($password, PASSWORD_BCRYPT);

        $result['error'] = $db->insert($columns, 'users', $param, true);

        // Доступы
        if (isset($permIds)) {
          $columns = $db->getColumnsTable('permission');
          $param = [];

          foreach (explode(',', $permIds) as $id) {
            $param[$id]['access_val'] = [];
            // Меню
            if (isset($_REQUEST['permMenuAccess_' . $id])) {
              $param[$id]['access_val']['menuAccess'] = $_REQUEST['permMenuAccess_' . $id];
            }

            $param[$id]['access_val'] = json_encode($param[$id]['access_val']);
          }

          $result['error'] = $db->insert($columns, 'permission', $param, true);
        }

        // Статусы
        if (isset($orderStatus)) {
          $columns = $db->getColumnsTable('order_status');
          $currentTable = $db->loadTable('order_status');
          $param = [];
          foreach (json_decode($orderStatus, true) as $status) {
            $param[$status['ID']]['name'] = $status['name'];
          }
          $result['error'] = $db->insert($columns, 'order_status', $param, false);
        }
      } else {
        $hash = '';

        if (isset($password) && isset($passwordRepeat) && $password === $passwordRepeat)
          $hash = password_hash($password, PASSWORD_BCRYPT);

        if(!isset($passwordRepeat) && !file_exists(SYSTEM_PATH)) {
          $password = '123';
          $hash = password_hash($password, PASSWORD_BCRYPT);
        }

        file_put_contents(SYSTEM_PATH, implode('|||', [$login, $password, $hash]));
      }

      // Global mail setting
      // Переписать и согласовать с mail.php загрузка пустые строки не сохранять
      $setting = getSettingFile();
      isset($orderMail) && $setting['orderMail'] = $orderMail;
      isset($orderMailCopy) && $setting['orderMailCopy'] = $orderMailCopy;
      isset($orderMailSubject) && $setting['orderMailSubject'] = $orderMailSubject;
      isset($orderMailFromName) && $setting['orderMailFromName'] = $orderMailFromName;
      !USE_DATABASE && $setting['onlyOne'] = isset($onlyOne);

      // Global manager setting
      $managerSetting = setManagerCustomField();
      count($managerSetting) && $setting['managerSetting'] = $managerSetting;

      setSettingFile($setting);
      break;
    case 'load':
      if (USE_DATABASE) $result['user'] = $db->getUser($main->getLogin(), 'ID, login, customization');
      else $result['user'] = $db->getUserFromFile($main->getLogin(), '', $main->checkStatus('ok'));

      $result['setting'] = getSettingFile();
      break;
    case 'addPermType':
      if (isset($permType)) {
        $dbKey = translit($permType);

        $permissions = $db->loadTable('permission');

        foreach ($permissions as $item) {
          if ($item['name'] === $dbKey) {
            $result['error'] = "Запись $permType существует";
            break;
          }
        }

        if (!isset($result['error'])) {
          $columns = $db->getColumnsTable('permission');
          $param = ['0' => [
            'name' => $dbKey,
            'access_val' => '[]',
          ]];
          $result['error'] = $db->insert($columns, 'permission', $param);

          $filePath = ABS_SITE_PATH . 'lang/dictionary.php';
          if (!count($result['error']['result']) && file_exists($filePath)) {
            $file = file($filePath);
            $file[count($file) - 1] = "  '$dbKey' => '$permType',\r\n];\r\n";
            file_put_contents($filePath, $file);
          }
        }
      }
      break;
    case 'removePermType':
      if (isset($permId)) {
        $result['error'] = $db->deleteItem('permission', [$permId]);
      }
      break;

    case 'createProperty':
    case 'changeProperty':
      if (isset($tableName) && isset($dataType) && !empty($tableName)) {
        $propertySetting = [];
        $setting = getSettingFile();

        $tableCode = $tableCode ?? translit($tableName);
        $propName = 'prop_' . str_replace('prop_', '', $tableCode);
        $dataType = str_replace('s_', '', $dataType);

        if (!isset($setting['propertySetting'][$propName]) || $setAction === 'changeProperty') {
          $setting['propertySetting'][$propName] = [
            'name' => $tableName,
            'type' => $dataType,
          ];
          setSettingFile($setting);
        } else {
          $result['error'] = 'Property exist';
        }
      } else $result['error'] = 'Property name not exist';
      break;
    case 'loadProperties':
      $setting = $setting ?? getSettingFile();
      if (isset($setting['propertySetting'])) {
         $result['propertiesTables'] = array_filter($setting['propertySetting'],
           function ($prop) { return isset($prop['type']);}
         );
      }
      break;
    case 'delProperties':
      if (isset($props) && ($setting = getSettingFile()) && isset($setting['propertySetting'])) {
        $setting['propertySetting'] = array_filter($setting['propertySetting'], function ($item) use ($props) {
          return !in_array($item, $props);
        }, ARRAY_FILTER_USE_KEY);

        setSettingFile($setting);
      }
      break;

  }
}
