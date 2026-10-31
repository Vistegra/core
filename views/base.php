<?php if ( !defined('MAIN_ACCESS')) die('access denied!');

/**
 * @var array $vars extract param
 */

global $main, $target;

if(!isset($pageTitle)) $pageTitle = '';
if(!isset($headContent)) $headContent = '';

if(!isset($global)) {
  if (!isset($pageHeader)) $pageHeader = template('parts/header');
  if (!isset($pageFooter)) $pageFooter = template('parts/footer');
  if (!isset($sideLeft)) $sideLeft = template('parts/sidemenu');
  if (!isset($content)) $content = '';
  if (!isset($sideRight)) $sideRight = '';
}
if(!isset($cssLinks)) $cssLinks = [];
if(!isset($jsLinks)) $jsLinks = [];
if(!isset($footerContent)) $footerContent = '';
if(!isset($footerContentBase)) $footerContentBase = template('parts/footerBase');

?>
<!doctype html>
<html lang="en">
<head>
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <?= $headContent ?>
  <title><?= $pageTitle; ?></title>
  <link rel="icon" href="<?= SITE_PATH ?>favicon.ico">
  <?php if($main->checkStatus('ok') || $target === 'login') { ?>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons">
    <link rel="stylesheet" href="<?= CORE_CSS?>admin.css">
  <?php } ?>

  <?php array_map(function($item) { ?>
    <link rel="stylesheet" href="<?= str_replace('//', '/', $item); ?>">
  <?php }, $cssLinks); ?>

  <script>
    window.DEBUG         = '<?= DEBUG ?>';
    window.CSV_DEVELOP   = '<?= CSV_DEVELOP ?>';
    window.SITE_PATH     = '<?= SITE_PATH ?>';
    window.MAIN_PHP_PATH = '<?= SITE_PATH ?>index.php';
    window.PUBLIC_PAGE   = '<?= PUBLIC_PAGE ?>';
    window.PATH_IMG      = '<?= PATH_IMG ?>';
    window.AUTH_STATUS   = '<?= $main->checkStatus('ok') ?>';
  </script>
</head>

<body>

<?php if(!isset($global)) { ?>
<main class="container-fluid mx-auto">
  <section class="wrapper">

    <?php if($sideLeft) { ?>
      <aside id="sideLeft"><?= $sideLeft; ?></aside>
    <?php } ?>

    <section class="main-panel mx-auto" style="<?= !$sideLeft ? 'width: 100%' : '' ?>">
      <?= $pageHeader; ?>

      <div class="content mt-0 p-1">
        <div class="row justify-center"> <!-- возможно убрать justify-center -->
                                         <!-- стили временно-->
          <div class="col-12 <?= $sideRight ? 'col-xl-10' : '' ?>"><?= $content; ?></div>

          <?php if($sideRight) { ?>
            <aside id="right" class="col-12 col-md-2"><?= $sideRight; ?></aside>
          <?php } ?>

        </div>
      </div>
      <footer class="footer"><?= $pageFooter; ?></footer>
    </section>

  </section>
</main>
<?php } else echo $global; ?>

<script defer type="module" src="<?= CORE_SCRIPT?>src.js"></script>
<script defer type="module" src="<?= CORE_SCRIPT?>main.js"></script>

<?php array_map(function($item) { ?>
  <script type="module" src="<?= str_replace('//', '/', $item); ?>"></script>
<?php }, $jsLinks) ?>

<?= $footerContent; ?>
<?= $footerContentBase; ?>
</body>
</html>
