<?php if ( !defined('MAIN_ACCESS')) die('access denied!');

/**
 * @var array $vars extract param
 */
$inline = strtolower(OUTSIDE);

$content = $global ?? $content ?? '';
$footerContent = $footerContent ?? '';

$cssLinksArr = $cssLinks ?? [];
$cssLinksRes = $inline ? '' : [];

$jsLinksArr = $jsLinks ?? [];
$jsLinksRes = $inline ? '' : [];

$globalWindowJsValue = '<script>window.CL_OUTSIDE = "1"; window.SITE_PATH = "' . SITE_PATH . '";' .
                       ' window.MAIN_PHP_PATH = "' . SITE_PATH . 'index.php' . '";' .
                       ' window.PUBLIC_PAGE = "' . PUBLIC_PAGE . '";' .
                       ' window.PATH_IMG = "' . PATH_IMG . '";</script>';

array_map(function($item) use (&$cssLinksRes, $inline) {
  if (gettype(OUTSIDE) !== 'boolean') {
    $global = stripos($item, 'global') !== false ? 'data-global="true"' : '';
    $href = $inline === 'i' ? 'href' : 'data-href';
    $cssLinksRes .= "<link rel=\"stylesheet\" $global $href=\"$item\">";
  }
  else $cssLinksRes[] = $item;
}, $cssLinksArr);

$jsLinksArr = [ CORE_SCRIPT . 'src.js', CORE_SCRIPT . 'main.js'];
array_map(function($item) use (&$jsLinksRes) {
  if (gettype(OUTSIDE) !== 'boolean') {
    $jsLinksRes .= '<script defer type="module" src="' . $item . '"></script>';
  }
  else $jsLinksRes[] = $item;
}, $jsLinksArr);

$result = [
  'content'             => $content,
  'globalWindowJsValue' => $globalWindowJsValue,
  'footerContent'       => $footerContent,
  'cssLinksArr'         => $cssLinksRes,
  'jsLinksArr'          => $jsLinksRes,
];

if ($inline === 's') {
  echo getPageAsString($result);
} else if ($inline === 'i') {
  echo $cssLinksRes . $globalWindowJsValue . $content . $footerContent . $jsLinksRes;
} else {
  echo json_encode($result);
  die();
}
