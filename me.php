<script type="text/javascript">
window.onafterprint = function(event){
	window.location.href = "order.php";
};
</script>
<?php

//view_order.php

if(isset($_GET["pdf"]) && isset($_GET['order_id']))
{
	include('database_connection.php');
	include('function.php');
	if(!isset($_SESSION['type']))
	{
		header('location:login.php');
	}
	$output = '';
	$statement = $connect->prepare("
		SELECT * FROM inventory_order 
		WHERE inventory_order_id = :inventory_order_id
		LIMIT 1
	");
	$statement->execute(
		array(
			':inventory_order_id' =>  $_GET["order_id"]
		)
	);
	$result = $statement->fetchAll();
	foreach($result as $row)
	{
		$state = $connect->prepare("SELECT * FROM user_details WHERE user_id = :userid");
		$state->execute(
			array (
				':userid' => $row['user_id']
			)
		);
		$res = $state->fetchAll();
		foreach($res as $rows)
		{
			$user = $rows['user_name'];
		}
		date_default_timezone_set("Africa/Lagos");
		$output .= '
		<table width="25%" border="1" cellspacing="0">
			<tr>
				<td align="center"><b>Invoice</b></td>
			</tr>
			<tr>
				<td colspan="2">
				<table width="100%" cellpadding="5">
					<tr>
						<td width="65%">
							To,<br /><br/>
							<b>RECEIVER (BILL TO)</b><br />
							<strong>Name :</strong> '.$row["inventory_order_name"].'<br />	
							<strong>Billing Address :</strong> '.$row["inventory_order_address"].'<br />
						</td>						
					</tr>
					<tr><td width="35%">
							<strong>Reverse Charge</strong><br />
							Invoice No. : '.$row["inventory_order_id"].'<br />
							Invoice Date : '.$row["inventory_order_created_date"].'<br />
							Invoice Time : '.$row["inventory_order_created_time"].'<br />
							Staff: '.$user.'
						</td></tr>
				</table>
				<br />
				<table width="50%" border="1" cellspacing="0" style="font-size:10px;">
					<tr>
						<th align="center">Sr No.</th>
						<th align="center">Product</th>
						<th align="center">Quantity</th>
						<th align="center">Price</th>
						<th align="center">Actual Amt.</th>
						<th align="center">Total</th>
					</tr>
					<tr>
					</tr>
		';
		$statement = $connect->prepare("
			SELECT * FROM inventory_order_product 
			WHERE inventory_order_id = :inventory_order_id
		");
		$statement->execute(
			array(
				':inventory_order_id' => $_GET["order_id"]
			)
		);
		$product_result = $statement->fetchAll();
		$count = 0;
		$total = 0;
		$total_actual_amount = 0;
		$total_tax_amount = 0;
		foreach($product_result as $sub_row)
		{
			$count = $count + 1;
			$product_data = fetch_product_details($sub_row['product_id'], $connect);
			$actual_amount = $sub_row["quantity"] * $sub_row["price"];
			$tax_amount = ($actual_amount * $sub_row["tax"])/100;
			$total_product_amount = $actual_amount + $tax_amount;
			$total_actual_amount = $total_actual_amount + $actual_amount;
			$total_tax_amount = $total_tax_amount + $tax_amount;
			$total = $total + $total_product_amount;
			$output .= '
				<tr>
					<td align="center">'.$count.'</td>
					<td align="center">'.$product_data['product_name'].'</td>
					<td align="center">'.$sub_row["quantity"].'</td>
					<td align="center">'.$sub_row["price"].'</td>
					<td align="center">'.number_format($actual_amount, 2).'</td>
					<td align="center">'.number_format($total_product_amount, 2).'</td>
				</tr>
			';
		}
		$output .= '
		<tr>
			<td align="center" colspan="4"><b>Total</b></td>
			<td align="center"><b>'.number_format($total_actual_amount, 2).'</b></td>
			<td align="center"><b>'.number_format($total, 2).'</b></td>
		</tr>
		';
		$output .= '
						</table>
						<p align="right">-------------------------<br />Receiver Signature</p>
						<p align="center">---------------------------------------</p>
						<p align="center" style="font-size:10px;">Software Powered by <strong>Genexo Concept(08169809192)</strong></p>
						<br />
					</td>
				</tr>
			</table>
		';
	}
	echo $output;
}

?>