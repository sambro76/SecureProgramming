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
  <form method="POST" action="login.php">
  	<!--show message -->
  	<?php 
  	  session_start();	
  	  if (isset($_SESSION['success'])) : ?>
      <div class="error success" >
      	<h3>
          <?php 
          	echo $_SESSION['success']; 
          	unset($_SESSION['success']);
          ?>
      	</h3>
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