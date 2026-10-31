<?php if ( !defined('MAIN_ACCESS')) die('access denied!');

/**
 * @var object $main - global
 * @var string $pathTarget
 */

$curYear = date('Y');
$curMonth = date('m');
$daysInMonth = date('t', mktime(0, 0, 0, $curMonth, 1, $curYear));
$dateRange = ["$curYear-$curMonth-01 00:00:01", "$curYear-$curMonth-$daysInMonth 23:59:59"];
$ordersStatus = $main->db->loadTable('order_status');
$orders = $main->db->loadOrder(0, 1000,	'last_edit_date', false, $dateRange);

$field = [];
$field['cssLinks'] = [CORE_CSS . 'module/calendar.css'];
$field['pageTitle'] = 'Календарь';
$field['footerContent'] = '';

if(count($orders)) {
	$orders = json_encode($orders);
	$field['footerContent'] .= "<div hidden id='ordersValue'>$orders</div>";
}

if(count($ordersStatus)) {
  $ordersStatus = json_encode($ordersStatus);
  $field['footerContent'] .= "<div hidden id='ordersStatusValue'>$ordersStatus</div>";
}

$main->exist('calendarTemplate') && $field = doHook('calendarTemplate', $field);
require $pathTarget;
$html = template('base', $field);
