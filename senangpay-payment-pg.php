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
<input type="hidden" name="secureHash" value="<?php echo $_GET['secureHash'] ?>"><br>
<input type="hidden" name="mid" value="<?php echo $_GET['mid'] ?>"><br>
<input type="hidden" name="invno" value="<?php echo $_GET['invno'] ?>"><br>
<input type="hidden" name="amt" value="<?php echo $_GET['amt'] ?>"><br>
<input type="hidden" name="desc" value="<?php echo $_GET['desc'] ?>"><br>
<input type="hidden" name="postURL" value="<?php echo $_GET['postURL'] . '&payment-id=' . $_GET['payment-id'] ?>"><br>
<input type="hidden" name="phone" value="<?php echo $_GET['phone'] ?>"><br>
<input type="hidden" name="email" value="<?php echo $_GET['email'] ?>"><br>
<input type="hidden" name="param" value="47630|<?php echo $_GET['invno'] ?>"><br>
<input id="submit_btn" type="submit" value="Submit">
<script type="text/javascript">
					window.onload = function(){
						document.getElementById("submit_btn").style.visibility = "hidden"
						document.getElementById("mpay_payment_form").submit();
					  }
					</script>
</form>
</body>
</html>