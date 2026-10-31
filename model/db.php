<?php use RedBeanPHP\Db;

/**
 * @var array $dbConfig - config from public
 * @var object $main
 */

if (!defined('MAIN_ACCESS')) die('access denied!');

$db = $main->getDB();

!isset($dbAction) && $dbAction = '';
isset($tableName) && $dbTable = $tableName;
!isset($dbTable) && $dbTable = '';

stripos($dbTable, '.csv') === false && $dbTable = basename($dbTable);

if ($dbAction === 'tables') { // todo добавить фильтрацию таблиц
  CHANGE_DATABASE && $result[$dbAction] = $db->getTables();
  $result['csvFiles'] = $db->scanDirCsv(PATH_CSV);
} else {

  $columns = [];
  if ($dbTable) {
    if (stripos($dbTable, '.csv')) $db->setCsvTable($dbTable);
    else if (USE_DATABASE) {
      $columns = $db->getColumnsTable($dbTable);
      if (!count($columns)) {
        $dbTable = $db->getTables($dbTable);
        if (count($dbTable)) {
          $dbTable = $dbTable[0]['dbTable'];
          $columns = $db->getColumnsTable($dbTable);
        }
      }
    }
  }

  $pageNumber = $currPage ?? 0;
  !isset($countPerPage) && $countPerPage = 20;
  $sortDirect = isset($sortDirect) && $sortDirect === 'true';

  switch ($dbAction) {
    // Tables
    case 'showTable':
      $result['columns'] = $columns;
      if (is_string($dbTable) && stripos($dbTable, '.csv')) $result['csvValues'] = $db->openCsv();
      else {
        if (CHANGE_DATABASE) {
          USE_DATABASE && $result['dbValues'] = $db->loadTable($dbTable);
        } else {
          $dbTable = $db->getTables($dbTable);
          count($dbTable) && $result['dbValues'] = $db->loadTable($dbTable[0]['dbTable']);
        }
      }
      break;
    case 'saveTable':
      if (isset($dbData) && !empty($dbData)) {
        $column = array_filter($columns, function ($col) { return $col['key'] === 'PRI'; });
        $priColumn = count($column) ? $column[0]['columnName'] : false;

        $dbDataOld = $db->loadTable($dbTable);
        $dbData = json_decode($dbData, true);

        $find = function ($array, $testRow) use ($priColumn) {
          return array_values(array_filter($array, function ($row) use ($priColumn, $testRow) {
            return $row[$priColumn] === $testRow[$priColumn];
          }));
        };

        // Delete
        $deleted = [];
        foreach ($dbDataOld as $index => $rowOld) {
          $have = $find($dbData, $rowOld);
          if (!count($have)) {
            $deleted[] = $rowOld[$priColumn];
            array_splice($dbDataOld, $index, 1);
          }
        }
        count($deleted) && $result['deleteCount'] = $db->deleteItem($dbTable, $deleted, $priColumn);

        // Insert & Update
        $added = [];
        $changed = [];
        foreach ($dbData as $row) {
          $have = $find($dbDataOld, $row);
          if (count($have)) {
            $dif = array_diff_assoc($row, $have[0]);
            !empty($dif) && $changed[$row[$priColumn]] = $row;
          } else {
            $added[] = $row;
          }
        }
        count($added) && $result['insert'] = $db->insert($columns, $dbTable, $added);
        count($changed) && $result['change'] = $db->insert($columns, $dbTable, $changed, true);
      }
      else if (isset($csvData) && !empty($csvData)) {
        $db->saveCsv(json_decode($csvData));
      }
      break;
    case 'loadCVS': $db->fileForceDownload(); break;
    case 'loadFormConfig':
      if (isset($dbTable)) {
        $filePath = PATH_CSV . '../xml' . str_replace('csv', 'xml', $dbTable);
        if (file_exists($filePath) && filesize($filePath) > 60) {
          $result['csvValues'] = $db->openCsv();
          $result['XMLValues'] = new SimpleXMLElement(file_get_contents($filePath));
        } else $result['error'] = 'File error';
      }
      break;
    case 'saveXMLConfig':
      if (isset($dbTable) && isset($XMLConfig)) {
        $result['error'] = Xml::saveXml($dbTable, json_decode($XMLConfig, true));
      }
      break;
    case 'loadXmlConfig':
      if (isset($dbTable)) {
        $filePath = PATH_CSV . '../xml' . str_replace('csv', 'xml', $dbTable);
        if (file_exists($filePath)) {
          if (filesize($filePath) < 60) Xml::createXmlDefault($filePath, substr($dbTable, 1));
          $result['XMLValues'] = new SimpleXMLElement(file_get_contents($filePath));
        }
      }
      break;
    case 'refreshXMLConfig':
      if (isset($dbTable)) {
        $filePath = PATH_CSV . '../xml' . str_replace('csv', 'xml', $dbTable);
        Xml::createXmlDefault($filePath, substr($dbTable, 1));
        $result['XMLValues'] = new SimpleXMLElement(file_get_contents($filePath));
      }
      break;

    // Orders
    case 'saveOrder':
      if (isset($reportVal)) {
        $customerId = intval($customerId ?? 0);
        $customerChange = $customerId === 0 ? 'add' : ($customerChange ?? 'add'); // add/change

        if ($customerChange) {
          $param = [$customerId => [
            'name' => $name ?? 'No name',
            'ITN'  => $ITN ?? '',
            'contacts' => json_encode([
              'phone'   => $phone ?? '',
              'email'   => $email ?? '',
              'address' => $address ?? '',
            ]),
          ]];

          $columns = $db->getColumnsTable('customers');
          $result['error'] = $db->insert($columns, 'customers', $param, $customerChange === 'change');
          $customerChange === 'add' && $customerId = $db->getLastID('customers');
        }

        $orderId = intval($orderId ?? 0);
        $orderTotal = $orderTotal ?? 0;

        $param = [$orderId => [
          'user_id'     => $main->getLogin('id'),
          'customer_id' => $customerId,
          'total'       => floatval(is_numeric($orderTotal) ? $orderTotal : 0),
          'important_value' => $importantVal ?? '{}',
          'status_id'       => $statusId ?? 1, // по умолчанию сохранять из настроек
          'save_value'      => $saveVal ?? '{}',
          'report_value'    => addCpNumber($orderId, $reportVal),
        ]];

        $columns = $db->getColumnsTable('orders');
        $result['error'] = $db->insert($columns, 'orders', $param, $orderId !== '0');

        $result['customerId'] = $customerId;
        $result['orderId']    = $db->getLastID('orders');
        $result['saveDate']   = date('Y-m-d H:i:s');
      }
      break;
    case 'loadOrders':
      !isset($sortColumn) && $sortColumn = 'create_date';

      $search = isset($search);
      $orderIds = isset($orderIds) ? json_decode($orderIds) : []; // TODO Зачем это
      $dateRange = isset($dateRange) ? json_decode($dateRange) : [];

      // Значит нужны все заказы (поиск)
      if ($countPerPage > 999) $countPerPage = 1000000;
      else $result['countRows'] = $db->getCountRows('orders');

      $result['orders'] = $db->loadOrder($pageNumber, $countPerPage, $sortColumn, $sortDirect, $dateRange, $orderIds);
      !$search && $result['statusOrders'] = $db->loadTable('order_status');
      break;
    case 'loadOrder': // Показать подробности
      $orderIds = isset($orderIds) ? json_decode($orderIds) : [];
      if (count($orderIds) === 1) $result['order'] = $db->loadOrderById($orderIds[0]);
      break;
    case 'changeStatusOrder':
      if (isset($commonValues) && isset($statusId) && count($columns)) {

        if (!is_numeric($statusId)) break;

        $commonValues = json_decode($commonValues);

        $db->changeOrders($columns, $dbTable, $commonValues, $statusId);
      }
      break;
    case 'delOrders':
      $orderIds = isset($orderIds) ? json_decode($orderIds) : [];
      if (count($orderIds)) $db->deleteItem('orders', $orderIds);
      break;

    // VisitorOrders
    case 'saveVisitorOrder':
      if (isset($inputValue)) {
        $param = [
          'cp_number'   => isset($cpNumber) ? $cpNumber : time(),
          'input_value' => $inputValue,
          'total'       => isset($total) ? $total : 0,
        ];

        isset($importantValue) && $importantValue !== 'false' && $param['importantValue'] = $importantValue;

        $db->saveVisitorOrder($param);
      }
      break;
    case 'loadVisitorOrders':
      !isset($sortColumn) && $sortColumn = 'createDate';

      $search = isset($search);
      // Значит нужны все заказы (поиск)
      if ($countPerPage > 999) $countPerPage = 1000000;
      else $result['countRows'] = $db->getCountRows('client_orders');

      $result['orders'] = $db->loadVisitorOrder($pageNumber, $countPerPage, $sortColumn, $sortDirect);
      break;
    case 'loadVisitorOrder': break;
    case 'delVisitorOrders':
      $orderIds = isset($orderIds) ? json_decode($orderIds) : [];
      if (count($orderIds)) $db->deleteItem('client_orders', $orderIds);
      break;

    // Section
    case 'createSection':
      $param = ['0' => []];
      $param['0']['parent_ID'] = $parentId ?? 0;
      if (isset($name) && isset($code) && !empty($name)) {
        $haveSection = $db->selectQuery('section', 'name', " parent_ID = $parentId");
        if (in_array($name, $haveSection)) {
          $result['error'] = 'section_exist';
        } else {
          $param['0']['name'] = $name;
          $param['0']['code'] = $code;
          $param['0']['active'] = intval(isset($activity) && $activity === "true");
          $result['error'] = $db->insert([], 'section', $param);
        }
      }
      break;
    case 'openSection':
      if (isset($sectionId) && is_numeric($sectionId)) {
        $result['countRowsElements'] = $db->getCountRows('elements', " section_parent_id = $sectionId");
        $result['elements'] = $db->loadElements($sectionId, $pageNumber, 10000);
      }
      break;
    case 'loadSection':
      $result['section'] = array_map(function ($row) {
        $row['key']      = intval($row['ID']);
        $row['parentId'] = intval($row['parent_ID']);
        $row['label']    = $row['name'];
        $row['activity'] = boolval($row['active']);
        unset($row['ID'], $row['parent_id'], $row['name'], $row['active']);
        return $row;
      }, $db->selectQuery('section'));
      break;
    case 'changeSection':
      $parentId = $parentId ?? 0;
      $name = $name ?? false;
      $code = $code ?? false;
      $param[$sectionId ?? 'error'] = [
        'parent_ID' => $parentId,
        'name'      => $name,
        'code'      => $code ?: $name,
        'active'    => intval(isset($activity) && $activity === "true"),
      ];
      if (!$param['error']) {
        if (empty($name)) { $result['error'] = 'name_error'; break; }

        $result['error'] = $db->insert($db->getColumnsTable('section'), 'section', $param, true);
      }
      break;
    case 'deleteSection':
      if (isset($sectionId)) {
        $ids = [$sectionId];
        $ids = array_merge($ids, $db->selectQuery('section', 'ID', " parent_ID = $sectionId "));
        $db->deleteItem('section', [$sectionId]);
      }
      break;

    // Elements
    case 'createElement':
    case 'copyElement':
      $param = ['0' => []];
      if (!isset($sectionId)) { $result['error'] = 'section_id_error'; break; }

      $param['0']['section_parent_id'] = $sectionId;
      if (isset($name) && isset($type) && !empty($name)) {
        $haveElements = $db->selectQuery('elements', 'name', " name = '$name' ");
        if (count($haveElements)) { $result['error'] = 'element_name_exist'; break; }

        $param['0']['name'] = $name;
        $param['0']['element_type_code'] = $type;
        $param['0']['activity'] = intval(isset($activity) && $activity === "true");
        $param['0']['sort'] = $sort ?? 100;
        $result['error'] = $db->insert($columns, 'elements', $param);

        // Проверка на срабатываение триггера
        $haveOptions = $db->selectQuery('options_elements', 'ID', " name = '$name' ");
        if (!count($haveOptions)) {
          $columns = $db->getColumnsTable('options_elements');
          $elementsId = $db->getLastID('elements');
          $param = ['0' => [
            'element_id' => $elementsId,
            'name'       => $name,
          ]];
          $result['error'] = $db->insert($columns, 'options_elements', $param);
        }
      }
      break;
    case 'openElement':
      if (isset($elementsId) && is_numeric($elementsId)) {
        $result['countRowsOptions'] = $db->getCountRows('options_elements', " element_id = $elementsId ");
        $result['options'] = $db->openOptions($elementsId);
      }
      break;
    case 'changeElements':
      $elementsId = json_decode($elementsId ?? '[]');
      if (count($elementsId)) {
        $single = count($elementsId) === 1;
        $name = $name ?? '';
        $param = [];

        if ($single) {
          $elements = $db->selectQuery('elements', ['ID', 'name'], " name = '$name' ");
          if (count($elements) > 1 || empty($name)
              || (count($elements) === 1 && $elements[0]['ID'] !== $elementsId[0])) {
            $result['error'] = 'element_name_exist'; break;
          }
        }

        foreach ($elementsId as $id) {
          $param[$id]['section_parent_id'] = $parentId ?? false;
          $single && $param[$id]['name'] = $name;
          $param[$id]['activity'] = intval(isset($activity) && $activity === "true");
          $param[$id]['sort'] = $sort ?? 100;
        }

        $result['error'] = $db->insert($db->getColumnsTable('elements'), 'elements', $param, true);
      }
      break;
    case 'deleteElements':
      $elementsId = json_decode($elementsId ?? '[]');
      count($elementsId) && $db->deleteItem('elements', $elementsId);
      break;
    case 'searchElements':
      if (isset($searchValue)) {
        $result = $db->searchElements($searchValue, $pageNumber, $countPerPage, $sortColumn ?? 'ID', $sortDirect);
      }
      break;

    // Options
    case 'loadOptions':
      $result['options'] = $db->loadOptions(
        isset($filter) ? json_decode($filter, true) : [],
        $pageNumber ?? 0, $countPerPage
      );
      break;
    case 'copyOption':
    case 'createOption':
      $param = ['0' => []];
      if (isset($name) && isset($elementsId) && !empty($name)) {
        $haveOption = $db->selectQuery('options_elements', ['ID', 'name'], " ID = '$elementsId' and name = '$name' ");
        if (count($haveOption)) { $result['error'] = 'option_name_exist'; break; }

        $param['0']['element_id'] = $elementsId;
        $param['0']['name'] = $name;
        $param['0']['money_input_id'] = $moneyInputId ?? 1;
        $param['0']['input_price'] = $inputPrice ?? 0;
        $param['0']['money_output_id'] = $moneyOutputId ?? 1;
        $param['0']['output_percent'] = $outputPercent ?? 0;
        $param['0']['output_price'] = $outputPrice ?? 0;
        $param['0']['unit_id'] = $unitId ?? 1;
        $param['0']['activity'] = intval(isset($activity) && $activity === "true");
        $param['0']['sort'] = $sort ?? 100;
        $param['0']['property'] = $propertyJson ?? '{}';

        $imageIds = [];
        foreach ($_REQUEST as $k => $v) {
          stripos($k, 'files') === 0 && $param['0']['images_id'][] = $v;
        }
        $param['0']['images_ids'] = $db->setFiles($result, $imageIds);

        $result['error'] = $db->insert($db->getColumnsTable('options_elements'), 'options_elements', $param);
      }
      break;
    case 'changeOptions':
      $optionsId = json_decode($optionsId ?? '[]');
      if (count($optionsId)) {
        $param = [];

        foreach ($optionsId as $id) {
          isset($name) && $param[$id]['name'] = $name;
          isset($moneyInputId) && $param[$id]['money_input_id'] = $moneyInputId;
          isset($inputPrice) && $param[$id]['input_price'] = $inputPrice;
          isset($moneyOutputId) && $param[$id]['money_output_id'] = $moneyOutputId;
          isset($outputPercent) && $param[$id]['output_percent'] = $outputPercent;
          isset($outputPrice) && $param[$id]['output_price'] = $outputPrice;
          isset($unitId) && $param[$id]['unit_id'] = $unitId;
          isset($sort) && $param[$id]['sort'] = $sort;

          $param[$id]['activity'] = intval(isset($activity) && $activity === "true");

          $imageIds = [];
          foreach ($_REQUEST as $k => $v) {
            stripos($k, 'files') === 0 && $imageIds[] = $v;
          }
          $param[$id]['property'] = $property ?? '{}';

          count($optionsId) === 1 && $param[$id]['images_ids'] = $db->setFiles($result, $imageIds);
        }

        $result['error'] = $db->insert($db->getColumnsTable('options_elements'), 'options_elements', $param, true);
      }
      break;
    case 'deleteOptions':
      $optionsId = json_decode($optionsId ?? '[]');
      if (count($optionsId)) $db->deleteItem('options_elements', $optionsId);
      break;

    // Options property
    case 'loadProperties':
      $setAction = 'loadProperties';
      $result['propertiesTables'] = [];
      $setting = getSettingFile();
      require 'setting.php';

      $dbPropertiesTables = $db->getTables('prop');

      foreach ($dbPropertiesTables as $table) {
        $result['propertiesTables'][$table['dbTable']] = [
          'name' => $setting['propertySetting'][$table['dbTable']]['name'] ?? $table['name'],
          'type' => 'справочник',
        ];
      };
      break;
    case 'loadProperty':
      if (isset($props)) {
        $result['propertyValue'] = $db->getColumnsTable($props);// todo загрузить просто
      }
      break;
    case 'createProperty':
    case 'changeProperty':
      if (isset($tableName) && isset($dataType) && !empty($tableName)) {
        // Простой или сложный параметр по префиксу
        if (stripos($dataType, 's_') === 0) {
          $setAction = $dbAction;
          require 'setting.php';
        } else {
          $tableCode = $tableCode ?? translit($tableName);
          $propName = 'prop_' . str_replace('prop_', '', $tableCode);

          $param = [];
          foreach ($_REQUEST as $key => $value) {
            if (stripos($key, 'colName') !== false) {
              $id = str_replace('colName', '', $key);

              if (isset($_REQUEST['colType' . $id])) {
                $param[translit($value)] = $_REQUEST['colType' . $id];
              }
            }
          }

          $setting = getSettingFile();
          if (!isset($setting['propertySetting'][$propName])) {
            $setting['propertySetting'][$propName]['name'] = $tableName;
            setSettingFile($setting);
            $result['error'] = $db->createPropertyTable($propName, $param);
          } else if ($dbAction === 'changeProperty') {
            $db->delPropertyTable([$propName]);
            $setting['propertySetting'][$propName]['name'] = $tableName;
            setSettingFile($setting);
            $result['error'] = $db->createPropertyTable($propName, $param);
          } else $result['error'] = 'Property exist';
        }
      } else $result['error'] = 'Property name error!';
      break;
    case 'delProperties':
      if (isset($props)) {
        $props = explode(',', $props);
        $setAction = 'delProperties';
        require 'setting.php';
        $db->delPropertyTable($props);
      }
      break;

    // Customers
    case 'loadCustomerByOrder':
      $orderIds = isset($orderIds) ? json_decode($orderIds) : [];
      if (count($orderIds) === 1) {
        $result['customer'] = $db->loadCustomerByOrderId($orderIds[0]);
        $result['users'] = $db->getUserByOrderId($orderIds[0]);
      }
      break;
    case 'loadCustomers':
      !isset($sortColumn) && $sortColumn = 'name';

      //$search = isset($search);
      $customerIds = isset($customerIds) ? json_decode($customerIds) : [];

      // Значит нужны все заказчики(поиск при сохранении)
      if ($countPerPage > 999) $countPerPage = 1000000;
      else $result['countRows'] = $db->getCountRows('customers');

      $result['customers'] = $db->loadCustomers($pageNumber, $countPerPage, $sortColumn, $sortDirect, $customerIds);
      break;
    case 'addCustomer':
    case 'changeCustomer':
      $newCustomer = isset($customerId) && is_numeric($customerId);
      $customerId = $customerId ?? 0;
      $param = [$customerId => []];
      $customer = isset($authForm) ? json_decode($authForm, true) : [];

      $contacts = [];
      foreach ($customer as $k => $v) {
        if ($k === 'cType') continue;
        if (in_array($k, ['name', 'ITN'])) $param[$customerId][$k] = $v;
        else $contacts[$k] = $v;
      }
      count($contacts) && $param[$customerId]['contacts'] = json_encode($contacts);

      $db->insert($columns, 'customers', $param, $newCustomer);
      break;
    case 'delCustomer':
      $usersId = isset($customerId) ? json_decode($customerId) : [];

      if (count($usersId)) {
        $result['customers'] = $db->deleteItem('customers', $usersId);
      }
      break;

    // Permission
    case 'loadPermission': break;

    // Rate
    case 'loadRate':
      $result['rate'] = $db->getMoney();
      break;

    // Users
    case 'loadUsers':
      !isset($sortColumn) && $sortColumn = 'register_date';

      $result['countRows'] = $db->getCountRows('users');
      $result['users'] = $db->loadUsers($pageNumber, $countPerPage, $sortColumn, $sortDirect);
      $result['permissionUsers'] = $db->loadTable('permission');
      break;
    //case 'loadUser': break; // Вероятно для загрузки пароля
    case 'addUser':
      $param = [0 => []];
      $user = isset($authForm) ? json_decode($authForm, true) : [];

      $contacts = [];
      foreach ($user as $k => $v) {
        if (in_array($k, ['login', 'name', 'permissionId'])) $param[0][$k] = $v;
        else if ($k === 'password') $param[0][$k] = password_hash($v, PASSWORD_BCRYPT);
        else $contacts[$k] = $v;
      }
      $param[0]['contacts'] = json_encode($contacts);

      $result['error'] = $db->insert($columns, 'users', $param);
      break;
    case 'changeUser':
      $usersId = isset($usersId) ? json_decode($usersId) : [];
      $authForm = isset($authForm) ? json_decode($authForm, true) : [];

      if (count($usersId)) {
        $param = [];

        foreach ($usersId as $id) {
          $param[$id] = [];
          $contacts = [];
          foreach ($authForm as $k => $v) {
            if (in_array($k, ['login', 'name', 'permission_id'])) $param[$id][$k] = $v;
            else $contacts[$k] = $v;
          }
          count($contacts) && $param[$id]['contacts'] = json_encode($contacts);
          $param[$id]['activity'] = isset($authForm['activity']) ? '1' : '0';
        }

        $result['error'] = $db->insert($columns, 'users', $param, true);
      }
      break;
    case 'changeUserPassword':
      $usersId = isset($usersId) ? json_decode($usersId) : [];

      if (count($usersId) === 1 && isset($validPass)) {
        $param[$usersId[0]]['password'] = password_hash($validPass, PASSWORD_BCRYPT);

        $result['error'] = $db->insert($columns, 'users', $param, true);
      }
      break;
    case 'delUser':
      $usersId = isset($usersId) ? json_decode($usersId) : [];

      if (count($usersId)) {
        $db->deleteItem('users', $usersId);
      }
      break;

    // Files
    case 'loadFiles':
      $result['files'] = $db->loadFiles();
      break;

    case 'openOrders': /* TODO когда это отправляется */
      break;

    default:
      echo 'SWITCH default DB.php' . var_dump($_REQUEST);
      break;
  }

  $db::close();
}
