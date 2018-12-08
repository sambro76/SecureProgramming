<?php
/*
 ============================================================================
 Project name        : SP Share
 Author      : Samnang Chay, Id: 2321
 Version     : 1.0.0
 Copyright   : 2018
 Description : Online File Sharing with Security
 ============================================================================
 */
 
$config = parse_ini_file('../config/db_config.ini');

define("DB_HOST", $config['dbhost']);
define("DB_USER", $config['username']);
define("DB_PASS", $config['password']);
define("DB_NAME", $config['dbname']);
define("DB_TABLE", $config['dbtable']);

define("R_PATH", __DIR__);
define("F_PATH", R_PATH.'/files');
define("H_FILE", false);

?>
