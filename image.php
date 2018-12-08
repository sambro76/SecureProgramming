<?php
/*
 ============================================================================
 Project name: SP Share
 Author      : Samnang Chay, Id: 2321
 Version     : 1.0.0
 Copyright   : 2018
 Description : Online File Sharing with Security
 ============================================================================
 */
session_start();

if (isset($_SESSION["username"])){ 
	require_once "config.php";
	require_once "fileupload.class.php";

	$img = new fileUpload;
	$img->showImage($_GET['id']);
}

?>
