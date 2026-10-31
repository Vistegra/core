<?php if ( !defined('MAIN_ACCESS')) die('access denied!');

!isset($ordersColumns) && $ordersColumns = [];
!isset($ordersVisitorColumns) && $ordersVisitorColumns = [];
?>
<div class="d-flex justify-content-between pb-4" id="actionBtnWrap">
  <div>
    <input type="button" class="btn btn-success oneOrderOnly" value="Редактировать" data-action="openOrder">
    <span id="orderBtn">
      <input type="button" class="btn btn-warning" value="Изменить Статус" data-action="changeStatusOrder">
      <input type="button" class="btn btn-primary oneOrderOnly" value="Pdf" data-action="savePdf">
      <input type="button" class="btn btn-primary oneOrderOnly" value="Печать" data-action="printReport">
      <input type="button" class="btn btn-primary oneOrderOnly" value="Отправить на почту" data-action="sendOrder">
    </span>
  </div>
  <div>
    <input type="button" class="btn btn-danger" value="Удалить" data-action="delOrders">
  </div>
</div>
<div class="d-none pb-4" id="confirmField">
  <select id="selectStatus" class="d-none custom-select select-status" name="statusId"></select>
  <input type="button" class="btn btn-success" value="Подтвердить" data-action="confirmYes">
  <input type="button" class="btn btn-warning" value="Отмена" data-action="confirmNo">
</div>
<div class="pb-4 d-none" id="printTypeField">
<?php for ($i = 1; $i <= PRINT_BTN; $i++) { ?>
  <input type="button" class="btn btn-primary"
         data-action="printReport" data-type="printType<?= $i ?>"
         value="<?= gTxt('printType' . $i) ?>">
<?php } ?>
  <input type="button" class="btn btn-warning" data-action="cancelPrint" value="Отмена">
</div>
<?php if (USERS_ORDERS) { ?>
  <div class="d-flex pb-4" style="justify-content: left">
    <div>
      <label title="Заказы сохраненные Менеджерами">
        <input type="radio" name="orderType" value="order" checked data-action="orderType">
        Сохраненные заказы</label>
    </div>
    <div class="ml-1">
      <label title="Уникальные расчеты посетителей">
        <input type="radio" name="orderType" value="visit" data-action="orderType">
        Пользовательские заказы</label>
    </div>
  </div>
<?php } ?>
<div class="res-table">

  <div class="form-group">
    <label class="w-100">Поиск: <input type="text" id="search" name="search" value="" class="form-control" autocomplete="off"></label>
  </div>

  <table id="commonTable" class="text-center table table-striped"></table>

  <template id="orderTableTmp">
    <table>
      <thead>
        <tr>
          <th></th>
          <?php foreach ($ordersColumns as $item) { ?>
            <th>
              <input type="button" class="btn btn-info btn-sm table-th" value="<?= $item['name']; ?>" data-column="<?= $item['dbName']; ?>">
            </th>
          <?php } ?>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><input type="checkbox" class="" data-id="${O.ID}"></td>
          <?php foreach ($ordersColumns as $item) { ?>
            <td>${<?= $item['dbName']; ?>}</td>
          <?php } ?>
        </tr>
      </tbody>
    </table>
  </template>

  <?php if (USERS_ORDERS) { ?>
  <template id="orderVisitorTableTmp">
    <table>
      <thead>
      <tr>
        <th></th>
        <?php foreach ($ordersVisitorColumns as $item) { ?>
          <th>
            <input type="button" class="btn btn-info btn-sm table-th" value="<?= $item['name']; ?>" data-column="<?= $item['dbName']; ?>">
          </th>
        <?php } ?>
      </tr>
      </thead>
      <tbody>
      <tr>
        <td><input type="checkbox" class="" data-id="${cp_number}"></td>
        <?php foreach ($ordersVisitorColumns as $item) { ?>
          <td>${<?= $item['dbName']; ?>}</td>
        <?php } ?>
      </tr>
      </tbody>
    </table>
  </template>
  <?php } ?>
</div>
