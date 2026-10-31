<?php if (!defined('MAIN_ACCESS')) die('access denied!');

/**
 * @var object $main
 * @var array $param
 * @var array $permission
 * @var array $managerField
 */

$field['content'] = template('parts/usersContent', $param);
$field['pageFooter'] = '<div id="paginator" class="w-100"></div>';

// Users/Manager custom field
$managerFieldHtml = '';
foreach ($managerField as $k => $item) {
  switch ($item['type']) {
    case 'textarea':
      $input = '<textarea name="' . $k . '" class="form-control"></textarea>';
      break;
    case 'string': case 'number': case 'date': default:
      $input = '<input type="' . $item['type'] . '" class="form-control" name="' . $k . '">';
      break;
  }

  $managerFieldHtml .= '<div class="form-group managerField"><label class="w-100">' . $item['name'] . $input .'</label></div>';
}

$field['footerContent'] = <<<footerContent
<template id="permission">
  <option value="\${ID}">\${name}</option>
</template>
<template id="tableContactsValue">
  <div>\${key}: \${value}</div>
</template>
<template id="userForm">
  <form action="#">
    <div class="form-group">
      <label class="w-100">Имя пользователя: <input type="text" class="form-control" name="name"></label>
    </div>
    <div class="form-group">
      <label class="w-100">Доступ: 
        <select class="form-control" name="permissionId">$permission</select>
      </label>
    </div>
    <div class="form-group">
      <label class="w-100">Логин: <input type="text" class="form-control" name="login"></label>
    </div>
    <div class="form-group">
      <label class="w-100">Пароль: <input type="password" class="form-control" name="password"></label>
    </div>
    <div class="form-group">
      <label class="w-100">Телефон: <input type="tel" class="form-control" name="phone"></label>
    </div>
    <div class="form-group">
      <label class="w-100">Почта: <input type="email" class="form-control" name="email"></label>
    </div>
    $managerFieldHtml
    <div id="changeField">
      <label class="w-100">Активность: <input type="checkbox" name="activity"></label>
    </div>
  </form>
</template>

<template id="userChangePassForm">
  <form action="#">
    <!--label>Старый пароль: <input type="password" name="oldPass"></label-->
    <div class="form-group">
      <label class="w-100">Новый пароль: <input type="password" class="form-control" name="newPass"></label>
    </div>
    <div class="form-group">
      <label class="w-100">Повторить пароль: <input type="password" class="form-control" name="repeatPass"></label>
    </div>
  </form>
</template>
footerContent;

$field['footerContent'] .= $main->initDictionary();
