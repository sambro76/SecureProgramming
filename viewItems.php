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
$duration = 300;

if (!isset($_SESSION['username'])) {
 	$_SESSION['timeout'] = "You must log in first";
 	header('location: login.php');
}
if (isset($_GET['logout'])) {
 	session_destroy();
 	unset($_SESSION['username']);
 	header("location: index.php");
}
include 'db_connect.php';

if (isset($_SESSION['group'])) {
	$db = db_connect();
	$query = "SELECT status FROM groups WHERE groupname='".$_SESSION['group']."'";
	$results = mysqli_query($db, $query);
	if (mysqli_num_rows($results) == 0) {
		$_SESSION['timeout'] = "Your group information is not found. Please contact system administrator.";
		header('location: login.php');
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>SP Share Home</title>
	<link rel="stylesheet" type="text/css" href="style2.css">
	<style type="text/css">
	.auto-style1 {
		text-decoration: underline;
	}
	.auto-style4 {
		border-style: solid;
		border-width: 1px;
	}
	.auto-style5 {
	border-width: 0px;
}
	.auto-style6 {
		border-style: solid;
		border-width: 1px;
		text-align: center;
	}
	.auto-style7 {
		text-align: center;
	}
	</style>
		<script>
			function load_img(element, id, style = "") {
				document.getElementById(element).innerHTML='<object type="text/html" style="'+ style +'" data="image.php?id='+ id +'"></object>';
			}
			document.addEventListener( "DOMContentLoaded", function(){
				<?php 
					$db = db_connect();
					$sql = "SELECT * FROM `users` WHERE username='".$_SESSION['username']."'";
					$result = mysqli_query($db, $sql);
					if (mysqli_num_rows($result) > 0) {
						$row = mysqli_fetch_assoc($result);
						
						if ($row['role']=="1") { 
							$sql="SELECT * FROM `items`";
							$role = 1;
						} else {
							$sql="SELECT * FROM `items`, (SELECT `username` FROM `users` WHERE `groupname`='".$row['groupname']."' AND NOT role='1') AS GetUser WHERE items.i_creator = GetUser.username ORDER by id";
							$role = 0;
						}
						$result = mysqli_query($db, $sql);

						if (mysqli_num_rows($result) > 0) {
							while ($row = mysqli_fetch_assoc($result)) {
								echo "load_img('file".$row['id']."', ". $row['id'].", 'width: 100px; border: 0px solid black;');"; 
							}
						}
					}
				?>
			});
		</script>
</head>
<body>

<div class="header" style="width: 90%">
	<h2>View Items</h2>
</div>
<div class="content" style="width: 90%">
    
	<?php 
	if (isset($_SESSION["username"])){ 
	
		if(isset($_SESSION['last_action'])){
		    $secondsInactive = time() - $_SESSION['last_action'];
		    $expireAfterSeconds = $duration;
		    if($secondsInactive >= $expireAfterSeconds){
		        session_unset();
		        session_destroy();
		        echo "We're sorry. <br>Your Session has timed out due to inactivity [".$duration."s]";
		        echo "<br><br>";
				echo "<p>Please <a href='login.php'>Sign In</a> to continue</p>";
		    }
		}
		$_SESSION['last_action'] = time();
	}
	
	?>
	
	   	<!--show message -->
  	<?php if (isset($_SESSION['success'])) : ?>
      <div class="error <?php if(!strpos($_SESSION['success'],'removed')) {echo 'success';} ?> ">
      	<h3>
          <?php 
          	echo $_SESSION['success']; 
          	unset($_SESSION['success']);
          ?>
      	</h3>
      </div>
  	<?php endif ?>

  	<?php if (isset($_SESSION['delete'])) : ?>
      <div class="error success">
      	<h3>
          <?php 
          	echo $_SESSION['delete']; 
          	unset($_SESSION['delete']);
          ?>
      	</h3>
      </div>
  	<?php endif ?>

	
    <!-- logged in user information -->
    <?php  if (isset($_SESSION['username'])) : ?>
		    <p>Welcome <strong><?php echo ucwords(strtolower($_SESSION['username'])); ?></strong></p>
			<p>Please follow the option below for user interaction. </p>
			<p>Here user can view, upload, download, and delete file(s) in the group 
			to which user belongs. </p>
			<p>&nbsp;</p>
			<p><?php if ($role==1) {
					echo "<a href='admin.php'>Admin Page</a>&nbsp;|&nbsp;";
					echo "<a href='userpage.php'>Upload Page</a>&nbsp;|&nbsp;";} 
				else {echo "<a href='userpage.php'>Upload Page</a>&nbsp;|&nbsp;";}?> 
			<a href="index.php?logout='1'" style="color: red;">Logout</a> </p>
			<p>&nbsp;</p>
			<p><span class="auto-style1"><strong>View / Delete / Download 
			Item(s)</strong></span><strong> : 
			</strong> </p>
			<div>
			    <table cellpadding="0" class="auto-style5" style="width: 100%">
					<tr>
						<th class="auto-style4">View</th>
						<th class="auto-style4">Filename</th>
						<th class="auto-style4">Mime_Type</th>
						<th class="auto-style4">Size</th>
						<th class="auto-style4">ItemName</th>
						<th class="auto-style4">Creator</th>
						<th class="auto-style4">Description</th>
						<th class="auto-style4">Created on</th>
						<th class="auto-style4">Download / Delete?</th>
					</tr>					
					
					<?php
					$db = db_connect();
					$sql = "SELECT * FROM `users` WHERE username='".$_SESSION['username']."'";
					$result = mysqli_query($db, $sql);
					if (mysqli_num_rows($result) > 0) {
						$row = mysqli_fetch_assoc($result);
						
						if ($row['role']=="1") { 
							$sql="SELECT * FROM `items`";
						} else {
							$sql="SELECT * FROM `items`, (SELECT `username` FROM `users` WHERE `groupname`='".$row['groupname']."' AND NOT role='1') AS GetUser WHERE items.i_creator = GetUser.username ORDER by id";
						}
						$result = mysqli_query($db, $sql);
						
						if (mysqli_num_rows($result) > 0) :
							while($row = mysqli_fetch_assoc($result)) { ?>
								<tr>
								<td class="auto-style4"><div id="file<?php echo $row['id']; ?>"></div></td>
								<td class="auto-style4"><?php echo $row['original_name']; ?></td>
								<td class="auto-style4"><?php echo $row['mime_type']; ?></td>
								<td class="auto-style4"><?php echo $row['filesize']; ?></td>
								<td class="auto-style4"><?php echo $row['i_name']; ?></td>
								<td class="auto-style4"><?php echo $row['i_creator']; ?></td>
								<td class="auto-style4"><?php echo $row['i_description']; ?></td>
								<td class="auto-style4"><?php echo $row['i_created']; ?></td>
								<td class="auto-style6">
									<form name="dl<?php echo $row['id']; ?>" action="d_or_d.php" method="POST" enctype="multipart/form-data" style="width: 50%;">
										<input name="id" type="hidden" value="<?php echo $row['id']; ?>">
										<input name="select" type="submit" value="download"><br>or&nbsp;
										<input name="select" type="submit" value="delete">
									</form>
								</td>
								</tr>
						<?php
							}
						endif;
					}
					?>
						<tr>
						<td colspan="9" class="auto-style7">
						  	  		<?php if (mysqli_num_rows($result) > 0) {
						  	  		}else {
						  	  			echo "No record.";
						  	  		}
						  	  		?>	
						</td>
						</tr>

										
	
				</table>
		        

		    </div>
		    
			    
    <?php endif ?>
    
    
    
</div>
		
</body>
</html>