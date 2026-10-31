<?php
global $main;
$admin = $main->getSettings('admin');
$catalogProperties = in_array('catalog', $main->getSideMenu());
?>
<div class="row container m-auto" id="settingForm">
  <?php if ($admin) { ?>
  <div class="col-6 border">
    <form action="#" id="mailForm" class="row">
      <div class="col-12 d-flex justify-content-between">
        <p>Почта для получения заказов</p>
        <input type="email" name="orderMail" value="<?= $main->getSettings('orderMail') ?>">
      </div>
      <div class="col-12 d-flex justify-content-between">
        <p class="mt-1">Копия письма</p>
        <input class="mt-1" type="email" name="orderMailCopy" value="<?= $main->getSettings('orderMailCopy') ?>">
      </div>
      <div class="col-12 d-flex justify-content-between">
        <p class="mt-1">Тема письма</p>
        <input class="mt-1" type="text" name="orderMailSubject" value="<?= $main->getSettings('orderMailSubject') ?>">
      </div>
      <div class="col-12 d-flex justify-content-between">
        <p class="mt-1">Имя отправителя</p>
        <input class="mt-1" type="text" name="orderMailFromName" value="<?= $main->getSettings('orderMailFromName') ?>">
      </div>
    </form>
  </div>
  <?php } ?>

  <div class="col-6 border">
    <form action="#" id="userForm">
      <input type="hidden" name="priority" value="<?= $main->getLogin('id') ?>">
      <div class="col-12 d-flex justify-content-between">
        <p>Логин</p>
        <input type="text" name="login" value="<?= $main->getLogin() ?>">
      </div>
      <div class="col-12 d-flex justify-content-between">
        <p class="mt-1">Новый Пароль</p>
        <input class="mt-1" type="password" name="password">
      </div>
      <div class="col-12 d-flex justify-content-between">
        <p class="mt-1">Повторите пароль</p>
        <input class="mt-1" type="password" name="passwordRepeat">
      </div>
      <div class="col-12 d-flex justify-content-between">
        <p class="mt-1" >Запретить одновременный вход</p>
        <label class="d-block w-50 text-center">
          <input class="mt-1" type="checkbox" name="onlyOne" <?= $main->getSettings('onlyOne') ? 'checked' : '' ?>>
        </label>
      </div>
    </form>
  </div>

  <?php if ($admin && USE_DATABASE) {
    !isset($permStatus) && $permStatus = []; ?>
  <div class="col-6 border">
    <form action="#" class="row" id="permission">
      <?php if (isset($permIds)) { ?>
        <input type="hidden" name="permIds" value="<?= $permIds ?>">
      <?php } ?>
      <div class="col-12 d-flex justify-content-between">
        <div>Добавить тип доступа</div>
        <div class="w-50">
          <input type="text" class="w-80" name="permType">
          <input type="button" value="+" data-action="addPermType">
        </div>
      </div>
      <div class="col-12 d-flex justify-content-between mt-1">
        <div>Тип доступа</div>
        <div class="w-50">
          <select class="w-80 useToggleOption" data-field="permTypes">
            <?php foreach ($permStatus as $item) { ?>
              <option value="<?= $item['ID'] ?>" data-target="perm<?= $item['ID'] ?>"><?= $item['name'] ?></option>
            <?php } ?>
          </select>
          <input type="button" value="x" data-action="removePermType">
        </div>
      </div>
      <?php foreach ($permStatus as $item) { ?>
      <div class="col-12 d-flex justify-content-between mt-1" data-relation="perm<?= $item['ID'] ?>">
        <div>Доступные меню</div>
        <select name="permMenuAccess_<?= $item['ID'] ?>" multiple size="5" class="w-50">
          <?php foreach ($main->getSideMenu() as $menu) { ?>
            <option value="<?= $menu ?>"><?= gTxt($menu) ?></option>
          <?php } ?>
        </select>
      </div>
      <?php } ?>
    </form>
  </div>

  <div class="col-6 border">
    <form action="#" id="managerForm" class="row">
      <div class="col-12 d-flex justify-content-between">
        <p class="col-7">Дополнительные поля менеджеров</p>
        <div class="col-5">
          <input type="button" data-action="addCustomManagerField" value="+">
          <input type="button" data-action="removeCustomManagerField" value="-">
        </div>
      </div>
      <div class="col-12 d-flex flex-wrap justify-content-between text-center" data-field="customField"></div>
    </form>
  </div>
  <?php } ?>

  <?php if ($admin && USE_DATABASE) { ?>
    <div class="col-6 border">
      <form action="#" id="rateForm" class="row">
        <div class="col-12 d-flex justify-content-between">
          <p class="mt-1">Автоматически обновлять курсы</p>
          <label class="d-block w-50 text-center">
            <input class="mt-1" type="checkbox" name="autoRefresh" checked
                   data-target="manualRefresh">
          </label>
        </div>
        <div class="col-12 text-center" data-relation="!manualRefresh">
          <input type="button" class="btn btn-primary" value="Редактировать курсы" data-action="loadRate">
        </div>
      </form>
    </div>
  <?php } ?>

  <?php if ($admin && false) { ?>
    <div class="col-6 border">
      <div class="col-12 text-center">Статусы</div>
      <div class="col-12">
        <select name="">

        </select>
      </div>
    </div>
  <?php } ?>

  <div class="col-12">
    <input type="button" class="btn btn-primary" value="Сохранить" data-action="save">
  </div>

  <?php if ($catalogProperties) { ?>
    <div class="col-12" id="propertiesWrap">
      <hr>
      <details>
        <summary data-action="loadProperties">
          Редактировать параметры каталога
        </summary>
        <div>
          <table id="propertiesTable" class="text-center table table-striped">
            <thead>
            <tr>
              <th></th>
              <th>Свойство</th>
              <th>Код</th>
              <th>Тип</th>
            </tr>
            </thead>
            <tbody>
            <tr>
              <td><input type="checkbox" class="" data-id="${property}"></td>
              <td>${name}</td>
              <td>${property}</td>
              <td>${type}</td>
            </tr>
            </tbody>
          </table>
          <div class="mt-1 text-center">
            <input class="btn btn-success" type="button" value="Добавить" data-action="createProperty">
            <input class="btn btn-warning" type="button" value="Изменить" data-action="changeProperty">
            <input class="btn btn-danger" type="button" value="Удалить" data-action="delProperties">
          </div>
        </div>
      </details>
    </div>
  <?php } ?>
</div>

<?php if ($catalogProperties) { ?>
<template id="propertiesCreateTmp">
  <form action="#" id="temp">
    <div class="form-group">
      <label class="w-100">Название свойства: <input type="text" class="form-control" name="tableName"></label>
    </div>
    <div class="form-group">
      <label class="w-100">Код свойства: <input type="text" class="form-control" name="tableCode"></label>
    </div>
    <div class="form-group">
      <label class="w-100">Тип данных:
        <select class="form-control useToggleOption" name="dataType" data-field="propertyType">
          <optgroup label="Простые">
            <option value="s_text" data-target="">Текст (~200 символов)</option>
            <option value="s_textarea">Текст (много)</option>
            <option value="s_number">Число</option>
            <option value="s_date">Дата</option>
            <option value="s_bool">Флаг (Да/Нет)</option>
          </optgroup>
          <optgroup label="Составные">
            <option value="h_select" data-target="selectField">Справочник</option>
          </optgroup>
        </select>
      </label>
    </div>
    <div class="form-group" data-relation="selectField">
      <div class="col-12 d-flex justify-content-between">
        <p class="col-5">Дополнительные поля параметра (имя есть)</p>
        <div class="col-7">
          <input class="btn btn-success" type="button" data-action="addCol" value="+">
          <input class="btn btn-danger" type="button" data-action="remCol" value="-">
        </div>
      </div>
      <div class="col-12 d-flex flex-wrap justify-content-between text-center" data-field="propertiesCols">
        <div class="col-12 d-flex justify-content-between mt-1" data-field="propertiesColItem">
          <div class="col-6">
            <input type="text" data-field="key">
          </div>
          <div class="col-6">
            <select class="w-100" data-field="type">
              <option value="string">Текст (~200 символов)</option>
              <option value="textarea">Текст (много)</option>
              <option value="double">Число</option>
              <option value="money">Число</option>
              <option value="date">Дата</option>
              <option value="file">Файл</option>
              <option value="bool">Флаг</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </form>
</template>
<?php } ?>
