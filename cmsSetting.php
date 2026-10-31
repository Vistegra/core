<?php //if ( !defined('MAIN_ACCESS')) die('access denied!');

/**
 * @var array $publicConfig - config from public
 */

$absPath = strtolower(str_replace('\\', '/', ABS_SITE_PATH));
$siteDir = str_replace(strtolower($_SERVER['DOCUMENT_ROOT']), '/', $absPath);
define('SITE_PATH', str_replace('//', '/', $siteDir));

require ABS_SITE_PATH . 'config.php'; // Public config

!isset($dbConfig) && $dbConfig = [];

define('CORE', basename( __DIR__ ) . '/');
const VIEW = CORE . 'views/';

const CORE_CSS = SITE_PATH . CORE . 'assets/css/';
const CORE_SCRIPT = SITE_PATH . CORE . 'assets/js/';

const SETTINGS_PATH = ABS_SITE_PATH . 'shared/settingSave.json';
const CSV_CACHE_FILE = ABS_SITE_PATH . 'shared/csvCache.bin';
const SYSTEM_PATH = ABS_SITE_PATH . 'shared/system.php';

!$publicConfig['PUBLIC_PAGE'] && define('ONLY_LOGIN', true);

foreach ($publicConfig as $k => $v) {
  if (!defined($k)) define($k, $v);
}

// Не использую цикл т.к. куча предупреждений
!defined('PROJECT_TITLE') && define('PROJECT_TITLE', false);
!defined('DEBUG') && define('DEBUG', false);
!defined('CSV_DEVELOP') && define('CSV_DEVELOP', false);
!defined('PUBLIC_PAGE') && define('PUBLIC_PAGE', false);
!defined('ONLY_LOGIN') && define('ONLY_LOGIN', false);
!defined('USERS_ORDERS') && define('USERS_ORDERS', false);
!defined('PATH_LEGEND') && define('PATH_LEGEND', false);
!defined('USE_DATABASE') && define('USE_DATABASE', true);
!defined('HOME_PAGE') && define('HOME_PAGE', PUBLIC_PAGE ?? ACCESS_MENU[0]);
!defined('ACCESS_MENU') && define('ACCESS_MENU', []);
!defined('PRINT_BTN') && define('PRINT_BTN', 1);
!defined('SHARE_DIR') && define('SHARE_DIR', '/');
!defined('OUTSIDE') && define('OUTSIDE', isset($_GET['outside']));

require_once CORE . 'model/func.php';
require_once CORE . 'model/classes/Main.php';
require_once CORE . 'model/hooks.php';

// Public php.
if(file_exists(ABS_SITE_PATH . 'public/hooks.php')) require_once ABS_SITE_PATH . 'public/hooks.php';

unset($absPath, $siteDir, $publicConfig, $k, $v);
