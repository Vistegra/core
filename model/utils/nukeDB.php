<?php

$dbname  = "countertop";

$confirm = isset($_GET['confirm']);

if ($confirm) {
  require '../php/libs/rb.php'; // LIBS redbean

  $fileSql = array_values(array_filter(scandir($_SERVER['DOCUMENT_ROOT']), function ($file) {
    return strpos($file, '.sql');
  }));

  if(count($fileSql)) $fileSql = $fileSql[0];
  else exit('dump базы не найден');

  $dbloaction = "localhost";
  $dbuser     = "root";
  $dbpasswd   = "";

  R::setup('mysql:host=' . $dbloaction . ';dbname=' . $dbname, $dbuser, $dbpasswd);
  R::fancyDebug(true);

  if (!R::testConnection()) {
    exit ('Не удалось подлючиться к базе данных');
  }
  // УНИЧТОЖИТЬ ВСЁ. ОСТОРОЖНО
  R::nuke();
  $op_data = '';
  $lines = file($_SERVER['DOCUMENT_ROOT'] . '/' . $fileSql);
  foreach ($lines as $line) {
    if (substr($line, 0, 2) == '--' || $line == '') // This IF Remove Comment Inside SQL FILE
      continue;
    $op_data .= $line;
    if (substr(trim($line), -1, 1) == ';') { // Break Line Upto ';' NEW QUERY
      R::exec($op_data); //$conn->query($op_data);
      $op_data = '';
    }
  }

  die();
}
?>
<h2>Внимание! База <?= $dbname; ?> будет уничтожена и импортировано из файла, все изменения потеряны.</h2>
<p>файл *.sql должен лежать в корне сайта</p>
<h3>Создать новую БД из файла?</h3>
<a href="nukeDB.php?confirm=true">Подтвердить</a>

