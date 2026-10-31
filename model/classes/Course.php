<?php

!defined('PATH_CSV') && define('PATH_CSV', $_SERVER['DOCUMENT_ROOT'] . '/csv');

class Course {
  const REFRESH_INTERVAL = 36000;
  const LINK_PARAM = '';
  const DEFAULT_CURRENCY = 'RUS'; // Определить валюту по умолчанию по домену

  /**
   * @var string[]
   */
  private $source = [
    'RUS' => "https://www.cbr.ru/scripts/XML_daily.asp",
    'BYN' => "https://www.nbrb.by/services/xmlexrates.aspx",
  ];

  private $db;
  private $xml;
  private $dataFile;


  /**
   * @var array
   */
  public $rate;

  public function __construct(&$db, $dataFile = PATH_CSV . 'exchange_rate.bin') {
    if (is_object($db)) $this->getRateFromDb($db);
    else $this->getRateFromFile($dataFile);

    $this->refresh();
  }

  private function checkTableMoney() {
    // проверить есть ли в Таблице базовая валюта
  }

  private function getMainCurrency() {
    $res = array_filter($this->rate, function ($c) { return boolval($c['main']); });
    if (count($res)) return array_values($res)[0];

    return $this->rate[self::DEFAULT_CURRENCY] ?? array_values($this->rate)[0];
  }

  private function searchRate($code) {
    foreach ($this->xml as $c) {
      if (strval($c->CharCode) === $code) {
        $rate = $c->Value ? strval($c->Value) : strval($c->Rate);
        return round((float) str_replace(",", ".", $rate), 4);
      }
    }
    return false;
  }

  private function notNeedRefresh(): bool {
    $time = time() - $this::REFRESH_INTERVAL;
    foreach ($this->rate as $currency) {
      if ($time > strtotime($currency['lastEditDate'])) return false;
    }
    return true;
  }

  private function getRateFromDb($db) {
    $this->db = $db;
    if (DEBUG) $this->checkTableMoney();
    $this->rate = $db->getMoney();
  }

  private function getRateFromFile($dataFile) {
    $this->dataFile = $dataFile;
    if (file_exists($dataFile)) {
      $data = unserialize(file_get_contents($dataFile));
      $this->refreshTime = $data['refresh_time'];
      $this->rate = $data['curs'];
    }
  }

  private function setRateToDb() {
    $this->db->setMoney($this->rate);
  }

  private function setRateToFile() {
    file_put_contents($this->dataFile,
      serialize(["refresh_time" => time(), 'curs' => $this->rate]));
  }

  private function readFromCbr(): void {
    $mainCurrency = $this->getMainCurrency();

    $linkSource = $this->source[$mainCurrency['code']] ?? false;

    if (!$linkSource || (!$this->xml = simplexml_load_file($linkSource . $this::LINK_PARAM))) return;

    foreach ($this->rate as $code => $currency) {
      if ($currency === $mainCurrency) continue;
      $value = $this->searchRate($code);
      if ($value) $this->rate[$code]['rate'] = $value;
    }
  }

  public function refresh(): Course {
    if ($this->notNeedRefresh()) return $this;

    $this->readFromCbr();
    if (USE_DATABASE) $this->setRateToDb();
    else $this->setRateToFile();
    return $this;
  }

  public function getRate(): array {
    return array_map(function ($c) {
      return [
        'id' => $c['code'],
        'value' => $c['rate'] ?? 1,
      ];
    }, $this->rate);
  }
}
