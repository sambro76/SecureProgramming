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

include('server.php');
session_regenerate_id(true);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>SP Share Login</title>
  <link rel="stylesheet" type="text/css" href="style2.css">
</head>
<body>
  <div class="header">
  	<h2>Login</h2>
  </div>
	 
  <form method="POST" action="login.php">
  	<?php 
		include('errors.php');
		
		if (isset($_SESSION['timeout'])) : ?>
		<div class="error success" >
      	<h4>
          <?php 
          	echo $_SESSION['timeout']; 
          	unset($_SESSION['timeout']);
	        session_unset();
	        session_destroy();
          ?>
      	</h4>
		</div>
  	<?php endif ?>
	
    <div class="input-group">
  		<label>Username</label>
  		<input type="text" name="username" >
  	</div>
  	<div class="input-group">
  		<label>Password</label>
  		<input type="password" name="password">
  	</div>
  	<div class="input-group">
  		<button type="submit" class="btn" name="login_user">Login</button>
  	</div>
  	<p>
  		Not yet a member? <a href="register.php">Sign up</a>
  	</p>
  </form>
</body>
</html>