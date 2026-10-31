<?php  if ( !defined('MAIN_ACCESS')) die('access denied!');

/**
 * @var object $main
 * @var string $pathTarget
 */

$field['pageTitle'] = PROJECT_TITLE;

$field['pageFooter'] = '';
$field['sideLeft']   = '';
$field['content']    = '';
$field['sideRight']  = '';

$wrongPass = $main->checkStatus('error');
$login     = $_REQUEST['login'] ?? '';
$pass      = $_REQUEST['password'] ?? '';

require $pathTarget;
$html = template('base', $field);
