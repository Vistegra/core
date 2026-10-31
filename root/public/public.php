<?php

if (!isset($field)) $field = [];
/*// Прайс
$param = [
  'id'    => 'id-product',
  'name'  => 'Наименование',
  'mName' => 'Материал',
  'unit'  => 'Ед. изм.',
  'value' => ['Стоимость, руб.', 'float'],
];

$price = loadCVS($param, 'price.csv');

$price = json_encode(array_filter($price, function ($i) {
  return boolval(strlen($i['id']));
}));*/

$field = [];

$field['headContent'] = '<meta name="Public"><meta name="description" content="Public">';
$field['cssLinks']   = [PATH_CSS . 'styles.css'];
$field['jsLinks']    = [PATH_JS . 'calculator.min.js'];
$field['pageTitle']  = 'Public';
$field['pageFooter'] = '';
//$field['pageHeader'] = '';

/*$dbContent = "<input type='hidden' id='dataPrice' value='$price'>" .
             "<input type='hidden' id='dataConfig' value='$config'>";*/
/*
// Курс
$dbContent .= $main->getCourse();
*/
