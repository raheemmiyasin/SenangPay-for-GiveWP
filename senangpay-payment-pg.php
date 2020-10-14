<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Mpay Payment</title>
</head>

<body>
	<p>
		Redirecting to MPAY ...
	</p>
	<form action="<?php echo $_GET['actionUrl'] ?>" method="POST" id="mpay_payment_form">
		<input type="text" name="detail" value="<?php echo $_GET['detail'] ?>"><br>
		<input type="text" name="amount" value="<?php echo $_GET['amount'] ?>"><br>
		<input type="text" name="order_id" value="<?php echo $_GET['order_id'] ?>"><br>
		<input type="text" name="name" value="<?php echo $_GET['name'] ?>"><br>
		<input type="text" name="email" value="<?php echo $_GET['email'] ?>"><br>
		<input type="text" name="phone" value="<?php echo $_GET['phone'] ?>"><br>
		<input type="text" name="hash" value="<?php echo $_GET['hashed_string'] ?>">
		<!-- unimportant attributes but for testing -->
		<input type="text" name="merchant_id" value="<?php echo $_GET['merchant_id'] ?>">
		<input type="text" name="secretkey" value="<?php echo $_GET['secretkey'] ?>">

		<input id="submit_btn" type="submit" value="Submit">

		<!-- Initiate to process payment when at onload -->
		<!-- <script type="text/javascript">
			window.onload = function () {
				document.getElementById("submit_btn").style.visibility = "hidden"
				document.getElementById("mpay_payment_form").submit();
			}
		</script> -->

	</form>
</body>

</html>