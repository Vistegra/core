<?php if (!defined('MAIN_ACCESS')) die('access denied!');

$field['content'] = template('calculatorContent');

$field['footerContent'] = <<<footerContent
<template id="sendMailTmp">
  <div class="modal-content d-flex">
    <span class="modal-content__close closeBtn"></span>
    <span class="modalT"></span>
  
    <form action="#" id="authForm">
      <input type="hidden" name="changeBool" value="0">
      
      <div class="modal-content__field d-flex">
        <label for="search" class="modal-content__label">Поиск</label>
        <input type="text" id="search" name="search" value="" class="modal-content__input w-100 mb-3" autocomplete="off">
      </div>
      
      <div class="modal-content__form grid-block">
  
        <div class="modal-content__field d-flex">
          <label for="name" class="modal-content__label">Имя</label>
          <input type="text" id="name" name="name" value="" class="modal-content__input">
        </div>
  
        <div class="modal-content__field d-flex">
          <label for="phone" class="modal-content__label">Телефон</label>
          <input type="tel" id="phone" name="phone" class="modal-content__input">          
        </div>        
  
        <div class="modal-content__field d-flex">
          <label for="email" class="modal-content__label">Почта</label>
          <input type="text" id="email" name="email" value="" class="modal-content__input">
        </div>
        
      </div>
      
      <div class="modal-content__form grid-block margin-top">
        <div class="modal-content__field">
          <label class="radio">
            <input type="radio" class="custom-radio style-circle" name="customerType" value="i" data-target checked>
            <div class="radio__text">Физ.лицо</div>
          </label>
        </div>
        
        <div class="modal-content__field">
          <label class="radio">
            <input type="radio" class="custom-radio style-circle" name="customerType" value="b" data-target="intField">
            <div class="radio__text">Юр.лицо</div>
          </label>
        </div>          
          
        <div class="modal-content__field d-flex">
          <label for="address" class="modal-content__label">Адрес</label>
          <input type="text" id="address" name="address" value="" class="modal-content__input">
        </div>        
        
        <div class="modal-content__field d-flex d-none intField">
          <label for="ITN" class="modal-content__label">ИНН</label>
          <input type="text" id="ITN" name="ITN" value="" class="modal-content__input">
        </div>          
       </div>      
      <!--div class="modal-content__field flex-row margin-top">
        <input type="checkbox" id="politic" name="politic" checked>
        <label for="politic" class="modal-content__label">Согласен с политикой конфидециальности</label>
      </div-->
      <div class="modal-content__form grid-block margin-top d-none" id="customerChange">
        <div class="modal-content__field">
          <label class="radio">
            <input type="radio" class="custom-radio style-circle" name="customerChange" value="change" checked>
            <div class="radio__text">Сохранить изменения</div>
          </label>
        </div>
        
        <div class="modal-content__field">
          <label class="radio">
            <input type="radio" class="custom-radio style-circle" name="customerChange" value="add">
            <div class="radio__text">Добавить нового клиента</div>
          </label>
        </div>   
      </div>
    </form>
    <button type="button" class="cl_btn st-yellow wide3 mx-auto" id="btnConfirmSend">OK</button>
  </div>
</template>
<template id="searchResult">
  <div class="border position-absolute bg-white w-100" style="top: 100%; z-index: +1; cursor: pointer"></div>
</template>
footerContent;

$field['footerContent'] .= template('docs/printTpl');
