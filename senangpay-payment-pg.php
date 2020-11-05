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

	<?php if (isset($_GET['recurring_id'])) { ?>
        <form action="<?php echo $_GET['actionUrl'] ?>" method="POST" id="senangpay_payment_form">
            <input type="hidden" name="recurring_id" value="<?php echo $_GET['recurring_id']; ?>"><br>
            <input type="hidden" name="order_id" value="<?php echo $_GET['order_id']; ?>"><br>
            <input type="hidden" name="name" value="<?php echo $_GET['name']; ?>"><br>
            <input type="hidden" name="phone" value="<?php echo $_GET['phone']; ?>"><br>
            <input type="hidden" name="amount" value="<?php echo $_GET['amount']; ?>"><br>
            <input type="hidden" name="hash" value="<?php echo $_GET['hashed_string'] ?>"><br>

		
	<?php } else { ?>


			<form action="<?php echo $_GET['actionUrl'] ?>" method="POST" id="senangpay_payment_form">
		<input type="hidden" name="detail" value="<?php echo $_GET['detail'] ?>"><br>
		<input type="hidden" name="amount" value="<?php echo $_GET['amount'] ?>"><br>
		<input type="hidden" name="order_id" value="<?php echo $_GET['order_id'] ?>"><br>
		<input type="hidden" name="name" value="<?php echo $_GET['name'] ?>"><br>
		<input type="hidden" name="email" value="<?php echo $_GET['email'] ?>"><br>
		<input type="hidden" name="phone" value="<?php echo $_GET['phone'] ?>"><br>
		<input type="hidden" name="hash" value="<?php echo $_GET['hashed_string'] ?>"><br>

	<?php } ?>
			<!-- unimportant attributes but for testing -->

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