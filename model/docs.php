<?php if (!defined('MAIN_ACCESS')) die('access denied!');

/**
 * @var array $main - global
 * @var string $docsAction - from query
 * @var string $docType - from query
 */

$data = [];

// Если есть отчет
$reportVal = isset($reportVal) ? json_decode($reportVal, true) : false;
// Если есть номер заказа
$orderIds = !$reportVal && isset($orderIds) ? json_decode($orderIds) : false;
// Посмотреть номер заказа в отчете
!$orderIds && $orderIds = isset($reportVal['orderId']) ? json_decode($reportVal['orderId']) : false;
$orderIds = is_array($orderIds) && count($orderIds) === 1 ? $orderIds[0] : false;

// Данные о менеджере
if (isset($addManager)) {
  if (isset($reportVal['name'])) {
    $userData = $main->db->getUser($reportVal['name'], 'name, contacts');
  } else {
    $userData = $main->db->getUserById($reportVal['userId'] ?? $main->getLogin('id'));
  }

  if (count($userData)) {
    $userData['contacts'] = json_decode($userData['contacts'], true);
    $data['userData'] = $userData;
  }
  unset($userData);
}

// Данные о клиенте
if (isset($addCustomer)) {
  if (isset($customerId)) {
    $customerData = $main->db->selectQuery('customer', '*', " ID = $customerId");
  } else if (isset($customer)) {
    $customerData = $customer;
  } else {
    $customerData = '';
  }

  if (!empty($customerData)) {
    $userData['contacts'] = json_decode($customerData['contacts'] ?? '{}', true);
    $data['customerData'] = $userData;
  }
  unset($customerData);
}

// Отчет загрузить из БД по ИД
if ($orderIds) {
  $reportVal = $main->db->loadOrderById($orderIds);

  $reportVal['id']           = $reportVal['ID'];
  $reportVal['customerName'] = $reportVal['C.name'];

  $reportVal['contacts']       = json_decode($reportVal['contacts'], true);
  $reportVal['importantValue'] = json_decode($reportVal['importantValue'], true);
  $reportVal['saveValue']      = json_decode($reportVal['saveValue'], true);
  $reportVal['reportValue']    = json_decode($reportVal['reportValue'], true);

  $data = [
    'order'       => $reportVal,
    'reportValue' => &$reportVal['reportValue'],
  ];
} else if ($reportVal) {
  $data['reportValue'] = $reportVal;
}

$docsAction = $docsAction ?? 'mail';
$docType = $docType ?? $docsAction;

if ($docType !== 'mail') {
  require_once 'classes/Docs.php';
  $docs = new Docs($docType, $data, $fileTpl ?? 'default');
}

if (isset($docsAction)) {
  switch ($docsAction) {
    case 'excel':
    case 'pdf':
    case 'print':
      $docType && $result = $docs->getDocs($mailTpl ?? 'mailTpl');
      break;
    case 'mail':
      $docType && $docsPath = $docs->getDocs('save');
      require_once 'classes/Mail.php';
      $mail = new Mail($mailTpl ?? 'mailTpl');
      $param = [
        'name'  => $name ?? '',
        'phone' => $tel ?? $phone ?? '',
        'email' => $email ?? '',
        'info'  => $info ?? '',
        'data'  => $data,
      ];
      count($_FILES) && $mail->addOtherFile($_FILES);
      $mail->prepareMail($param);
      $docType && $mail->addFile($docsPath);
      isset($email) && $mail->addMail($email);
      $result['mail'] = $mail->send();
      break;

    case 'getPrintStyle':
      $fileTpl = isset($fileTpl) ? $fileTpl : 'printTpl.css';

      if (file_exists(ABS_SITE_PATH . 'public/views/docs/' . $fileTpl)) {
        ob_start();
        include(ABS_SITE_PATH . 'public/views/docs/' . $fileTpl);
        $result['style'] = ob_get_clean();
      }
      break;
  }
}
