<?php
//login.php

include('database_connection.php');

if(isset($_SESSION['type']))
{
	header("location:index.php");
}

$message = '';

if(isset($_POST["login"]))
{
	$query = "
	SELECT * FROM user_details 
		WHERE user_email = :user_email
	";
	$statement = $connect->prepare($query);
	$statement->execute(
		array(
				'user_email'	=>	$_POST["user_email"]
			)
	);
	$count = $statement->rowCount();
	if($count > 0)
	{
		$result = $statement->fetchAll();
		foreach($result as $row)
		{
			if($row['user_status'] == 'Active')
			{
				if(password_verify($_POST["user_password"], $row["user_password"]))
				{
				
					$_SESSION['type'] = $row['user_type'];
					$_SESSION['user_id'] = $row['user_id'];
					$_SESSION['user_name'] = $row['user_name'];
					header("location:index.php");
				}
				else
				{
					$message = "<label>You have entered wrong password</label>";
				}
			}
			else
			{
				$message = "<label>Your account is disabled, contact the administrator of your company</label>";
			}
		}
	}
	else
	{
		$message = "<label>You have entered wrong email address</label>";
	}
}

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Genexo Inventory Management System</title>		
		<script src="js/jquery-1.10.2.min.js"></script>
		<link rel="stylesheet" href="css/bootstrap.min.css" />
		<script src="js/bootstrap.min.js"></script>
	</head>
	<body style="background: url('css/img/bg.jpg'); background-color: #4B9CD3; background-position: center; background-blend-mode: multiply;">
		<br /><br /><br /><br /><br /><br /><br />
		<div class="container">
		<div class="col-lg-3"></div>
		<div class="col-lg-6">
			<h2 align="center" style="font-family:'Bree Serif'; color:white;">Genexo Inventory Management System</h2>
			<br />
			<div class="panel panel-default">
				<div class="panel-heading">Login</div>
                <div class="panel-body">
					<form method="post" action="login.php">
						<p align="center" style="color:#F00;"><?php echo $message; ?></p>
						<div class="form-group">
							<label>User Email</label>
							<input type="text" name="user_email" class="form-control" required />
						</div>
						<div class="form-group">
							<label>Password</label>
							<input type="password" name="user_password" class="form-control" required />
						</div>
						<div class="form-group">
							<input type="submit" name="login" value="Login" class="btn btn-info" />
						</div>
					</form>
				</div>
				</div>
				<div class="col-lg-3"></div>
			</div>
		</div>
	</body>
</html>