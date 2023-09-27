<?php

//view_order.php

if(isset($_GET["pdf"]) && isset($_GET['order_id']))
{
	require_once 'pdf.php';
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
			':inventory_order_id'       =>  $_GET["order_id"]
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
		$output .= '
		<table width="100%" border="1" cellpadding="5" cellspacing="0">
			<tr>
				<td colspan="2" align="center" style="font-size:18px"><b>Invoice</b></td>
			</tr>
			<tr>
				<td colspan="2">
				<table width="100%" cellpadding="5">
					<tr>
						<td width="65%">
							To,<br />
							<b>RECEIVER (BILL TO)</b><br />
							Name : '.$row["inventory_order_name"].'<br />	
							Billing Address : '.ucfirst($row["inventory_order_address"]).'<br/>
							Payment Method : '.$row["payment_status"].'
						</td>
						<td width="35%">
							Reverse Charge<br />
							Invoice No. : '.$row["inventory_order_id"].'<br />
							Invoice Date : '.$row["inventory_order_created_date"].'<br />
							Invoice Time : '.$row["inventory_order_created_time"].'<br />
							Order Performed By: '.$user.'
						</td>
					</tr>
				</table>
				<br />
				<table width="100%" border="1" cellpadding="5" cellspacing="0">
					<tr>
						<th rowspan="2">Sr No.</th>
						<th rowspan="2">Product</th>
						<th rowspan="2">Quantity</th>
						<th rowspan="2">Price</th>
						<th rowspan="2">Actual Amt.</th>
						<th colspan="2">Tax (%)</th>
						<th rowspan="2">Total</th>
					</tr>
					<tr>
						<th>Rate</th>
						<th>Amt.</th>
					</tr>
		';
		$statement = $connect->prepare("
			SELECT * FROM inventory_order_product 
			WHERE inventory_order_id = :inventory_order_id
		");
		$statement->execute(
			array(
				':inventory_order_id'       =>  $_GET["order_id"]
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
					<td>'.$count.'</td>
					<td>'.$product_data['product_name'].'</td>
					<td>'.$sub_row["quantity"].'</td>
					<td aling="right">'.$sub_row["price"].'</td>
					<td align="right">'.number_format($actual_amount, 2).'</td>
					<td>'.$sub_row["tax"].'%</td>
					<td align="right">'.number_format($tax_amount, 2).'</td>
					<td align="right">'.number_format($total_product_amount, 2).'</td>
				</tr>
			';
		}
		$output .= '
		<tr>
			<td align="right" colspan="4"><b>Total</b></td>
			<td align="right"><b>'.number_format($total_actual_amount, 2).'</b></td>
			<td>&nbsp;</td>
			<td align="right"><b>'.number_format($total_tax_amount, 2).'</b></td>
			<td align="right"><b>'.number_format($total, 2).'</b></td>
		</tr>
		';
		$output .= '
						</table>
						<br />
						<br />
						<br />
						<br />
						<br />
						<br />
						<p align="right">----------------------------------------<br />Receiver Signature</p>
						<br />
						<br />
						<br />
						<p align="justify">---------------------------------------------------------------------------------------------</p>
						<p align="center">----------------------------------------<br />Software Powered by <strong>Genexo Concept (genexoconcept@gmail.com)</strong></p>
					</td>
				</tr>
			</table>
		';
	}
	$pdf = new Pdf();
	$file_name = 'Order-'.$row["inventory_order_name"].'-'.$row["inventory_order_date"].'.pdf';
	$pdf->loadHtml($output);
	$pdf->render();
	ob_end_clean();
	$pdf->stream($file_name, array("Attachment" => 0));
	end;
}

?>