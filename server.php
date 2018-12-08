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

$fullname = "";
$username = "";
$email = "";
$groupname = "";
$errors = array(); 

include 'db_connect.php';

//REGISTER USER
if (isset($_POST['reg_user'])) {
  $db = db_connect();
  //Receive all input values from the form
  $fullname = mysqli_real_escape_string($db, $_POST['fullname']);
  $username = mysqli_real_escape_string($db, $_POST['username']);
  $email = mysqli_real_escape_string($db, $_POST['email']);
  $password_1 = mysqli_real_escape_string($db, $_POST['password_1']);
  $password_2 = mysqli_real_escape_string($db, $_POST['password_2']);
  $groupname = mysqli_real_escape_string($db, $_POST['groupname']);

  if (!(preg_match("/^.{1,80}$/i", $fullname))) {array_push($errors, "Fullname format .{1,80}");}
  if (!(preg_match("/^.{2,16}$/i", $username))) {array_push($errors, "Username format .{2,16}");}  
  if (!(preg_match("/^[^@\s<&>]+@([-a-z0-9]+\.)+[a-z]{2,}$/i", $email))){array_push($errors, "Email is invalid");}
  if (!(preg_match("/^\S*(?=\S{6,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$/i", $password_1))){array_push($errors, "Password need to be min. 6 ASCII characters and at least one a-z, A-Z, a number and a special character");}
  if ($password_1 != $password_2) {array_push($errors, "The two passwords do not match");}
  if (!(preg_match("/^[A-Za-z-\'\ ]{2,24}$/i", $groupname))){array_push($errors, "Groupname format is invalid");}
  
  //Check duplicate username and/or email
  $sql = "SELECT * FROM users WHERE username='".$username."' OR email='".$email."' LIMIT 1";
  $result = mysqli_query($db, $sql);
  $user = mysqli_fetch_assoc($result);
  
  if ($user) { // if user exists
    if ($user['username'] === $username) {
      array_push($errors, "Username already exists");
    }
    if ($user['email'] === $email) {
      array_push($errors, "email already exists");
    }
  }

  //Finally, register user if there are no errors in the form
  if (count($errors) == 0) {
	$sql = "SELECT * FROM groups WHERE groupname='".$groupname."' LIMIT 1";
	$result = mysqli_query($db, $sql);
	  
	if(mysqli_num_rows($result) == 0) {
		$sql = "INSERT INTO groups VALUES ('','".$groupname."','".$username."','')";
		mysqli_query($db, $sql);
	}
	
	$password = password_hash($password_1, PASSWORD_BCRYPT, array('cost' => 10));
	$sql = "INSERT INTO users VALUES('','".$fullname."','".$username."', '".$email."', '".$password."','".$groupname."','','')";
	mysqli_query($db, $sql);
	
	$_SESSION['success'] = "Your registration form has been submitted successfully.<br>Please wait...";
	header('location: post_register.php');
  }
}

//Create user by Admin
if (isset($_SESSION["username"])){ 
	$db = db_connect();
	$query = "SELECT * FROM users WHERE username='".$_SESSION["username"]."' and role='1'";
  	$results = mysqli_query($db, $query);
  	
 	if (mysqli_num_rows($results) == 1) {
		if (isset($_POST['createUser'])) {
		  //Receive all input values from the form
		  $fullname = mysqli_real_escape_string($db, $_POST['fullname']);
		  $username = mysqli_real_escape_string($db, $_POST['username']);
		  $email = mysqli_real_escape_string($db, $_POST['email']);
		  $password_1 = mysqli_real_escape_string($db, $_POST['password_1']);
		  $password_2 = mysqli_real_escape_string($db, $_POST['password_2']);
		  $groupname = mysqli_real_escape_string($db, $_POST['groupname']);

		  if (!(preg_match("/^.{1,80}$/i", $fullname))) {array_push($errors, "Fullname is invalid");}
		  if (!(preg_match("/^.{2,16}$/i", $username))) {array_push($errors, "Username is invalid");}  
		  if (!(preg_match("/^[^@\s<&>]+@([-a-z0-9]+\.)+[a-z]{2,}$/i", $email))){array_push($errors, "Email is invalid");}
		  if (!(preg_match("/^\S*(?=\S{6,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$/i", $password_1))){array_push($errors, "Password is invalid");}
		  if ($password_1 != $password_2) {array_push($errors, "The two passwords do not match");}
		  if (!(preg_match("/^[A-Za-z-\'\ ]{1,24}$/i", $groupname))){array_push($errors, "Groupname is invalid");}
		    
		  //Check duplicate username and/or email
		  $sql = "SELECT * FROM users WHERE username='".$username."' OR email='".$email."' LIMIT 1";
		  $result = mysqli_query($db, $sql);
		  $row = mysqli_fetch_assoc($result);
		  
		  if ($row) { // if user exists
		    if ($row['username'] === $username) {
		      array_push($errors, "Username already exists");
		    }
		    if ($row['email'] === $email) {
		      array_push($errors, "email already exists");
		    }
		  }

		  //Finally, register user if there are no errors in the form
		  if (count($errors) == 0) {
			$sql = "SELECT * FROM groups WHERE groupname='".$groupname."' LIMIT 1";
			$result = mysqli_query($db, $sql);
			  
			if(mysqli_num_rows($result) == 0) {
				$sql = "INSERT INTO groups VALUES ('','".$groupname."','".$_SESSION["username"]."','1')";
				mysqli_query($db, $sql);
			}
			
			$password = password_hash($password_1, PASSWORD_BCRYPT, array('cost' => 10));
			$sql = "INSERT INTO users VALUES('','".$fullname."','".$username."', '".$email."', '".$password."','".$groupname."','','1')";
			mysqli_query($db, $sql);

			$_SESSION['success'] = "User ".$fullname." with username='".$username."' is created and active.";
			//header('location: admin.php');
	  		}
	  	}	
	} else {
		array_push($errors, "You are not authorized.");
	}
}
			

//LOGIN USER
if (isset($_POST['login_user'])) {
  $db = db_connect();
  $username = mysqli_real_escape_string($db, $_POST['username']);
  $password = mysqli_real_escape_string($db, $_POST['password']);

  if (empty($username)) {
  	array_push($errors, "Username is required");
  }
  if (empty($password)) {
  	array_push($errors, "Password is required");
  }

  if (count($errors) == 0) {
  	$query = "SELECT * FROM users WHERE username='".$username."'";
  	$results = mysqli_query($db, $query);
  	
  	if (mysqli_num_rows($results) == 1) {
  	  	while ($row = mysqli_fetch_assoc($results)) {
  	  		$pwd = $row["password"];
			$group = $row["groupname"];
  	  		$role = $row["role"];
  	  		$status = $row["status"];
  	  	}
	  	if ($role=="1"){ //role '1' is an admin previlege
		  	if (password_verify($password, $pwd)) {
				  $_SESSION['username'] = $username;
				  $_SESSION['UserRight']="admin";
			  	  $_SESSION['success'] = "You are now logged in";
				  $_SESSION['last_action'] = time();
				  header('location: admin.php');
			} else {
				array_push($errors, "Wrong username/password");
			}
		}else {
			if($status=='1') {
				if (password_verify($password, $pwd)) {
					$query = "SELECT status FROM groups WHERE groupname='".$group."'";
					$results = mysqli_query($db, $query);
					if (mysqli_num_rows($results) == 1) {
						$row = mysqli_fetch_assoc($results);
						$grpStatus = $row["status"];
						
						if($grpStatus=='1') {
							$_SESSION['username'] = $username;
							$_SESSION['group'] = $group;
							$_SESSION['UserRight']="user";			  		  
							$_SESSION['success'] = "You are now logged in";
							$_SESSION['last_action'] = time();
							header('location: userpage.php');
						}
						if($grpStatus=='2') {
							array_push($errors, "Your group is inactive. You can not use SP Share for now. Please contact system administrator.");
						}
						if($grpStatus=='' || $grpStatus=='0') {
							array_push($errors, "Your group is pending for decision. Please wait...");
						}
					} else {
						array_push($errors, "Your group information is not found. Please contact system administrator.");
					}						
			  	} else {
			  	 	array_push($errors, "Wrong username/password");
			  	}
			} 
			if($status=='2') {
				  	  array_push($errors, "Sorry, you are not approved, please contact system administrator.");	
			}
			if($status=='' || $status=='0') {
			  		  array_push($errors, "Your registration is pending for approval");			
			}
		}
	 
  	}else {
  		array_push($errors, "Wrong username/password");
  	}
  }
}

//Admin activities
if (isset($_SESSION["username"])){ 
		
	if (isset($_POST['actionAdm'])) {
		//Approve USER
		if ($_POST['actionAdm']=='user') {
		    $db = db_connect();
		  	//$sql="SELECT * FROM `users` WHERE NOT role='1' AND (status='' or status='0') ORDER BY Id";
			$sql="SELECT * FROM `users` WHERE NOT role='1' ORDER BY Id";
		  	$results = mysqli_query($db, $sql);
		  	
		  	if (mysqli_num_rows($results) > 0) {
		  	  	while ($row = mysqli_fetch_assoc($results)) {
		  	  		$usr=$row["username"];
		  	  		if (isset($_POST[$usr])) {
		  	  			if ($_POST[$usr]!="") {
			  	  			$sql="UPDATE users SET status='".$_POST[$usr]."' WHERE username='".$usr."'";
			  	  			mysqli_query($db, $sql);
			  	  		}
		  	  		}
		 	  	}
				$msg = "Approval [User] completed";
				$_SESSION['success'] = $msg;	
		 	  	
		  	}else {
				$msg = "No record is found.";
				$_SESSION['success'] = $msg;	
		  	}
		}
		
		//Approve Group
		if ($_POST['actionAdm']=='group') {
		    $db = db_connect();
		  	$sql="SELECT * FROM groups ORDER BY GId";
		  	$results = mysqli_query($db, $sql);
		  	
		  	if (mysqli_num_rows($results) > 0) {
		  	  	while ($row = mysqli_fetch_assoc($results)) {
		  	  		$grp=$row["GId"];
		  	  		if (isset($_POST[$grp])) {
			  	  		if ($_POST[$grp]!="") {	
			  	  			$sql="UPDATE groups SET status='".$_POST[$grp]."' WHERE GId='".$grp."'";
			  	  			mysqli_query($db, $sql);
			  	  		}
		  	  		}
		 	  	}
				$msg = "Approval [Group] completed";
				$_SESSION['success'] = $msg;
		
		  	}else {
				$msg = "No record is found.";
				$_SESSION['success'] = $msg;	
		  	}
		}
		
		//Create Group by Admin
		if ($_POST['actionAdm']=='createGrp') {
			if (empty($_POST['createGrpName'])) {
				$_SESSION['success'] = "Plesae input group name.";
			} else {
				    $db = db_connect();
				    $sql = "SELECT * FROM groups WHERE groupname='".$_POST['createGrpName']."' LIMIT 1";
				  	$result = mysqli_query($db, $sql);
				  	$grp = mysqli_fetch_assoc($result);
				  
				  if ($grp) { // if group exists
				    if ($grp['groupname'] === $_POST['createGrpName']) {
				     	$msg = "Group '".$_POST['createGrpName']."' already exists!";
						$_SESSION['success'] = $msg;
						
						//$_POST['createGrpName']='';
						//$_POST['actionAdm']='';
						//unset($_POST['actionAdm']);
				    }
				  } else {
					  	$sql="INSERT INTO groups VALUES ('','".$_POST['createGrpName']."','".$_SESSION["username"]."','1')";
						mysqli_query($db, $sql);
						$msg = "Group '".$_POST['createGrpName']."' has been created...";
						$_SESSION['success'] = $msg;
						
						//$_POST['createGrpName']='';
						//$_POST['actionAdm']='';
						//unset($_POST['actionAdm']);
				  }
			}
		}
		
		//Delete Group by Admin
		if ($_POST['actionAdm']=='deleteGrp') {
		    $db = db_connect();
		    $sql = "DELETE FROM groups WHERE GId='".$_POST['GId']."'";
			if (mysqli_query($db, $sql)) {
				$msg = "Group Id:'".$_POST['GId']."' has been deleted...";
				$_SESSION['success'] = $msg;
			} else {
				$msg = "Error deleting Group Id:'".$_POST['GId']."'...";
				$_SESSION['success'] = $msg;
			}
		}

		//Delete User by Admin
		if ($_POST['actionAdm']=='deleteUser') {
		    $db = db_connect();
		    $sql = "DELETE FROM users WHERE Id='".$_POST['Id']."'";
			if (mysqli_query($db, $sql)) {
				$msg = "User has been deleted...";
				$_SESSION['success'] = $msg;
			} else {
				$msg = "Error deleting this user...";
				$_SESSION['success'] = $msg;
			}
		}

	}
	
	//Admin set limit of space consumption
	if (isset($_POST['setSLimit'])) {
		
		$db = db_connect();
		if (isset($_POST['item_size'])) {
			$sql="UPDATE space_limit SET size='".$_POST['item_size']."' WHERE type='item'";
			mysqli_query($db, $sql);
			$_SESSION['success'] = "Space limit is set.";
		}

		if (isset($_POST['user_size'])) {
			$sql="UPDATE space_limit SET size='".$_POST['user_size']."' WHERE type='user'";
			mysqli_query($db, $sql);
			$_SESSION['success'] = "Space limit is set.";
		}
		
		if (isset($_POST['group_size'])) {
			$sql="UPDATE space_limit SET size='".$_POST['group_size']."' WHERE type='group'";
			mysqli_query($db, $sql);
			$_SESSION['success'] = "Space limit is set.";
		}
	}

	$_POST = array();
}

?>
