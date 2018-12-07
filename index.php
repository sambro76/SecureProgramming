<?php 
session_start(); 
$duration = 600; //set session timeout in seconds
session_regenerate_id(True);

if (!isset($_SESSION['username'])) {
 	$_SESSION['timeout'] = "Please log in";
 	header('location: login.php');
}
if (isset($_GET['logout'])) {
 	session_destroy();
 	unset($_SESSION['username']);
 	header("location: index.php");
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>SP Share Home</title>
	<link rel="stylesheet" type="text/css" href="style2.css">
</head>
<body>

<div class="header">
	<h2>Home Page</h2>
</div>
<div class="content">
    
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
	
    <!-- logged in user information -->
    <?php  if (isset($_SESSION['username'])) : ?>
    	<p>Welcome <strong><?php echo $_SESSION['username']; ?></strong></p>
	    <p>Please <a href="userpage.php">click here</a> to go to 'SP Share' 
		Page.</p>
	    <p>&nbsp;</p>
    	<p> <a href="index.php?logout='1'" style="color: red;">Logout</a> </p>
    <?php endif ?>
</div>
		
</body>
</html>