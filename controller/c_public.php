<?php if ( !defined('MAIN_ACCESS')) die('access denied!');

/**
 * @var object $main
 */

$dbContent = "";
$field = ['pageTitle' => PROJECT_TITLE ?? 'Project title'];

require ABS_SITE_PATH . 'public/public.php';

$authStatus = $main->checkStatus('ok');

// Если загрузка
if ($authStatus && isset($_GET['orderId'])) {
  $orderId = $_GET['orderId'];

  if (is_numeric($orderId)) {
    $order = $main->db->loadOrderById($orderId);

    if ($order) {
      $dbContent .= "<input type='hidden' id='dataOrder' value='" . json_encode($order) . "'>";

      $customer = $main->db->loadCustomerByOrderId($order['ID']);
      if ($customer) {
        $dbContent .= "<input type='hidden' id='dataCustomer' value='" . json_encode($customer) . "'>";
      }
    }
  }
}

if ($authStatus && isset($_GET['orderVisitorId'])) {
  $orderId = $_GET['orderVisitorId'];

  if (is_numeric($orderId)) {
    $order = $main->db->selectQuery('client_orders', ['*'], "cp_number = '$orderId'");

    if (count($order) === 1) {
      $dbContent .= "<input type='hidden' id='dataVisitorOrder' value='" . json_encode($order[0]) . "'>";
    }
  }
}

if (!$authStatus) $field['sideLeft'] = '';


require ABS_SITE_PATH . 'public/views/' . PUBLIC_PAGE . '.php';
$html = template(OUTSIDE ? 'outside' : 'base', $field);
