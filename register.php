<?php include('server.php') ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Registration Form</title>
  <link rel="stylesheet" type="text/css" href="style2.css">
</head>
<body>
  <div class="header">
  	<h2>Register</h2>
  </div>
	
  <form method="POST" action="register.php">
  	<?php include('errors.php'); ?>
	  	
  	<div class="input-group">
  	  Please complete the following:
  	  <label>Your Full Name</label>
  	  <input type="text" name="fullname" value="<?php echo $fullname; ?>" maxlength="80">
  	</div>
  	<div class="input-group">
  	  <label>Username</label>
  	  <input type="text" name="username" value="<?php echo $username; ?>" maxlength="16">
  	</div>

  	<div class="input-group">
  	  <label>Email</label>
  	  <input type="email" name="email" value="<?php echo $email; ?>" maxlength="30">
  	</div>
  	<div class="input-group">
  	  <label>Password</label>
  	  <input type="password" name="password_1"  maxlength="30">
  	</div>
  	<div class="input-group">
  	  <label>Confirm password</label>
  	  <input type="password" name="password_2"  maxlength="30">
  	</div>
  	<div class="input-group">
  	  <label>Please select or enter a group name</label>
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
	<!--
		<datalist id="grp">
			<option value="Default">Default</option>
			<option value="CSE">CSE</option>
			<option value="EE">EE</option>
		</datalist>
	-->
		
	</div>
		
  	<div class="input-group">
  	  <button type="submit" class="btn" name="reg_user">Register</button>
  	</div>

  	<p>
  		Already a member? <a href="login.php">Sign In</a>
  	</p>
  </form>
</body>
</html>