<?php
	session_start();
	require_once "../models/Auth.php";

	if (!empty($_POST)) {
		if (Auth::attempt($_POST['username'], $_POST['password'])) {
			header('Location: app.php');
			exit;
		}
	}
?>
<!DOCTYPE html>
<html>
	<head>
	    <?php include "partials/head.php"; ?>
	</head>
	<body>
	    <div class="container" id="login-container">
	        <div class="row">
				<div class="text-center">
	                <a href="">
	            	</a>
	                <br>
	                <br>
	            </div>
			</div>
	        <br>
	        <div class="well col-md-4 col-md-offset-4">
	            <h4 class="text-center">Fintouch
	            	<br>
	            	<small>Lorem ipsum dolor sit amet, consectetur </small>
	            </h4>
				
				<?php if (isset($_SESSION['error_msg'])) : ?>
					<div class="alert alert-danger alert-dismissible" role="alert">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true" onclick="this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode); return false;">&times;</span></button>
						<?php echo $_SESSION['error_msg']; ?>
					</div>	
					<?php unset($_SESSION['error_msg']); ?>
				<?php endif; ?>
				
		        <form method="POST" class="form-signin" role="form">
					<input type="text" name="username" class="form-control" placeholder="Username" required autofocus autocomplete="off">
					<input type="password" name="password" class="form-control" placeholder="Password" required autocomplete="off">
					<button class="btn btn-lg btn-default btn-block" name="submit" type="submit">Login</button>
				</form>
		        <p>Lorem ipsum dolor sit amet, consecte <a href="">https://lorem.ipsum.com</a></p>
	        </div>
	    </div>
		<?php include "partials/foot.php"; ?>
  	</body>
</html>
