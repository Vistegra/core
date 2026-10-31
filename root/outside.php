<?php

if (defined('MAIN_ACCESS') || defined('ABS_SITE_PATH')) die('access denied!');

const MAIN_ACCESS = true;
const OUTSIDE = 'I'; // I - inline без Shadow Dom, S - Включить в тень
const ABS_SITE_PATH = __DIR__ . '/';

$homePath = get_include_path();
set_include_path(ABS_SITE_PATH);

require 'core/head.php';

set_include_path($homePath);
