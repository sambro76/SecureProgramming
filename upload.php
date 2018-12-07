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
 
session_start();

if (isset($_SESSION["username"])){ 

	require_once "config.php";
	require_once "fileupload.class.php";
	require_once "db_connect.php";
	$db = db_connect();
	
	if (isset($_SESSION['group'])) {
		$query = "SELECT status FROM groups WHERE groupname='".$_SESSION['group']."'";
		$results = mysqli_query($db, $query);
		if (mysqli_num_rows($results) == 0) {
			$_SESSION['timeout'] = "Your group information is not found. Please contact system administrator.";
			header('location: login.php');
		}
	}

	if (isset($_POST['upload'])) {
		$img = new fileUpload;
		$result = $img->uploadImages($_FILES['image'], $_SESSION["username"]);

		if(!empty($result->error)){
			foreach($result->error as $errMsg){
				echo $errMsg;
			}
		} 
		if(!empty($result->info)){
			foreach($result->info as $infoMsg){
				$_SESSION['success'] .= $infoMsg.'<br />';
			//	echo $infoMsg.'<br />';
			}
			$i = 0;
			$locVar = "";
			foreach($result->ids as $id){
				$i = $i + 1 ; 
				$locVar=$locVar."file".$i."=".$id."&";
			}
			//$_SESSION['success'] = $locVar;
			$_SESSION['showImage'] = "".$i;
			unset($_POST['upload']);
	 		header('location: userpage.php?'.$locVar);
		}
	}

	if (isset($_POST['Update'])) {
		$total = $_SESSION['showImage'];

		for($i=1; $i<=$total; $i++) {
			$id = mysqli_real_escape_string($db, $_POST['id'.$i]);
			$iName = mysqli_real_escape_string($db, $_POST['iName'.$id]);
			$iDesc = mysqli_real_escape_string($db, $_POST['iDesc'.$id]);
			$iName = htmlentities($iName, ENT_QUOTES);
			$iDesc = htmlentities($iDesc, ENT_QUOTES);
			
			$sql="UPDATE items SET `i_name`='".$iName."', `i_description`='".$iDesc."' WHERE `id`='".$id."' ";
			mysqli_query($db, $sql);	
		}
		unset($_SESSION['showImage']);
		header('location: userpage.php');
	}

} else {
	header('location: index.php?logout=1');
}
/*

if(!empty($result->ids)){
	foreach($result->ids as $id){
		echo "".F_PATH.H_FILE."?". $id;
	}
}

*/