<?php

namespace cms;

use Course;
use RedBeanPHP;
use Xml\Xml;

/**
 * @var $dbConfig
 */

/**
 * Trait Authorization
 * @package cms
 */
trait Authorization {

  /**
   * @var string[]
   */
  static $AVAILABLE_ACTION = ['loadCVS', 'saveVisitorOrder', 'openElement', 'loadOptions', 'loadProperties', 'loadProperty', 'loadFiles'];

  private $id, $login, $name;

  /**
   * @var string
   */
  private $status = 'no';

  /**
   * @var array
   */
  private $sideMenu = [];

  /**
   * @var bool
   */
  private $admin = true;

  /**
   * @param $field
   * @return mixed
   */
  public function getLogin(string $field = 'login') {
    return $this->$field;
  }

  /**
   * @param array $session
   * @return $this|Main
   */
  public function setLogin(array $session): Main {
    $this->login = $session['login'];
    $this->name  = $session['name'];
    $this->id    = $session['priority'];
    $this->setLoginStatus('ok');
    return $this;
  }

  /**
   * @param string $status
   *
   * @return bool
   */
  public function checkStatus(string $status = 'ok'): bool {
    return $this->status === $status;
  }

  /**
   * @param string $status
   * @return $this|Main
   */
  public function setLoginStatus(string $status): Main {
    $this->status = $status;
    return $this;
  }

  /**
   * Проверка пароля
   * @param string $target
   * @return $this|Main
   */
  public function checkAuth(string $target = ''): Main {
    $this->setLoginStatus('no');
    session_start();

    if (isset($_SESSION['hash'])
        //&& !$this->checkStatus('error')
        && $_SESSION['id'] === $_COOKIE['PHPSESSID']) {

      if ($this->db->checkUserHash($_SESSION)) {
        $this->setLogin($_SESSION);
        $target === '' && reDirect(true, HOME_PAGE);
      }
    }

    return $this;
  }

  /** Нужна ли регистрация для действия */
  public function checkAction(string $action) {
    return in_array($action, $this::$AVAILABLE_ACTION) ? true : $this->checkAuth('check');
  }

  /**
   *   Перейти на страницу входа(login) если нет регистрации и доступ к открытой странице закрыт
   * или нет регистрации и целевая страница не открыта
   * @param string $target
   * @return $this|Main
   */
  public function applyAuth(string $target = ''): Main {
    if ($this->checkStatus('no') && $target !== 'login'
        && (ONLY_LOGIN || (PUBLIC_PAGE && $target !== 'public'))) {
      //$_SESSION['target'] = !in_array($target , [HOME_PAGE, PUBLIC_PAGE]) ? $target : '';
      $_SESSION['target'] = $target;
      reDirect(false);
    } else if ($target === 'login' && isset($_REQUEST['status'])) $this->setLoginStatus('error');

    session_abort();
    return $this;
  }

  private function setSideMenu() {
    if (USE_DATABASE) {
      $menuAccess = isset($this->getSettings('permission')['menuAccess'])
        ? $this->getSettings('permission')['menuAccess']
        : false;

      $menuAccess = $menuAccess ? explode(',', $menuAccess) : [];
      $this->sideMenu = count($menuAccess) ? $menuAccess : ACCESS_MENU;
    } else {
      $filterMenu = ['orders', 'calendar', 'customers', 'users', 'statistic', 'catalog'];
      $this->sideMenu = array_filter(ACCESS_MENU, function ($m) use ($filterMenu) {
        return !in_array($m, $filterMenu);
      });
    }
    PUBLIC_PAGE && $this->sideMenu = array_merge([PUBLIC_PAGE], $this->sideMenu);
    $this->sideMenu[] = 'setting';
  }

  /**
   * get array of pages
   * @param bool $first
   * @return array|mixed
   */
  public function getSideMenu(bool $first = false) {
    return $first ? array_values($this->sideMenu)[0]
                  : $this->sideMenu;
  }

  /**
   * Check available page
   * @param string $page
   * @return bool
   */
  public function availablePage(string $page): bool {
    return in_array($page, $this->getSideMenu());
  }

}

/**
 * Trait Page
 * @package cms
 */
trait Page {
  private $target;

  /**
   * @return mixed
   */
  public function getTarget() {
    return $this->target;
  }

  /**
   * @param mixed $get
   */
  public function setTarget($get) {
    $this->target = (isset($get['targetPage']) && $get['targetPage'] !== '') ?
      str_replace('/', '', $get['targetPage']) : HOME_PAGE;
  }
}

/**
 * Trait dictionary
 * @package cms
 */
trait Dictionary {

  /**
   * @var string
   */
  private $dictionaryPath = ABS_SITE_PATH . 'lang/dictionary.php';

  private function includeFromSetting() {
    if (isset($this->setting['managerSetting'])) {
      $list = $this->setting['managerSetting'];
      return array_reduce(array_keys($list), function ($r, $k) use ($list) {
        $r[$k] = $list[$k]['name'];
        return $r;
      }, []);
    }
    return [];
  }

  /** Словарь lang/dictionary.php (для gTxt) */
  public function getDictionary(): array {
    $mess = [];
    if (file_exists($this->dictionaryPath)) include $this->dictionaryPath;
    return array_merge($mess, $this->includeFromSetting());
  }

  /** Словарь lang/dbDictionary.php (для gTxtDB) */
  public function getDbDictionary(): array {
    $mess = [];
    $path = ABS_SITE_PATH . 'lang/dbDictionary.php';
    if (file_exists($path)) include $path;
    return $mess;
  }

  public function initDictionary() {
    $mess = [];
    include $this->dictionaryPath;
    $mess = array_merge($mess, $this->includeFromSetting());
    $mess = json_encode($mess);
    return $mess ? "<input type='hidden' id='dictionaryData' value='$mess'>" : '';
  }
}

/** Trait Cache */
trait Cache {

  private $updateTime = 1209600; // 2 Недели.
  /**
   * @var array
   */
  private $cvsVars = ['all'];
  /**
   * @var bool
   */
  private $needCsvCached = false;

  private function checkEditTime() {
    $this->needCsvCached = time() - filemtime(CSV_CACHE_FILE) > $this->updateTime;
  }

  public function setCsvVariable(array $vars) {
    $this->cvsVars = $vars;
    return $this;
  }

  /**
   * @param mixed ...$vars
   * @return bool if loaded, then return true
   */
  public function loadCsvCache(&...$vars) {
    if (file_exists(CSV_CACHE_FILE)) {
      $this->checkEditTime();

      if (!$this->needCsvCached) {
        $data = json_decode(gzuncompress(file_get_contents(CSV_CACHE_FILE)), true);
        $this->setCsvVariable(array_keys($data));
        foreach (array_values($data) as $index => $var) {
          $vars[$index] = $var;
        }
        return true;
      }
    } else {
      $this->needCsvCached = true;
    }
    return false;
  }

  public function saveCsvCache(...$vars) {
    if (file_exists(CSV_CACHE_FILE)) {
      $this->checkEditTime();

    } else {
      $data = [];
      foreach ($this->cvsVars as $index => $key) $data[$key] = $vars[$index];
      file_put_contents(CSV_CACHE_FILE, gzcompress(json_encode($data), 1));
    }
  }

}

/**
 * Trait hooks
 * @package cms
 */
trait Hooks {
  private $hooks = [];

  public function addHook($hookName, $callable) {
    $this->addAction($hookName, $callable);
  }

  public function addAction($hookName, $callable) {
    if (!is_string($hookName) || !is_callable($callable)) return;

    $this->hooks[$hookName] = $callable;
  }

  public function execAction($hookName, ...$args) {
    if ($this->exist($hookName)) {
      $func = $this->hooks[$hookName];

      if (!isset($args) || !is_array($args)) {
        $args = [];
      }

      if (isset($func)) {
        return $func(...$args);
      }
    }
    return false;
  }

  public function exist($hookName) {
    return isset($this->hooks[$hookName]);
  }
}

final class Main {
  use Authorization;
  use Dictionary;
  use Cache;
  use Hooks;
  use Page;

  /**
   * @var array
   */
  private $setting = [];

  /**
   * @var array
   */
  private $dbConfig;

  /**
   * @var array
   */
  private $dbTables = [];

  /**
   * @var RedBeanPHP\Db|null
   */
  private $dbInstance = null;

  public function __construct($dbConfig) {
    $this->dbConfig = $dbConfig;
    $this->loadSetting();
  }

  public function __get($value) {
    if ($value === 'db') {
      if ($this->dbInstance === null) {
        require_once CORE . 'model/classes/Db.php';
        $this->dbInstance = new RedBeanPHP\Db($this->dbConfig);
      }
      return $this->dbInstance;
    }

    return $this->$value;
  }

  /**
   *
   */
  private function loadSetting() {
    $this->setting = [];//getSettingFile();
  }

  private function checkXml() {
    require_once CORE . 'model/classes/Xml.php';
    Xml::checkXml($this->dbTables);
  }

  public function setSettings($key, $value) {
    $this->setting[$key] = $value;
  }

  /**
   * Get one setting or array if have
   * @param $key
   * @return false|mixed|string
   */
  public function getSettings($key) {
    if ($key === 'json') return json_encode($this->setting);
    if (isset($this->setting[$key])) return $this->setting[$key];
    return false;
  }

  /**
   * Установка всех параметров для аккаунта
   * @return $this
   */
  public function setAccount(): Main {
    if ($this->checkStatus('ok')) {
      // Меню
      $this->setSideMenu();

      if (DB_TABLE_IN_SIDEMENU) {
        $dbTables = [];
        if (USE_DATABASE) {
          if (CHANGE_DATABASE) {
            $dbTables = array_merge($dbTables, $this->db->getTables());
          } else if (in_array('catalog', ACCESS_MENU)) {
            $props = array_merge([[
              'dbTable' => 'codes',
              'name' => gTxtDB('codes', 'codes')
            ]], $this->db->getTables('prop'));

            $props = array_map(function ($prop) {
              $setting = $this->getSettings('propertySetting')[$prop['dbTable']] ?? false;
              $setting && $setting['name'] && $prop['name'] = $setting['name'];
              return $prop;
            }, $props);
            $dbTables = array_merge($dbTables, ['z_prop' => $props]);
          }
        }
        $this->dbTables = array_merge($dbTables, $this->db->scanDirCsv(PATH_CSV));
        //$this->checkXml();
      }
    }
    return $this;
  }

  public function getBaseTable(): array {
    return $this->dbTables;
  }

  public function getDB(): RedBeanPHP\Db {
    return $this->db;
  }

  /**
   * @param string $dataId
   * @param bool   $justRate
   * @return string
   * @default $dataId = 'dataRate'
   * @default $justRate = false
   */
  public function getCourse(string $dataId = 'dataRate', bool $justRate = false): string {
    require_once CORE . 'model/classes/Course.php';
    $rate = new Course($this->db);
    $rate = $justRate ? array_map(function ($rate) { return $rate['rate'];}, $rate->rate) : $rate->rate;
    $rate = json_encode($rate);
    return "<input type='hidden' id='$dataId' value='$rate'>";
  }
}

$main = new Main(USE_DATABASE ? $dbConfig : []);
