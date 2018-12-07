<?php
function db_connect() {
    static $conn;
    if(!isset($conn)) {
        $config = parse_ini_file('../config/db_config.ini');
        $conn = mysqli_connect($config['dbhost'], $config['username'], $config['password'], $config['dbname']);
    }
    if($conn === false) {
        return mysqli_connect_error();
    }
    return $conn;
}
?>	