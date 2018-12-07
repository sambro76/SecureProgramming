<?php
/*
 ============================================================================
 Name        : SP Share
 Author      : Samnang Chay, Id: 2321
 Version     : 1.0.0
 Copyright   : 2018
 Description : CSE5382-Assignment 3
 ============================================================================
 */
session_set_cookie_params(0);

session_start();

if (isset($_SESSION["username"])){ 
	require_once "config.php";
	require_once "fileupload.class.php";

	$img = new fileUpload;
	$img->showImage($_GET['id']);
}

?>
