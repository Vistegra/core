<?php
$data = $this->data;
?>
<div class="wrapper">
  <header>
    <table>
      <tbody>
        <tr>
          <td>
            <img src="<?= $this->imgPath ?>logo.jpg" alt="logo">
          </td>
          <td style="text-align: right; font-size: 15px; width:30%; vertical-align: middle">
            <u>тел. +7 (499) 450-64-66</u>
            <span style="font-size: 14px">www.site.ru</span>
          </td>
        </tr>
      </tbody>
    </table>
  </header>
  <?php if ($data['cpNumber']) { ?>
  <h3>Ваш индивидуальный номер расчёта: <?= $data['cpNumber'] ?></h3>
  <?php } ?>
  <h4>Исходные данные:</h4>
  <table>
    <tbody>
    <?php foreach ($data['input'] as $rows) { ?>
        <tr>
          <td style="width: 25%;"><?= $rows[0] ?></td>
          <td style="width: 25%;"><?= $rows[1] ?></td>
        </tr>
    <?php } ?>
    </tbody>
  </table>
  <h4>Смета:</h4>
  <table>
    <thead>
      <tr>
        <th>№</th>
        <th>Наименование</th>
        <th>Материал</th>
        <th>Ед.изм.</th>
        <th>Кол-во</th>
        <th>Цена</th>
        <th>Сумма</th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 0;
        foreach ($data as $key => $list) {
        if (!in_array($key, ['base', 'paint'])) continue;
        foreach ($list as $rows) { ?>
          <tr>
            <td style="width: 5%;"><?= $i += 1 ?></td>
            <td style="width: 25%;"><?= $rows['name'] ?></td>
            <td style="width: 25%;"><?= $rows['mName'] ?></td>
            <td style="width: 10%;"><?= $rows['unit'] ?></td>
            <td style="width: 10%;"><?= $rows['count'] ?></td>
            <td style="width: 10%;"><?= $rows['value'] ?></td>
            <td style="width: 15%;"><?= $this->setNumFormat($rows['total']) ?></td>
          </tr>
      <?php }
      } ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="5">СТОИМОСТЬ МАТЕРИАЛОВ:</td>
        <?php $total = $data['base']['sTotal'] + $data['paint']['sTotal'] ?>
        <td colspan="2" style="text-align: center;"><?= $this->numFormat($total) ?> руб.</td>
      </tr>
    </tfoot>
  </table>
</div>
