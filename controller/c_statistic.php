<?php  if ( !defined('MAIN_ACCESS')) die('access denied!');
/**
 * @var string $pathTarget
 */

$field = [
  'pageTitle' => 'Статистика',
];

require $pathTarget;
$html = template('base', $field);
