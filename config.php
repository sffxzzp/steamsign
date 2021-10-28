<?php
$sqlInfo = array(
    "host"  =>  getenv('MYSQLHOST').':'.getenv('MYSQLPORT'),
    "user"  =>  getenv('MYSQLUSER'),
    "pwd"   =>  getenv('MYSQLPASSWORD'),
    "db"    =>  getenv('MYSQLDATABASE')
);
$key = getenv('STEAMAPIKEY');

$prefix = 'ss_';
$tabletime = $prefix.'time';
$tablestorage = $prefix.'storage';