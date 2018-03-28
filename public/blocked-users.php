<?php
	session_start();

	require_once "../models/Auth.php";
	require_once "../models/User.php";
	require_once "../models/Input.php";
	require_once "../models/LoginAttempt.php";

	// Check if admin
	if (!Auth::admin()){
		Auth::logout();
	}

	$users = User::blocked();

    $userAll = User::all();

	if(!empty($_POST)){
		if (Input::has('id')) {
			LoginAttempt::clearAttempts(Input::get('id'));
		}
		header("Refresh:0");
	}
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
	            	<h4 class="text-center">Blocked Users</h4>
	            	<br>
            	</div>
				<table class="table">
					<thead>
						<tr>
							<th>First Name</th>
							<th>Last Name</th>
							<th>Email</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($users as $u) : ?>
							<tr>
								<td><?= Input::escape($u['first_name']); ?></td>
								<td><?= Input::escape($u['last_name']); ?></td>
								<td><?= Input::escape($u['email']); ?></td>
								<td>
									<form role="form" method="POST">
										<button type="submit" class="btn btn-danger btn-sm" value="<?= $u['id']; ?>" name="id" onclick="return confirm('Are you sure you want to unblock this user?');"><i class="fa fa-minus" aria-hidden="true"></i></button>
									</form>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<div class="col-md-6 col-md-offset-3">
	            	<br>
	            	<h4 class="text-center">Users Quickview</h4>
	            	<br>
					<form>
						<select name="users" onchange="showUser(this.value)"  class="form-control">
							<option value="">Select a user:</option>
							<?php foreach($userAll as $user) : ?>
								<option value="<?= $user['id']; ?>"><?= $user['first_name'] . " " . $user['last_name']; ?></option>
							<?php endforeach; ?>
						</select>
					</form>
            	</div>
				<div class="form-group col-md-12">
					<br>
					<div id="txtHint"></div>
				</div>
            </div>
        </div>  
    </div>
    <?php include "partials/foot.php"; ?>
</body>
</html>