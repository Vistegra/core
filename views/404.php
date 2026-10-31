<?php
$dbError = isset($_REQUEST['dbError']);
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>not found 404</title>
</head>
<body>
<?php if (!$dbError) { ?>
  <a href="/">Home</a>
  <h2>Not found</h2>
<?php } else { ?>
  <a href="/">Home</a>
  <h2>Data Base connect or other error!!</h2>
<?php } ?>
</body>
</html>
