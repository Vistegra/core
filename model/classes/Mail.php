<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!defined('MAIN_ACCESS')) die('access denied!');

// General setting
!defined('MAIL_TARGET') && define('MAIL_TARGET', 'trydim@mail.ru');
!defined('MAIL_SUBJECT_DEFAULT') && define('MAIL_SUBJECT_DEFAULT', 'Заявка с сайта ' . $_SERVER['SERVER_NAME']);
!defined('ABS_SITE_PATH') && define('ABS_SITE_PATH', $_SERVER['DOCUMENT_ROOT']);
!defined('CORE') && define('CORE', '/');
!defined('SETTINGS_PATH') && define('SETTINGS_PATH', '');

// MailSetting
const MAIL_TARGET_DEBUG = 'trydim@mail.ru';
const MAIL_SMTP = true;

const MAIL_PORT = 465;
const MAIL_HOST = 'smtp.yandex.ru';
const MAIL_FROM = 'noreplycalcby@yandex.ru';
const MAIL_PASSWORD = '638ch1';
/*
const MAIL_HOST = 'smtp.mail.ru';
const MAIL_FROM = 'mail.common@list.ru';
const MAIL_PASSWORD = 'RAE^ysPypo22';
/*
const MAIL_FROM = 'commonserver@yandex.ru';
const MAIL_PASSWORD = 'xmbxqxulvhwcqyta';
*/

require_once CORE . 'libs/vendor/autoload.php';

class Mail {
  private $mailTpl, $body = '', $docPath = '', $pdfFileName = '';
  private $mailTarget;
  private $subject, $fromName;
  private $otherMail       = [];
  private $attachmentFiles = [];


  /**
   *
   * @return array
   */
  private function LoadSettingFile(): array {
    $content = file_get_contents(SETTINGS_PATH);
    return !empty($content) && is_string($content) ? json_decode($content): [];
  }

  /**
   * @param string $fileName
   * @return string
   */
  private function findFile(string $fileName): string {
    return __DIR__ . '/template/mailTpl.php';
  }

  /**
   * @param string $mailTpl
   */
  public function __construct(string $mailTpl) {
    $this->mailTpl = $mailTpl;

    if (!DEBUG && file_exists(SETTINGS_PATH)) {
      $setting = function_exists('getSettingFile') ? getSettingFile() : $this->LoadSettingFile();
      $this->mailTarget = $setting['orderMail'];
      isset($setting['orderMailCopy']) && strlen($setting['orderMailCopy']) && $this->otherMail[] = $setting['orderMailCopy'];
      isset($setting['mailFromName']) && strlen($setting['mailFromName']) && $this->otherMail[] = $setting['orderMailCopy'];

      if (isset($setting['mailSubject'])) $this->subject = $setting['mailSubject'];
      else $this->subject = MAIL_SUBJECT_DEFAULT;
    }
  }

  /**
   * @param array $array
   */
  public function prepareMail(array $array = []): void {
    extract($array);
    ob_start();

    // Шаблон в public
    if (file_exists(ABS_SITE_PATH . 'public/views/docs/' . $this->mailTpl . '.php'))
      include ABS_SITE_PATH . "public/views/docs/$this->mailTpl.php";
    else if (file_exists($this->mailTpl)) include $this->mailTpl;
    else {
      ob_clean();
      $this->body = $this->getDefaultTemplate();
      return;
    }
    $this->body = ob_get_clean();
  }

  /**
   * @param string $str
   */
  public function setSubject(string $str): void {
    $this->subject = $str;
  }

  /**
   * @param string $email
   */
  public function addMail(string $email): void {
    $this->otherMail[] = $email;
  }

  /**
   * @param string $docPath
   * @param string $fileName
   */
  public function addFile(string $docPath, string $fileName = '') {
    $fileName = !empty($fileName) ? $fileName : basename($docPath);
    $this->docPath = $docPath;
    $this->pdfFileName = empty($fileName) ? uniqid() . '.pdf' : $fileName;
  }

  public function addOtherFile($files) {
    $that = $this;
    array_map(function ($file) use (&$that) {
      if (!is_file($file['tmp_name'])) return;
      $that->attachmentFiles[] = [
        'path' => $file['tmp_name'],
        'name' => $file['name'],
        'type' => $file['type'],
        //$encoding = self::ENCODING_BASE64,
      ];
    }, $files);
  }

  public function send() {
    $mail = new PHPMailer();
    $mail->SMTPDebug = DEBUG !== false;          // Enable verbose debug output
    $mail->CharSet = "UTF-8";

    try {
      if (MAIL_SMTP) {
        $mail->isSMTP();                         // Send using SMTP
        $mail->Host = MAIL_HOST;                 // Set the SMTP server to send through
        $mail->SMTPAuth = true;                  // Enable SMTP authentication
        $mail->Username = MAIL_FROM;             // SMTP username
        $mail->Password = MAIL_PASSWORD;         // SMTP password
        $mail->SMTPSecure = 'ssl';               // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port = MAIL_PORT;                 // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
      }

      // Получатель письма
      if (DEBUG) {
        $this->mailTarget = MAIL_TARGET_DEBUG;
        $this->subject    = 'Тестовое письмо ' . $_SERVER['SERVER_NAME'];
        $this->fromName   = 'vistegra.by';
      }

      $mail->addBCC($this->mailTarget ?? MAIL_TARGET_DEBUG);
      $mail->From = MAIL_FROM;
      $mail->FromName = $this->fromName;

      foreach ($this->otherMail as $moreMail) {
        if ($moreMail === $this->otherMail) continue;
        $mail->addBCC($moreMail);
      }

      // -----------------------
      // Mail Body
      // -----------------------

      if (isset($resource))
        for ($i = 1; $i <= count($resource); $i++)
          $mail->AddEmbeddedImage($resource[$i - 1], "pict$i.jpg", "pict$i.jpg", 'base64', 'image/jpeg');

      $mail->Subject = $this->subject ?? MAIL_SUBJECT_DEFAULT;
      $mail->MsgHTML($this->body);
      $mail->AltBody = 'Тестовое сообщение.';

      // Attachment files
      if (is_file($this->docPath)) {
        $mail->addAttachment($this->docPath, $this->pdfFileName);
        $resource = [$this->docPath];
      }

      // Other Files
      foreach ($this->attachmentFiles as $file) {
        $mail->addAttachment($file['path'], $file['name'], 'base64', $file['type']);
      }

      $mail->send();

      if (isset($resource)) array_map(function ($item) { unlink($item); }, $resource);

      return true;
    } catch (Exception $e) {
      return 'Сообщение не было отправлено. Причина ошибки: {$mail->ErrorInfo}';
    }
  }

  /*
private function createImg($img) {
  define('PATH', '../'); // ссылки приходят относительно index.php - указать путь
  // стоит добавить проверку на адекватность
  $arrResource = [];
  foreach ($img as $items) {
    $size = getimagesize(PATH . $items[0]);
    if($size) {
      $resultImg = @imagecreatetruecolor($size[0], $size[1]);
      foreach ($items as $img) {
        if($layerImg = imagecreatefrompng( PATH . $img ))
          imagecopy($resultImg, $layerImg, 0, 0, 0, 0, $size[0], $size[1]);
      }
      $filename = uniqid() . '.jpg';
      imagejpeg($resultImg, $filename, 50);
      array_push($arrResource, $filename);
    }
  }
  return $arrResource;
}*/

  /**
   * @return string
   */
  private function getDefaultTemplate(): string {
    $htmlTemplate = 'Default Template<br>';
    foreach ($_REQUEST as $k => $v) {
      $htmlTemplate .= "<div>$k: $v</div>";
    }
    return $htmlTemplate;
  }

}

