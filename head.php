<?php if ( !defined('MAIN_ACCESS')) die('access denied!');

/**
 * Роутер ядра (восстановлен под PHP 8.4).
 *
 * ?mode=...            - ajax-запросы (auth, DB, docs, FM, setting) -> model/main.php
 * /                    - открытая страница (калькулятор)
 * /login/              - вход в админку
 * /admindb/, /orders/, /customers/, /users/, /calendar/, /setting/ ... - разделы админки
 *
 * @var $main - global
 */

require_once basename( __DIR__ ) . '/cmsSetting.php';

if (isset($_REQUEST['mode'])) {
  require 'model/main.php';
} else {
  $html = '';

  $main->setTarget($_GET);
  $target = $main->getTarget();

  // Открытая страница доступна по /, /public/ и /calculator/
  if ($target === PUBLIC_PAGE || $target === 'index.php') $target = 'public';

  // Если уже авторизован и открыл /login/ - отправить на главную
  $main->checkAuth($target === 'login' ? '' : $target);
  $main->applyAuth($target);
  $main->setAccount();

  $dbTables = $main->getBaseTable() ?? [];
  $pageTarget = $_SESSION['target'] ?? '';
  $pageTarget === 'login' && $pageTarget = '';

  if ($target === 'public') {
    require CORE . 'controller/c_public.php';
  } else if ($target === 'login'
             || ($main->availablePage($target)
                 && file_exists(CORE . "controller/c_$target.php")
                 && file_exists(VIEW . "$target.php"))) {
    $pathTarget = VIEW . "$target.php";
    require CORE . "controller/c_$target.php";
  } else {
    http_response_code(404);
    $html = template('404');
  }

  echo $html;
}

unset($target, $pathTarget, $html, $authStatus, $dbContent, $field, $orderId, $main);
