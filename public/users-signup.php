<?php
	require_once "../models/User.php";
	require_once "../models/Input.php";
	require_once "../models/Log.php";
	session_start();

	$unique_id = htmlspecialchars(strip_tags($_GET['ref']));

	$user = User::findByUniqId($unique_id);

	if(isset($user->password)){
		header('Location: logout.php');
		exit;
	}

	if(!isset($_GET['ref'])){
		header('Location: logout.php');
		exit;
	}

	function checkValues()
	{
		return Input::setAndNotEmpty('password') && Input::setAndNotEmpty('confirm_password');
	}

	function updateUser()
	{
		$unique_id = htmlspecialchars(strip_tags($_GET['ref']));
		$user = User::findByUniqId($unique_id);

		$errors = [];

		try{
			$email = Input::has('email') ? Input::getString('email') : null;
		} catch (Exception $e) {
			array_push($errors, $e->getMessage());
		}

		try{
			$password = Input::has('password') ? Input::getString('password') : null;
			$confirm_password = Input::has('confirm_password') ? Input::getString('confirm_password') : null;

			if($password != $confirm_password){
				$message = "Passwords do not match! Please try again!";
				echo "<script type='text/javascript'>alert('$message');</script>";
				return false;
			}else if ($password == $confirm_password){
				$hashed_password = password_hash($password, PASSWORD_DEFAULT);
			}
		} catch (Exception $e) {
			array_push($errors, $e->getMessage());
		}

		try{
	    	$user = User::find($user->id);
			$user->password = $hashed_password;
			$user->save();
		} catch (PDOException $e){
			array_push($errors, $e->getMessage());
		}

		if(!empty($errors)){
			return $errors;
		}

		return $errors;
	}

	function pageController()
	{
		$errors = null;

		$unique_id = htmlspecialchars(strip_tags($_GET['ref']));
		$user = User::findByUniqId($unique_id);

		$_SESSION['UserId'] = $user->id;

		if (!empty($_POST)) {
			if (checkValues()) {
				$errors = updateUser();
				if(empty($errors)){

			        $log = new Log();
			        $log->user_id = $user->id;
			        $log->ip_address = $_SERVER['REMOTE_ADDR'];
			        $log->user_agent = $_SERVER['HTTP_USER_AGENT'];
			        $log->created_at = date("Y-m-d H:i:s");
			        $log->log_type = 'USER';
			        $log->save();

					header('Location: app.php');
					exit;
				}else{
					echo "Did not insert into logs. Something went wrong :(";
				}		
			} else {
				$message = "Invalid format. Please try again.";
				$javascript = "<script type='text/javascript'>alert('$message');</script>";
				echo $javascript;
			}
		}

		return array(
			'errors' => $errors,
			'email'  => $user->email
		);
	}
	extract(pageController());
?>
<!DOCTYPE html>
<html>
<head>
	<?php include "partials/head.php"; ?>
</head>
<body>
	<div class="container">
        <div class="row">
			<div class="text-center">
                <a href=""></a>
                <br>
                <br>
            </div>
		</div>
        <br />
        <div class="well col-md-4 col-md-offset-4">
            <h4 class="text-center">User Registration<br/>
            <small>Sign up below to become a user.</small>
            </h4>
			
			<?php if(!empty($errors)) : ?>
				<?php foreach($errors as $error) : ?>
					<h5> <?= $error; ?> </h5>
				<?php endforeach ?>
			<?php endif ?>
			
	        <form method="POST" class="form-signin" role="form">
				<input type="email" name="email" class="form-control" value="<?= $email; ?>" required disabled>
				<input type="password" name="password" class="form-control" placeholder="Password" required autofocus>
				<input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
				<button class="btn btn-lg btn-default btn-block" name="submit" type="submit">Login</button>
			</form>
    	</div>
    </div>

    <?php include "partials/foot.php"; ?>
</body>
</html>
