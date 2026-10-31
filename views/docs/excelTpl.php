<?php
$listData = isset($this->data['report']) ? $this->data['report'] : false;//$this->data['report'];
!$listData && $listData = $this->data['rBack']['report'];

$rows = [];
foreach ($listData['base'] as $item) {
  if (!isset($item['code']) || strlen($item['code']) < 1) continue;
  $rows[] = [$item['code'], $item['count']];
}
