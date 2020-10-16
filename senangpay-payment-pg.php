<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Senangpay Payment</title>
</head>

<body>
	<p>
		Redirecting to Senangpay ...
	</p>
	<form action="<?php echo $_GET['actionUrl'] ?>" method="POST" id="senangpay_payment_form">
		<input type="hidden" name="detail" value="<?php echo $_GET['detail'] ?>"><br>
		<input type="hidden" name="amount" value="<?php echo $_GET['amount'] ?>"><br>
		<input type="hidden" name="order_id" value="<?php echo $_GET['order_id'] ?>"><br>
		<input type="hidden" name="name" value="<?php echo $_GET['name'] ?>"><br>
		<input type="hidden" name="email" value="<?php echo $_GET['email'] ?>"><br>
		<input type="hidden" name="phone" value="<?php echo $_GET['phone'] ?>"><br>
		<input type="hidden" name="hash" value="<?php echo $_GET['hashed_string'] ?>">
		<!-- unimportant attributes but for testing -->
		<input type="hidden" name="merchant_id" value="<?php echo $_GET['merchant_id'] ?>">
		<input type="hidden" name="secretkey" value="<?php echo $_GET['secretkey'] ?>">
		<input type="hidden" name="postURL" value="<?php echo $_GET['postURL'] ?>">

		<input id="submit_btn" type="submit" value="Submit">

		<!-- Initiate to process payment when at onload -->
		<script type="text/javascript">
			window.onload = function () {
				document.getElementById("submit_btn").style.visibility = "hidden"
				document.getElementById("senangpay_payment_form").submit();
			}
		</script>

	</form>
</body>

</html>