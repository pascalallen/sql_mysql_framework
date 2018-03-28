<?php
	session_start();
	require_once "../models/Auth.php";
	require_once "../models/Input.php";
	require_once "../models/User.php";

	if(Auth::user()){
		header('Location: logout.php');
		exit;
	}

	function checkValues()
	{
		return Input::setAndNotEmpty('first_name') && Input::setAndNotEmpty('last_name') && Input::setAndNotEmpty('email');
	}

	function insertUser()
	{
		$errors = [];

		try{
			$first_name = Input::has('first_name') ? Input::getString('first_name') : null;
		} catch (Exception $e) {
			array_push($errors, $e->getMessage());
		}

		try{
			$last_name = Input::has('last_name') ? Input::getString('last_name') : null;
		} catch (Exception $e) {
			array_push($errors, $e->getMessage());
		}

		try{
			$email = Input::has('email') ? Input::getString('email') : null;
		} catch (Exception $e) {
			array_push($errors, $e->getMessage());
		}

		try{
			$super_user = Input::has('super_user') ? Input::getString('super_user') : null;
		} catch (Exception $e) {
			array_push($errors, $e->getMessage());
		}

		if(!empty($errors)){
			return $errors;
		}

		$subject = "You've been added as a user!";
		$uniqueId = uniqid();

		$msg = "<html>
					<head>
						<meta charset='utf-8'>
					    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
					    <meta name='viewport' content='width=device-width, initial-scale=1'>
					    <meta name='description' content=''>
					    <meta name='author' content=''>
				  		<title>My Company, LLC.</title>
				  		<!-- Latest compiled and minified Bootstrap CSS -->
						<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' integrity='sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u' crossorigin='anonymous'>
					</head>
					<body>
						<div class='container'>
	        				<div class='row'>
	        					<div class='col-md-4 col-md-offset-4'>
									<h4 class='text-center'>You have been added as a user!<br>
										<small>Please follow this <a href='https://myapp.com/users-signup.php?ref=" . $uniqueId . "'>link</a> to create your password.</small>
									</h4>
									<br>
									<br>
								</div>
							</div>
						</div>
						<!-- Latest compiled and minified Bootstrap JavaScript -->
						<script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js' integrity='sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa' crossorigin='anonymous'></script>
					</body>
				</html>";

		// use wordwrap() if lines are longer than 70 characters
		$msg = wordwrap($msg,70);

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= "From: noreply@myapp.com";

		mail($email, $subject, $msg, $headers);

	    $user = new User();
	    $user->first_name = $first_name;
	    $user->last_name = $last_name;
	    $user->email = $email;
	    $user->unique_id = $uniqueId;
	    $user->super_user = $super_user;
	    $user->save();

		return $errors;
	}

	function deleteUser()
	{
		if (Input::has('id')) {
			User::delete(Input::get('id'));
		}

	}

	function pageController(){

		$errors = null;
		$errorExists = null;
		$clients = Client::allActive();

		if (!empty($_POST)) {
			$email = htmlspecialchars(strip_tags($_POST['email']));
			if (checkValues() && User::findByEmail($email) == false) {
				$errors = insertUser();
			} else if(User::findByEmail($email)){
				$errorExists = "User already exists!";
			}
		}

		deleteUser();
		
		$users = User::all();

		return array(
			'users' => $users,
			'errors' => $errors,
			'errorExists' => $errorExists
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
	<?php include "partials/navbar.php"; ?>
    <div class="col-sm-push-1 col-md-11 column">
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-6 col-md-offset-3">
                    <br>
                    <h4 class="text-center">Users</h4>
                    <br>
                </div>

	            <?php if(!empty($errors)) : ?>
					<?php foreach($errors as $error) : ?>
						<div class="alert alert-danger alert-dismissible col-md-4 col-md-offset-4" role="alert">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true" onclick="this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode); return false;">&times;</span></button>
						<?= $error ?>
					</div>
					<?php endforeach ?>
				<?php endif ?>

	            <?php if(!empty($errorExists)) : ?>
					<div class="alert alert-danger alert-dismissible col-md-4 col-md-offset-4" role="alert">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true" onclick="this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode); return false;">&times;</span></button>
						<?= $errorExists ?>
					</div>
				<?php endif ?>

				<!-- add user -->
				<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
					      	<div class="modal-header">
				        		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					          		<span aria-hidden="true">&times;</span>
					        	</button>
				        		<h4 class="modal-title" id="addUserModalLabel">Add User</h4>
					      	</div>
							<div class="modal-body">
								<form method="POST">
									<div class="form-group"><input type="text" class="form-control" name="first_name" placeholder="First Name"></div>
									<div class="form-group"><input type="text" class="form-control" name="last_name" placeholder="Last Name"></div>
									<div class="form-group"><input type="email" class="form-control" name="email" placeholder="Email"></div>
									<div class="form-check form-group">
										<input type="checkbox" class="form-check-input" value="1" name="super_user"> Super User 
										<button type="submit" class="btn btn-info btn-sm">Add</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>

				<table class="table">
					<thead>
						<tr>
							<th>First Name</th>
							<th>Last Name</th>
							<th>Email</th>
							<th>Super User</th>
							<th><button type="button" class="btn btn-success btn-sm" value="" data-toggle="modal" data-target="#addUserModal"><i class="fa fa-plus" aria-hidden="true"></i></button></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($users as $user) : ?>
							<tr>
								<td><?= Input::escape($user['first_name']); ?></td>
								<td><?= Input::escape($user['last_name']); ?></td>
								<td><?= Input::escape($user['email']); ?></td>
								<td><?= Input::escape($user['super_user']); ?></td>
								<td>
									<form role="form" method="POST">
										<button type="submit" class="btn btn-danger btn-sm" value="<?= $user['id'] ?>" name="id" onclick="return confirm('Are you sure you want to remove this user from Far West Capital\'s Credit Portal?');"><i class="fa fa-minus" aria-hidden="true"></i></button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
            </div>
        </div>  
    </div>
    <?php include "partials/foot.php"; ?>
</body>
</html>