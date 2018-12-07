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
include 'db_connect.php';

if (isset($_SESSION["username"])){ 
	require_once "config.php";
	require_once "fileupload.class.php";

	if (isset($_POST['select'])) {
		$img = new fileUpload;
		if ($_POST['select']==="download") {
			$img->downloadImage($_POST['id']);
		}
		if ($_POST['select']==="delete") {
		    $db = db_connect();
		    //check if user is admin or not
		    $sql = "SELECT `role` FROM `users` WHERE username='".$_SESSION['username']."'";
			$result = mysqli_query($db, $sql);
			if (mysqli_num_rows($result) > 0) {
				$row = mysqli_fetch_assoc($result);
				if ($row['role']=="1") {
		    		$sql="SELECT * FROM items WHERE id='".$_POST['id']."'";
				} else {
					$sql="SELECT * FROM items WHERE id='".$_POST['id']."' AND i_creator='".$_SESSION["username"]."'";
				}	
			  	
				$result = mysqli_query($db, $sql);
				
			  	if (mysqli_num_rows($result) == 1) {
			  		$row = mysqli_fetch_assoc($result);
			  		$data = $row['name'];
			  		$dir = "files";
			  		$dirHandle = opendir($dir);
			  		while ($file=readdir($dirHandle)) {
			  			if ($file==$data) { 
			  				if (unlink($dir."/".$file)) {
								$_SESSION['delete'] = "File '".$row['original_name']."' is deleted";
							
								$sql = "DELETE FROM items WHERE id='".$_POST['id']."'";
								if (mysqli_query($db, $sql)) {
									$_SESSION['delete'] = $_SESSION['delete'].", and also deleted from DB.";
								} else {
									$_SESSION['delete'] = $_SESSION['delete'].", but not deleted from DB!";
								}
						  	} else {
						  		$_SESSION['delete'] = "File deletion is unsuccessful!";
						  	}
						} 
				  	}
				  	closedir($dirHandle);
			  	} else {
			  		$_SESSION['delete'] = "Sorry, you are not authorized to delete item created by other user.";
			  	}
				header('location: viewItems.php');
			}
		}
	//echo $result->info;
	}
}
?>
