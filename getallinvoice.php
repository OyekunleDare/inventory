<?php
//profile.php

include('database_connection.php');

if(!isset($_SESSION['type']))
{
	header("location:login.php");
}

$query = "
SELECT * FROM user_details 
WHERE user_id = '".$_SESSION["user_id"]."'
";
$statement = $connect->prepare($query);
$statement->execute();
$result = $statement->fetchAll();
$name = '';
$email = '';
$user_id = '';
foreach($result as $row)
{
	$name = $row['user_name'];
	$email = $row['user_email'];
	$key = $row['user_type'];
	$_SESSION['type'] = $key;
}

include('header.php');

?>

<script>
	$(document).ready(function(){
		$('#inventory_order_date').datepicker({
			format: "yyyy-mm-dd",
			autoclose: true
		});
	});
	</script>
		<div class="panel panel-default">
			<div class="panel-heading">Get All Invoice</div>
			<div class="panel-body">
				<form method="post" id="edit_profile_form">
					<div class="col-lg-3" style="border:2px solid #317eac;"><p align="center" style="font-weight:bold; font-family:'Bree Serif'; font-size:18px; color:#317eac;">Search by day</p><p align="center"><input type="text" name="inventory_order_date" id="inventory_order_date" class="form-control" required /></p></div>
                    <div class="col-lg-3" style="border:2px solid #317eac;"><p align="center" style="font-weight:bold; font-family:'Bree Serif'; font-size:18px; color:#317eac;">Search by week</p><p align="center"><input type="week" /></p></div>
                    <div class="col-lg-3" style="border:2px solid #317eac;"><p align="center" style="font-weight:bold; font-family:'Bree Serif'; font-size:18px; color:#317eac;">Search by month</p><p align="center"><input type="month" /></p></div>
                    <div class="col-lg-3" style="border:2px solid #317eac;"><p align="center" style="font-weight:bold; font-family:'Bree Serif'; font-size:18px; color:#317eac;">Search by year</p><p align="center"><input type="year" /></p></div>
				</form>
			</div>
		</div>


			
