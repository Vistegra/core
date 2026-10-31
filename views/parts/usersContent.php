<?php if ( !defined('MAIN_ACCESS')) die('access denied!');

/**
 * @var string $permission from user.php
 */

?>

<div class="d-flex justify-content-between pb-4" id="actionBtnWrap">
  <div>
    <input type="button" class="btn btn-success" value="Добавить" data-action="addUser">
    <input type="button" class="btn btn-warning" value="Изменить" data-action="changeUser">
    <input type="button" class="btn btn-warning" value="Сменить пароль" data-action="changeUserPassword">
  </div>
  <div>
    <input type="button" class="btn btn-danger" value="Удалить" data-action="delUser">
  </div>
</div>
<div class="text-center d-none" id="confirmField">
  <select id="selectPermission" class="d-none"><?= $permission; ?></select>
  <input type="button" class="btn btn-success" value="Подтвердить" data-action="confirmYes">
  <input type="button" class="btn btn-warning" value="Отмена" data-action="confirmNo">
</div>
<div class="res-table">
<table id="usersTable" class="text-center table table-striped">
	<thead>
	<tr>
    <th></th>
    <?php if(isset($columns)) foreach ($columns as $item) { ?>
      <th>
        <input type="button" class="btn btn-info btn-sm table-th" value="<?= $item['name']; ?>" data-column="<?= $item['dbName']; ?>">
      </th>
    <?php } ?>
	</tr>
	</thead>
	<tbody>
  <tr>
    <td><input type="checkbox" class="" data-id="${U.ID}"></td>
		<?php if(isset($columns)) foreach ($columns as $item) { ?>
      <td>${<?= $item['dbName']; ?>}</td>
		<?php } ?>
  </tr>
  </tbody>
	<tfoot><tr></tr></tfoot>
</table>
</div>
