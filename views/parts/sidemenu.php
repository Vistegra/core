<?php if (!defined('MAIN_ACCESS')) die('access denied!');
global $main;

$adminMenu = '';
$dbTables = $main->getBaseTable();

if(is_array($dbTables)) {

  function subSideMenuItem($fileName, $name, $active) {
    $sitePath = SITE_PATH . "admindb?tableName=" . $fileName;
    return <<<item
      <li class="nav-item $active">
        <a class="nav-link pl-5" href="$sitePath">$name</a>
      </li>
item;
  }

  function subSideMenu($title, $item, $root) {
    $icon = $root ? 'fa-database' : 'fa-arrow-right';
    $idWrap = $root ? 'id="DBTablesWrap"' : '';
    return <<<menu
      <a class="nav-link" href="#admindb">
        <i class="fa $icon"></i>
        <p>$title <b class="caret"></b></p>
      </a>
      <div class="d-none" $idWrap data-role="link">
        <ul class="nav">$item</ul>
      </div>
menu;
  }

  function createMenu($title, $tables, $link = '', $root = true) {
    $items = '';
    foreach ($tables as $key => $item) {
      if (!is_numeric($key)) {
        $items .= '<li class="nav-item">' . createMenu($key, $item, $link . '/' . $key, false) . '</li>';
        continue;
      }

      global $tableActive;
      $linkTarget = $item['fileName'] ?? $item['dbTable'] ?? $item['name'];
      $active = $tableActive === $link . '/' . $linkTarget ? 'active' : '';
      $items .= subSideMenuItem($link . '/' . $linkTarget, $item['name'], $active);
    }
    return subSideMenu(gTxt($title), $items, $root);
  }

  $adminMenu = createMenu('Администрирование', $dbTables);
}

?>
<div class="sidebar"> <!-- data-background-color="white"-->

  <div class="logo ml-3">
    <a href="<?= SITE_PATH ?>" class="simple-text logo-normal">Logo</a>
  </div>
  <div class="sidebar-wrapper">
    <ul id="sideMenu" class="nav">
      <?php if (PUBLIC_PAGE) { ?>
        <li class="nav-item">
          <a class="nav-link" href="<?= SITE_PATH ?>public">
            <i class="material-icons">calculate</i>
            <p>Калькулятор</p>
          </a>
        </li>
      <?php } ?>
      <?php foreach ($main->getSideMenu() as $item) {
        switch ($item) {
          case 'orders': ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= SITE_PATH ?>orders">
                <i class="material-icons">table_chart</i>
                <p>Заказы</p>
              </a>
            </li>
            <?php break;
          case 'calendar': ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= SITE_PATH?>calendar">
                <i class="material-icons">date_range</i>
                <p>Календарь</p>
              </a>
            </li>
            <?php break;
          case 'customers': ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= SITE_PATH ?>customers">
                <i class="material-icons">assignment_ind</i>
                <p>Клиенты</p>
              </a>
            </li>
            <?php break;
          case 'users': ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= SITE_PATH ?>users">
                <i class="material-icons">supervisor_account</i>
                <p>Менеджеры</p>
              </a>
            </li>
            <?php break;
          case 'statistic': ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= SITE_PATH ?>statistic">
                <i class="material-icons">timeline</i>
                <p>Статистика</p>
              </a>
            </li>
            <?php break;
          case 'admindb': ?>
            <li class="nav-item">
              <?php if($adminMenu) { ?>
               <?= $adminMenu ?>
               <?php } else { ?>
                <a class="nav-link" href="<?= SITE_PATH ?>admindb">
                  <i class="fa fa-database"></i>
                  <p>Администрирование</p>
                </a>
              <?php } ?>
            </li>
            <?php break;
          case 'catalog': ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= SITE_PATH ?>catalog">
                <i class="material-icons">view_list</i>
                <p>Каталог</p>
              </a>
            </li>
            <?php break;
          case 'fileManager': ?>
            <li class="nav-item">
              <a class="nav-link" href="<?= SITE_PATH ?>fileManager">
                <i class="material-icons">view_list</i>
                <p>Файловый менеджер</p>
              </a>
            </li>
            <?php break;
        }
      } ?>
      <?php if (in_array('setting', $main->getSideMenu()) || $main->getSettings('admin')) { ?>
      <li class="nav-item">
        <a class="nav-link" href="<?= SITE_PATH ?>setting">
          <i class="material-icons">settings</i>
          <p>Настройки</p>
        </a>
      </li>
      <?php } ?>
    </ul>
  </div>
</div>
