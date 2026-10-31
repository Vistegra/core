<?php

$publicConfig = [
  'PROJECT_TITLE' => 'My project',
  // Любое значение включает режим отладки
  //'DEBUG' => true,
  //'MAIL_TARGET_DEBUG' => 'trydim@mail.ru',

  'PATH_CSS' => SITE_PATH . 'public/css/',
  'PATH_IMG' => SITE_PATH . 'public/images/',
  'PATH_JS'  => SITE_PATH . 'public/js/',

  /** Использовать БД для авторизации */
  'USE_DATABASE' => true,

  /** Возможность прямого редактирования БД из админки */
  'CHANGE_DATABASE' => false,

  /** Любое значение отключает защитный режим */
  //'CSV_DEVELOP' => true,
  /** Папка с csv файлами */
  'PATH_LEGEND'       => ABS_SITE_PATH . 'public/views/legend.php',
  'PATH_CSV'          => ABS_SITE_PATH . 'shared/csv/',
  'CSV_STRING_LENGTH' => '1000',
  'CSV_DELIMITER'     => ';',

  /** Отображать таблицы в меню */
  'DB_TABLE_IN_SIDEMENU' => true,

  /** Сохранять пользовательские расчеты */
  'USERS_ORDERS' => true,

  /** Домашняя страница - страница на которую переходит ПОСЛЕ регистрации
   * По умолчанию PUBLIC_PAGE
   * Если нету, тогда на первую открытую ACCESS_MENU
   */
  //'HOME_PAGE' => 'catalog',

  //'ONLY_LOGIN' => true,
  /** Страница для доступа без регистрации: файл/файлы с таким именем должен быть в public/views/
   * Если пользователь не зарегистрирован, переход на эту страницу.
   * (позже) или через запятую если несколько страниц.
   * если false, то константа ONLY_LOGIN всегда true
   */
  'PUBLIC_PAGE' => 'calculator',

  /** Какую библиотеку использовать (добавить в настройки) mpdf, html2pdf */
  // внутри битрикс не доступно mpdf
  'PDF_LIBRARY' => 'mpdf',

  /** Пункты меню какие показывать и последовательность
  'orders', 'calendar', 'customers', 'users', 'statistic', 'admindb', 'catalog', 'fileManager'
  заказы     календарь   клиенты   менеджеры статистика администрирование каталог файловый менеджер
  options - настройки
   */
  'ACCESS_MENU' => ['orders', 'calendar', 'customers', 'users', 'statistic', 'admindb', 'fileManager'],

  /** Папка по умолчанию для файлового менеджера */
  'SHARE_DIR' => 'public/images/',
];

//----------------------------------------------------------------------------------------------------------------------
// DB connect/config
$dbConfig = [
  'dbHost'     => 'localhost',
  'dbName'     => 'dbName',
  /*'dbUsername' => 'dbUser',
  'dbPass'     => 'WHZM4JpunONGycmf'*/
  'dbUsername' => 'root',
  'dbPass'     => ''
];
