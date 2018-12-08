<?php 
/*
 ============================================================================
 Project name        : SP Share
 Author      : Samnang Chay, Id: 2321
 Version     : 1.0.0
 Copyright   : 2018
 Description : CSE5382
 ============================================================================
 */

include "server.php";
$duration = 300; //set session timeout in seconds

if (!isset($_SESSION['username'])) {
 	$_SESSION['timeout'] = "You must log in first";
 	header('location: login.php');
}

	if (isset($_SESSION["username"])){ 
		if(isset($_SESSION['last_action'])){
		    $secondsInactive = time() - $_SESSION['last_action'];
		    if($secondsInactive >= $duration){
		        unset($_SESSION['username']);
		        $_SESSION['timeout'] = "We're sorry. <br>Your Session has timed out due to inactivity [".$duration."s]";
				header('location: login.php');
		    }
		}
		$_SESSION['last_action'] = time();
	}

if (isset($_GET['logout'])) {
 	session_destroy();
 	unset($_SESSION['username']);
 	header("location: login.php");
}

if ($_SESSION['UserRight']!="admin"){
    unset($_SESSION['username']);
    $_SESSION['timeout'] = "Unauthorized Access!";
	header('location: login.php');
}


?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>SP Share Admin Profile</title>
	<link rel="stylesheet" type="text/css" href="style2.css">
	<style type="text/css">
	.auto-style1 {
		text-align: right;
	}
	.auto-style2 {
		text-align: left;
	}
	</style>
	</head>
<body>

<div class="header" style="width: 100%">
	<h2>Admin Page</h2>
</div>
<div class="content" style="width: 100%">
  	<!--show message -->
  	<?php if (isset($_SESSION['success'])) : ?>
      <div class="error success" >
      	<h3>
          <?php 
          	echo $_SESSION['success']; 
          	unset($_SESSION['success']);
          ?>
      	</h3>
      </div>
  	<?php endif ?>
	
	
    <!-- logged in user information -->
    <?php  if (isset($_SESSION['username'])) : ?>
	<form method="post" action="admin.php" style="width: 85%">    	
    	<p>Welcome <strong><?php echo ucwords(strtolower($_SESSION['username'])); ?></strong></p>
	    <p>&nbsp;</p>
	    <p>Please select the following action to perform: </p>
		<p><a href="viewItems.php">View All Items</a>&nbsp;|&nbsp;<a href="index.php?logout='1'" style="color: red;">Logout</a> </p>
		<p>&nbsp;</p>
		<p>List of available and pending groups:</p>
	
		<table cellpadding="1">
			<tbody class="auto-style2">
			<tr>
				<td>
					<table border='1' cellpadding="0" cellspacing="0">
					<tr>
					<th>&nbsp;GId&nbsp;</th>
					<th>&nbsp;Group Name&nbsp;</th>
					<th>Initiator</th>
					<th>&nbsp;Status&nbsp;</th>
					<th>&nbsp;Set Status </th>
					<th>&nbsp;Delete Group? </th>						
					
					</tr>
					<?php
					$db = db_connect();
					$sql="SELECT * FROM groups ORDER BY GId";
					$result = mysqli_query($db, $sql);
		
					if (mysqli_num_rows($result) > 0) : 
						while($row = mysqli_fetch_assoc($result)) { ?>
							<tr>
							<td><?php echo $row["GId"] ?></td>
							<td><?php echo $row["groupname"] ?></td>
							<td><?php echo $row["initiator"] ?></td>
							<td><?php echo $row["status"]?></td>
							<td class="auto-style1">
							<select name="<?php echo $row["GId"] ?>" style="width: 51px">
								<option selected=""></option>
								<option value="0">&nbsp;&nbsp;0</option>
								<option value="1">&nbsp;&nbsp;1 </option>
								<option value="2">&nbsp;&nbsp;2 </option>
								</select></td>
							<td class="auto-style1">
								<input name="GId" type="hidden" value="<?php echo $row['GId']; ?>">
								<button type='submit' name='actionAdm' value="deleteGrp">Delete</button>
							</td>
							</tr>

					<?php
						}
					endif
					?>
							<tr>
							<td class="auto-style1" colspan="5">
						  	  		<?php if(mysqli_num_rows($result)==0) { 
						  	  			echo "No record.";
						  	  			echo "<button type='submit' name='actionAdm' value='group' disabled>Set Approval</button>";
						  	  		}else {
						  	  			echo "<button type='submit' name='actionAdm' value='group'>Set Approval</button>";
						  	  		}
						  	  		?>	
							</td>
							</tr>
					</table>
				</td>
				
			</tr>
			<tr>
				<td>
					<strong><u>Note</u>:</strong> status code 
					'0'/Null: Need decision;
					'1': Approved; 
					'2': Not approved.</td>
				<td>
				</td>
			</tr>
		</table>
		<p>&nbsp;</p>
		
		
		
		<p>&nbsp;</p>
		<p>Create group here by entering a group name:&nbsp;&nbsp; <input type="text" name="createGrpName" maxlength="24" >
		<button type='submit' name='actionAdm' value="createGrp">Create</button>
				
		</p>
		
		
		
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		
		
		
		<div class="input-group">
  	  	  Create a user here by completing the following user information:<br>
		<table style="width: 100%">
			<tr>
				<td>Full name</td>
				<td>
  	  <label>Username</label></td>
				<td>
  	  <label>Email</label></td>
				<td>
  	  <label>Password</label></td>
				<td>
  	  <label>Re-enter password</label></td>
				<td>Assign group</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td> <input type="text" name="fullname" value="<?php echo $fullname; ?>" maxlength="80"></td>
				<td>
  	  <input type="text" name="username" value="<?php echo $username; ?>" maxlength="16"></td>
				<td>
  	  <input type="email" name="email" value="<?php echo $email; ?>" maxlength="30"></td>
				<td>
  	  <input type="password" name="password_1"  maxlength="30"></td>
				<td>
  	  <input type="password" name="password_2"  maxlength="30"></td>
				<td>
  	  <input type="text" name="groupname" list="grp" value="<?php echo $groupname; ?>" maxlength="24" />
  	  
  	  		<?php
		    $db = db_connect();
		  	$sql="SELECT * FROM groups ORDER BY GId";
		  	$results = mysqli_query($db, $sql);
		  	
		  	if (mysqli_num_rows($results) > 0) {
		  	  	while ($row = mysqli_fetch_assoc($results)) {
		  	  		echo '<datalist id="grp">"';
		  	  		echo '<option value="'.$row["groupname"].'">'.$row["groupname"].'</option>';
		  	  	}
		  	}		  	  		
		?>
		
  	  </td>
				<td class="auto-style1">
  	  <button type="submit" class="btn" name="createUser">Register</button>
  				</td>
			</tr>
		</table>
		<?php include('errors.php'); ?>
		</div>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
    	<p>List of available users, and pending user registration for approval:</p>
	
		<table cellpadding="1">
			<tbody class="auto-style2">
			<tr>
				<td>
					<table border='1' cellpadding="0" cellspacing="0">
					<tr>
					<th>&nbsp;Id&nbsp;</th>
					<th>&nbsp;Fullname&nbsp;</th>
					<th>&nbsp;Username&nbsp;</th>
					<th>&nbsp;Email&nbsp;</th>
					<th>Request&nbsp;Group&nbsp;</th>
					<th>&nbsp;Status&nbsp;</th>
					<th>&nbsp;Set Status </th>
					<th>&nbsp;Delete User? </th>												
					</tr>
					<?php
					$db = db_connect();
					//$sql="SELECT * FROM `users` WHERE NOT role='1' AND (status='' or status='0') ORDER BY Id";
					$sql="SELECT * FROM `users` WHERE NOT role='1' ORDER BY Id";
					$result = mysqli_query($db, $sql);
		
					if (mysqli_num_rows($result) > 0) : 
						while($row = mysqli_fetch_assoc($result)) { ?>
							<tr>
							<td style="height: 22px"><?php echo $row["Id"] ?></td>
							<td style="height: 22px"><?php echo $row["fullname"] ?></td>
							<td style="height: 22px"><?php echo $row["username"] ?></td>
							<td style="height: 22px"><?php echo $row["email"] ?></td>
							<td style="height: 22px"><?php echo $row["groupname"] ?></td>
							<td style="height: 22px"><?php echo $row["status"]?></td>
							<td class="auto-style1" style="height: 22px">
							<select name="<?php echo $row["username"] ?>" style="width: 55px">
								<option value="" selected></option>
								<option value="0">&nbsp;&nbsp;0</option>
								<option value="1">&nbsp;&nbsp;1 </option>
								<option value="2">&nbsp;&nbsp;2 </option>
								</select></td>
							<td class="auto-style1">
								<input name="Id" type="hidden" value="<?php echo $row['Id']; ?>">
								<button type='submit' name='actionAdm' value="deleteUser">Delete</button>
							</td>
								
							</tr>

					<?php
						}
					endif
					?>
							<tr>
							<td class="auto-style1" colspan="7">
						  	  		<?php if(mysqli_num_rows($result)==0) { 
						  	  			echo "No record.";
						  	  			echo "<button type='submit' name='actionAdm' value='user' disabled>Set Approval</button>";
						  	  		}else {
						  	  			echo "<button type='submit' name='actionAdm' value='user'>Set Approval</button>";
						  	  		}
						  	  		?>	
							</td>
							</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					<strong><u>Note</u>:</strong> status code 
					'0'/Null: Need decision;
					'1': Approved; 
					'2': Not approved.</td>
				<td>
				</td>
			</tr>
		</table>
		<br>Limit space consumption: <br>
					<table border='1' cellpadding="0" cellspacing="0">
					<tr>
					<th>Per type</th>
					<th>Current Space Limit</th>
					<th>&nbsp;Set space in MB/KB<br>(e.g. 4.5M, 1450K)</th>
						
					</tr>
					<?php
					$db = db_connect();
					$sql="SELECT * FROM space_limit";
					$result = mysqli_query($db, $sql);
		
					if (mysqli_num_rows($result) > 0) : 
						while($row = mysqli_fetch_assoc($result)) { ?>
							<tr>
							<td style="height: 22px"><?php echo $row["type"] ?></td>
							<td style="height: 22px"><?php echo $row["size"] ?></td>
							<td style="height: 22px" class="auto-style1">
								<input type="text" name="<?php echo $row["type"]."_size"; ?>" value="<?php echo $row["size"] ?>" maxlength="6"></td>
							</tr>
					<?php
						}
					endif
					?>
							<tr>
							<td class="auto-style1" colspan="3">
						  	  		<?php if(mysqli_num_rows($result)==0) { 
						  	  			echo "No record.";
						  	  			echo "<button type='submit' name='setSLimit' value='' disabled>Set Now</button>";
						  	  		}else {
						  	  			echo "<button type='submit' name='setSLimit' value=''>Set Now</button>";
						  	  		}
						  	  		?>	
							</td>
							</tr>
					</table>
		<br> <br>
		</form>
    <?php endif ?>
</div>
		
</body>
</html>
