<?php
/**
 * Писать специально уникальные(новые) имена фукнций.
 */


/**
 * @param $number
 * @param $reportVal
 * @return false|string
 */
function addCpNumber_DefaultFunc($number, $reportVal) {
  $report = json_decode($reportVal, true);
  $report['cpNumber'] = $report['cpNumber'] ?? $number;
  return json_encode($report);
}
addHook('addCpNumber', 'addCpNumber_DefaultFunc');
