<?php

/**
 * @var $main {class} - global from
 */

if (!defined('MAIN_ACCESS')) die('access denied!');

!isset($authAction) && ($authAction = 'noAuthAction');
!isset($login) && ($login = '');
!isset($password) && ($password = '');

switch ($authAction) {
  case 'login':
    if ($user = $main->db->checkPassword($login, $password)) {
      $_SESSION['login']    = $login;
      $_SESSION['password'] = $password;
      $_SESSION['name']     = $user['name'];
      $_SESSION['priority'] = $user['ID']; //типа маскирую
      $_SESSION['id']       = $_COOKIE['PHPSESSID'];

      $_SESSION['hash'] = password_hash($_COOKIE['PHPSESSID'] . $password, PASSWORD_BCRYPT);
      /*$main->getSettings('onlyOne') && */$main->db->setUserHash($user['ID'], $_SESSION['hash']);

      reDirect(true, (isset($clientPageTarget) && $clientPageTarget !== 'login') ? $clientPageTarget : '');
    } else reDirect(false, "login?status=error&login=$login");
    break;
  case 'exit':
    if (isset($_SESSION['priority'])) {
      $hash = password_hash(uniqid(), PASSWORD_BCRYPT);
      /*$main->getSettings('onlyOne') &&*/$main->db->setUserHash($_SESSION['priority'], $hash);
      $_SESSION['password'] = uniqid();
      $_SESSION['target'] = '';
      reDirect(false);
    }
    break;

}
