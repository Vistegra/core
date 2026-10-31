<?php if ( !defined('MAIN_ACCESS')) die('access denied!');

/**
 * @var object $main
 * @var array $param
 */

$field['content'] = template('parts/customersContent', $param);
$field['pageFooter'] = '<div id="paginator" class="w-100"></div>';

$field['footerContent'] = <<<footerContent
<template id="tableOrderBtn">
  <input type="button" class="btn btn-info btn-sm table-th" value="Посмотреть заказы" data-id="\${id}" data-action="openOrders">
</template>
<template id="tableOrdersNumbers">
  <div>
    <label class="m-2 d-flex">
      <input type="radio" name="orders" value="\${value}">
      <span class="ml-2">Заказ №\${value}</span>
    </label>
  </div>
</template>
<template id="tableContactsValue">
  <div>\${key}: \${value}</div>
</template>
<template id="customerForm">
  <form action="#">
    <div class="form-group">
      <label class="w-100">Имя: <input type="text" class="form-control" name="name" value=""></label>
    </div>
    <div class="form-group">
      <label class="w-100">Телефон: <input type="tel" class="form-control" name="phone"></label>
    </div>
    <div class="form-group">
      <label class="w-100">Почта: <input type="email" class="form-control" name="email"></label>
    </div>
    
    <div class="saveOrderField modal-content__form grid-block margin-top">
      <div class="modal-content__field">
        <label class="radio">
          <input type="radio" name="cType" value="i" checked data-target="customerTypeI">
          <span class="radio__text">Физ.лицо</span>
        </label>
      </div>

      <div class="modal-content__field">
        <label class="radio">
          <input type="radio" name="cType" value="b" data-target="customerTypeB">
          <span class="radio__text">Юр.лицо</span>
        </label>
      </div>
    </div>
    
    <div class="form-group">
      <label class="w-100">Адрес: <input type="text" name="address" value="" class="form-control"></label>
    </div>
    
    <div class="form-group" data-relation="customerTypeB && !customerTypeI">
      <label class="w-100">ИНН: <input type="text" name="ITN" value="" class="form-control"></label>
    </div>
  </form>
</template>
<template id="noFoundSearchMsg">
  <tr><td colspan="15">не найдено</td></tr>
</template>
footerContent;

$field['footerContent'] .= '<a id="publicPageLink" href="' . PUBLIC_PAGE . '" hidden></a>';
$field['footerContent'] .= $main->initDictionary();
