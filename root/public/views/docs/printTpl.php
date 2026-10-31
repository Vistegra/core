<?php ?>
<template id="printTable">
  <div>
    <style>
      * {
        font-size: 14px;
      }
      table.minimalistBlack {
        border: 1px solid #000000;
        width: 100%;
        text-align: left;
        border-collapse: collapse;
      }
      table.minimalistBlack td, table.minimalistBlack th {
        border: 1px solid #000000;
        padding: 5px 4px;
      }
      table.minimalistBlack tbody td {
        font-size: 1rem;
      }
      table.minimalistBlack thead {
        background: #CFCFCF;
        border-bottom: 1px solid #000000;
      }
      table.minimalistBlack thead th {
        font-size: 1.1rem;
        font-weight: bold;
        color: #000000;
        text-align: center;
      }
      table.minimalistBlack tfoot td {
        font-size: 1rem;
      }
      .print__caption {
        margin-bottom: 20px;
        font-size:     1.5rem;
        font-weight:   bold;
      }
      .d-none {
        display: none !important;
      }
    </style>
    <!--link rel="stylesheet" media="print" href="/assets/css/print.css" Не работает (политика безопастности)-->

    <table class="minimalistBlack">
      <caption id="numberWrap" class="print__caption"><h3>СМЕТА №<span id="number"></span></h3></caption>
      <thead>
      <tr>
        <th>Наименование</th>
        <th>Eд.из</th>
        <th>Значение</th>
      </tr>
      </thead>
      <tbody></tbody>
    </table>
    <div id="trussImg"></div>
  </div>
</template>
