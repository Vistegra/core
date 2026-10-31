<?php if (!defined('MAIN_ACCESS')) die('access denied!');

/**
 * @var string $fmAction
 */

$config = [
  'extensions_for_editor' => array('ab', 'txt', 'php', 'js', 'tpl',
                                   'html', 'htm', 'css', 'text', 'json', 'lng', 'xml', 'ini', 'sql') //Allowed extensions
];

if (isset($fmAction)) {
  switch ($fmAction) {
    case 'showTable':
      if (isset($dir) && $dir) $dir = urldecode($dir);

      function formatBytes($size) {
        $sizes = ['b', 'kb', 'mb', 'gb', 'tb'];
        $retString = '%01.2f %s';
        $lastsizestring = end($sizes);
        foreach ($sizes as $sizeString) {
          if ($size < 1024) break;
          if ($sizeString !== $lastsizestring) $size /= 1024;
        }
        if ($sizeString === $sizes[0]) {
          $retString = '%01d %s';
        } // Bytes aren't normally fractional
        return sprintf($retString, $size, $sizeString);
      }

      function folderSize($path) {
        if (!file_exists($path)) return 0;
        if (is_file($path)) return filesize($path);
        $ret = 0;
        foreach (glob($path . "/*") as $fn)
          $ret += folderSize($fn);
        return $ret;
      }

      function slashes($str) {
        return str_replace("//", "/", $str);
      }

      $html = '<table id="ab-list-pages"><thead><tr><th>' . basename($dir) . '</th><th>Размер</th><th>Действие</th></tr></thead>';
      $curTime = time();

      if (stream_resolve_include_path($dir)) {
        $files = scandir($dir);
        array_shift($files);
        array_shift($files);
        natcasesort($files);


        if (count($files) > 2) {
          foreach ($files as $file) {
            if (stream_resolve_include_path($dir . $file) && filetype($dir . $file) == 'dir') {

              // if folder
              $foldersize = '<small>' . formatBytes(folderSize($dir . $file)) . '</small>';
              $folderpath = slashes($dir . $file . '/');

              $html .= '<tr class="lightgray">
                          <td class="ab-tdfolder"><a href="' . $folderpath . '" class="closed">' . $file . '</a></td>
                          <td>' . $foldersize . '</td>
                          <td><a class="ab-btn btn-danger delete-directory" title="Удалить папку" href="' . $folderpath . '"><i class="fa fa-trash" aria-hidden="true"></i></a>
                          <!--<button class="ab-btn blue renamefolder" title="Переименовать папку"><i class=" fa fa-random" aria-hidden="true"></i></button>-->
                          <a class="ab-btn asphalt downloadfolder" title="Скачать архивом" data-action="downloadFolder" data-path="' . $folderpath . '"><i class="fa fa-download" aria-hidden="true"></i></a></td></tr>';

            }
          }

          foreach ($files as $file) { //if files

            if (stream_resolve_include_path($dir . $file) && filetype($dir . $file) !== 'dir') {
              $filepath = slashes($dir . $file);

              $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ||
                           $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

              $current_url = $protocol . $_SERVER['SERVER_NAME'] . '/';

              //$filepath = mb_strtolower($filepath);
              $url = str_replace(mb_strtolower($_SERVER['DOCUMENT_ROOT']), $current_url, $filepath); // исрпавить

              $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

              $size = '<small>' . formatBytes(filesize($filepath)) . '</small>';
              $newFileIcon = filectime($filepath) - $curTime > -1800
                ? '<i class="ml-2 fas fa-plus" aria-hidden="true" style="color: red" title="Новый файл"></i>' : '';

              $html .= '<tr class="white">
                          <td class="ab-tdfile"><span class="ext-file ext-' . $ext . '">' . $file . $newFileIcon .'</i></span></td>
                          <td>' . $size . '</td>
                          <td><a href="' . $filepath . '" class="ab-btn btn-danger delete-file" title="Удалить файл"><i class="fa fa-trash" aria-hidden="true"></i></a>
                          <!--<button class="ab-btn blue renamefile" title="Переименовать файл"><i class=" fa fa-random" aria-hidden="true"></i></button>-->
                          <a class="ab-btn asphalt downloadfile" title="Скачать файл" data-action="downloadFile" data-path="' . $filepath . '"><i class="fa fa-download" aria-hidden="true"></i></a>';

              //if image file
              if (in_array($ext, array("jpg", "jpeg", "png", "gif", "ico", "bmp", 'svg'))) {
                $html .= '<a class="ab-btn green zoom" href="' . $url . '" target="_blank"><i class="fa fa-eye" aria-hidden="true"></i></a>';

                //if edited file
              } elseif (in_array($ext, $config['extensions_for_editor'])) {
                //$html .= '<a class="ab-btn violet ab-edit-file" href="editor.php?editfile=' . $filepath . '" target="_blank" title="Редактировать"><i class="fa fa-pencil" aria-hidden="true"></i></a></td></tr>';
              }

              $html .= '</td></tr>';
            }
          }
        } else {
          $html .= '<tr class="lightgray"><td>---</td><td>---</td><td>---</td></tr>';
        }

      } else {
        die('Not found - ' . $dir);
      }

      $html .= '</table>';
      break;
    case 'createFolder':
      if (isset($dir) && $dir) {
        mkdir($dir, 0777, true);
      }
      break;
    case 'renameFolder':
      if (isset($oldName) && $oldName && isset($newName) && $newName) {
        rename($oldName, $newName);
      }
      break;
    case 'deleteFolder':
      function deletefolder($dir) {

        $iterator = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
          if ($file->isDir()) {
            rmdir($file->getRealPath());
          } else {
            unlink($file->getRealPath());
          }
        }
        rmdir($dir);
        return true;
      }

      if (isset($dir) && $dir && file_exists($dir)) {
        deletefolder($dir);
      }
      break;
    case 'deleteFile':
      if (isset($dir) && $dir && file_exists($dir)) {
        unlink($dir);
      }
      break;
    case 'downloadFolder':
      if (isset($dir) && $dir) {

        $folder = basename($dir);

        if (!empty($folder)) $zip_file = $folder . '.zip';
        else $zip_file = 'download.zip';


        // Get real path for our folder
        $rootPath = realpath($dir);

        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
          new RecursiveDirectoryIterator($rootPath),
          RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
          // Skip directories (they would be added automatically)
          if (!$file->isDir()) {
            // Get real and relative path for current file
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($rootPath) + 1);

            // Add current file to archive
            $zip->addFile($filePath, $relativePath);
          }
        }

        // Zip archive will be created only after closing object
        $zip->close();

        if (ob_get_level()) ob_end_clean();

        header('FileName: ' . json_encode(basename($zip_file)));
        header('Content-Length: ' . filesize($zip_file));
        if (readfile($zip_file)) {
          $zip = dirname(__FILE__) . '/' . $zip_file;
          unlink($zip);
        }
      }
      break;
    case 'downloadFile':
      if (isset($dir) && $dir) {

        $file = urldecode($dir); // Decode URL-encoded string

        if (file_exists($file)) {

          // сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
          // если этого не сделать файл будет читаться в память полностью!
          if (ob_get_level()) {
            ob_end_clean();
          }

          header('FileName: ' . json_encode(basename($file)));
          header('Content-Length: ' . filesize($file));

          if ($fd = fopen($file, 'rb')) {
            while (!feof($fd)) {
              print fread($fd, 1024);
            }
            fclose($fd);
          }
          exit;
        }
      }
      break;
    case 'uploadFile':
      if (count($_FILES['files']['name']) && isset($dir) && $dir) {

        for ($i = 0; $i < count($_FILES['files']['name']); $i++) {

          $shortname = $_FILES['files']['name'][$i];

          //Get the temp file path
          $tmpFilePath = $_FILES['files']['tmp_name'][$i];

          //Make sure we have a filepath
          if ($tmpFilePath !== "") {

            //save the url and the file
            $filePath = $dir . $_FILES['files']['name'][$i];

            //Upload the file into the temp dir
            move_uploaded_file($tmpFilePath, $filePath);
          }
        }
      }
      break;
  }
}

$result['html'] = $html ?? '';
