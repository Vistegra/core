<?php

define('INSTALL_PATH', 'model/');

// Папка сайта
$root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$dir = str_replace($root, '', __DIR__ . '/');
$dir = str_replace(INSTALL_PATH, '', $dir);

//echo var_dump($_GET);
extract($_GET);

if(isset($ready)) {
	$upDir = '../';

	if ($pathSite === '') {
		$pathSite = '/';
	}

	if ($server === 'Apache') {
		$serverFile        = '.htaccess';
		$serverFileContent = <<<conf
# Закрыть доступ всем
# deny from all

Options +FollowSymLinks
RewriteEngine on
RewriteBase $pathSite
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([^?]*) $pathSite\index.php?targetPage=$1 [L,QSA]
conf;
	} else {
		$serverFile        = $nginxFile;
		$serverFileContent = <<<conf
server {
    listen         %ip%:%httpport%;
    listen         %ip%:%httpsport% ssl;
    server_name    %host% %aliases%;
    
    ssl_certificate               "%sprogdir%/userdata/config/cert_files/server.crt";
    ssl_certificate_key           "%sprogdir%/userdata/config/cert_files/server.key";
    
    #add_header Strict-Transport-Security "max-age=94608000";
    
    # if (\$request_method !~* ^(GET|HEAD|POST)$ ){return 403;}
    location ~ /\. {deny all;}

    location / {
        root       "%hostdir%";
        index      index.php;
        # Конфиг чпу
        #if (!-e \$request_filename) {
        #  rewrite ^(.*)$ /index.php break;
        #}

        #try_files \$uri \$uri/ /index.php?\$args bre;
        rewrite ^[?<=\/](.*)$ /index.php?targetPage=$1;

    }

    location ~ \.(js|css|png|jpg|gif|swf|svg|ico|pdf|mov|fla|zip|rar)$ {
        root "%hostdir%";
        try_files \$uri =404;
    }

    location ~ \.(eot|otf|ttf|woff|woff2|fbx|obj)$ {
        root "%hostdir%";
        try_files \$uri =404;
    }

    location ~ \.php$ {
        root                               "%hostdir%";
        try_files                          \$uri =404;
        # if (!-e \$document_root\$document_uri){return 404;}
        fastcgi_pass                       backend;
        fastcgi_index                      index.php;
        fastcgi_buffers                    4 64k;
        fastcgi_connect_timeout            1s;
        fastcgi_ignore_client_abort        off;
        fastcgi_next_upstream              timeout;
        fastcgi_read_timeout               5m;
        fastcgi_send_timeout               5m;
        fastcgi_param    CONTENT_TYPE      \$content_type;
        fastcgi_param    CONTENT_LENGTH    \$content_length;
        fastcgi_param    DOCUMENT_URI      \$document_uri;
        fastcgi_param    DOCUMENT_ROOT     \$document_root;
        fastcgi_param    GATEWAY_INTERFACE CGI/1.1;
        fastcgi_param    HTTPS             \$https;
        fastcgi_param    QUERY_STRING      \$query_string;
        fastcgi_param    REQUEST_METHOD    \$request_method;
        fastcgi_param    REQUEST_URI       \$request_uri;
        fastcgi_param    REMOTE_ADDR       \$remote_addr;
        fastcgi_param    REMOTE_PORT       \$remote_port;
        fastcgi_param    SERVER_ADDR       \$server_addr;
        fastcgi_param    SERVER_PORT       \$server_port;
        fastcgi_param    SERVER_NAME       \$host;
        fastcgi_param    SERVER_PROTOCOL   \$server_protocol;
        fastcgi_param    SERVER_SOFTWARE   nginx;
        fastcgi_param    SCRIPT_FILENAME   \$document_root\$fastcgi_script_name;
        fastcgi_param    SCRIPT_NAME       \$fastcgi_script_name;
        fastcgi_param    TMP               "%sprogdir%/userdata/temp";
        fastcgi_param    TMPDIR            "%sprogdir%/userdata/temp";
        fastcgi_param    TEMP              "%sprogdir%/userdata/temp";
    }
}
conf;
	}

	file_put_contents($upDir . $serverFile, $serverFileContent);

	$config = <<<config
<?php //if ( !defined('MAIN_ACCESS')) die('access denied!');

// Путь от корня сайта
if ( !defined('SITE_PATH')) define('SITE_PATH', '$pathSite');

if ( !defined('CONTROLLER')) define('CONTROLLER', 'controller/c_');
if ( !defined('VIEW')) define('VIEW', 'views/');

if ( !defined('PATH_CSS')) define('PATH_CSS', 'assets/css/');
if ( !defined('PATH_IMG')) define('PATH_IMG', 'assets/images/');
if ( !defined('PATH_SCRIPT')) define('PATH_SCRIPT', 'assets/js/');

//----------------------------------------------------------------------------------------------------------------------
// DB connect/config
\$dbConfig = [
	'dbHost'     => '$dbHost',
	'dbName'     => '$dbName',
	'dbUsername' => '$dbUsername',
	'dbPass'     => '$dbPass'
];
config;

	file_put_contents($upDir . 'config.php', $config);
}

?>

<form action="<?= $_SERVER['SCRIPT_NAME'] ?>">
  <?php if(1) { ?>
  <p>Сайт</p>
  <label>Папка сайта: <input type="text" name="pathSite" value="<?= $dir ?>"></label>
  <br><small>со слэшами с обоих сторон</small>

  <p>Сервер</p>
  <br><label>Apache: <input type="radio" name="server" value="Apache"></label>
  <br><label>Nginx: <input type="radio" name="server" value="Nginx" checked></label>
  <br><label>Nginx Версия: <input type="text" name="nginxFile" value="Nginx_1.17_vhost.conf"></label>

  <p>БД</p>
  <br><label>Хост: <input type="text" name="dbHost" value="localhost"></label>
  <br><label>Имя БД: <input type="text" name="dbName"></label>
  <br><label>Пользователь: <input type="text" name="dbUsername" value="admin"></label>
  <br><label>Пароль: <input type="text" name="dbPass"></label>

    <br><label>Готово: <input type="checkbox" name="ready" value="1"></label>
  <button>Установить</button>
  <?php } else echo 'ok' ?>
</form>


