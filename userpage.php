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
if (isset($_SESSION['group'])) {
	require_once "db_connect.php";
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
	.auto-style2 {
		text-align: left;
	}
	.auto-style3 {
		text-align: right;
	}
	</style>
		<script>
			function load_img(element, id, style = "") {
				document.getElementById(element).innerHTML='<object type="text/html" style="'+ style +'" data="image.php?id='+ id +'"></object>';
			}
			document.addEventListener( "DOMContentLoaded", function(){
				<?php 
				if (isset($_SESSION['showImage'])) {
					$total = $_SESSION['showImage'];
					for($i=1; $i<=$total; $i++) {
						echo "load_img('file".$i."', ". $_GET['file'.$i].", 'width: 100px; border: 0px solid black;');"; 
					}
				}
				?>
			});
		</script>
</head>
<body>

<div class="header" style="width: 75%">
	<h2>SP Share</h2>
</div>
<div class="content" style="width: 75%">
    
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
	
    <!-- logged in user information -->
    <?php  if (isset($_SESSION['username'])) : ?>
		    <p>Welcome <strong><?php echo ucwords(strtolower($_SESSION['username'])); if(isset($_SESSION['group'])) {echo " [".$_SESSION['group']."]";} ?></strong></p>
			<p>Please follow the option below for user interaction. </p>
			<p>Here user can view, upload, download, and delete file(s) in the group 
			to which user belongs. </p>
			<p>&nbsp;</p>
			<p><a href="viewItems.php">View Items In Group</a>&nbsp; 
				|&nbsp; <a href="index.php?logout='1'" style="color: red;">Logout</a> </p>
			<p>&nbsp;</p>
			<p><span class="auto-style1"><strong>Upload</strong></span>: </p>
			<div>
			    <form name="upload" action="upload.php" method="post" enctype="multipart/form-data" style="width: 100%">
		        Select 
					file(s) to upload: <input type="file" name="image[]" multiple>
		        <input type="submit" name="upload" value="  Upload  ">
		        <br><br>
		        
		        <table border='1' cellpadding="0" cellspacing="0">
					<tr>
						<th class="auto-style2">Id</th>
						<th class="auto-style2">Filename / Image</th>
						<th class="auto-style2">Item Name</th>
						<th class="auto-style2">Description</th>
					</tr>
					
						<?php 
							   if (isset($_SESSION['showImage'])) {
									$total = $_SESSION['showImage'];
									for($i=1; $i<=$total; $i++) {
										echo "<input type='hidden' name='id".$i."' value='".$_GET['file'.$i]."' />";
										echo "<tr><td>".$_GET['file'.$i]."</td>";
								   		echo "<td><div id='file".$i."'></div></td>";
										echo "<td><input type='text' name='iName".$_GET['file'.$i]."' value='' maxlength='68'></td>";
										echo "<td><textarea cols='40' name='iDesc".$_GET['file'.$i]."'  rows='4'  maxlength='500'></textarea></td></tr>";
								   	}
							   }
						?>
						<tr>
						<td colspan="4" class="auto-style3">
						  	  		<?php if (!isset($_SESSION['showImage'])) {
						  	  			echo " ";
						  	  			echo "<button type='submit' name='Update' value='' disabled>Update Now</button>";
						  	  		}else {
						  	  			echo "<button type='submit' name='Update' value=''>Update Now</button>";
						  	  		}
						  	  		?>	
						</td>
						</tr>
				</table>
		        
		        </form>
		    </div>
		    
			    
    <?php endif ?>
  
    
</div>
		
</body>
</html>