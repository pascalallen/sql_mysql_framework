<?php
	session_start();

	require_once "../models/Auth.php";
	require_once "../models/Input.php";
	require_once "../models/Admin.php";

	// Check if admin
	if (!Auth::admin()){
		Auth::logout();
	}


	function checkValues()
	{
		return Input::setAndNotEmpty('email') && Input::setAndNotEmpty('password');
	}

	function insertAdmin()
	{
		$errors = [];

		try{
			$email = Input::has('email') ? Input::getString('email') : null;
		} catch (Exception $e) {
			array_push($errors, $e->getMessage());
		}

		try{
			$password = Input::has('password') ? Input::getString('password') : null;
		} catch (Exception $e) {
			array_push($errors, $e->getMessage());
		}

		if(!empty($errors)){
			return $errors;
		}

		$insert_admin = new Admin();
		$insert_admin->email = strtolower($email);
		$insert_admin->password = password_hash($password, PASSWORD_DEFAULT);
		$insert_admin->save();

		return $errors;
	}

	function pageController(){

		$errors = null;
		$errorExists = null;

		if (!empty($_POST)) {
			$email = htmlspecialchars(strip_tags($_POST['email']));
			if (checkValues() && Admin::findByEmail($email) == false) {
				$errors = insertAdmin();
			} else {
				$errorExists = "Admin already exists!";
			}
		}

		// Count
		$count = Admin::count();
		$limit = 10;
		$max_page = ceil($count / $limit);

		// Sanitizing	
		$page = Input::has('page') ? Input::get('page') : 1; // grabs url value if exists, if not set to 1
		$page = (is_numeric($page)) ? $page : 1; // is value numeric, if not set to 1
		$page = ($page > 0) ? $page : 1; // is value greater than zero, if not set to 1
		$page = ($page <= $max_page) ? $page : $max_page; // is value less than or equal maximum amount of pages, if not set to max page
		
		// Offset
		$offset = $page * $limit - $limit;
		$admins = Admin::limitAndOffset($limit, $offset);

		return array(
			'page' => $page,
			'admins' => $admins,
			'errors' => $errors,
			'errorExists' => $errorExists,
			'max_page' => $max_page
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
	        	<h4 class="text-center">Admins</h4>
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

				<table class="table">
					<thead>
						<tr>
							<th>Email</th>
							<th>Password</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<form method="POST">
							<tr>
								<td><div class="form-group"><input type="email" class="form-control" name="email" placeholder="Email"></div></td>	
								<td><div class="form-group"><input type="password" class="form-control" name="password" placeholder="Password"></div></td>
								<td><button type="submit" class="btn btn-info btn-sm"><i class="fa fa-plus" aria-hidden="true"></i></button></td>
							</tr>
						</form>
						<?php foreach ($admins as $admin) : ?>
							<tr>
								<td><?= Input::escape($admin['email']); ?></td>
								<td></td>
								<td></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php if ($page != 1 && $page != 0) : ?>
					<a href="?page=<?= ($page - 1) ?>"><i class="fa fa-arrow-left" aria-hidden="true"></i></a>
				<?php endif; ?>
				<?php if ($page != $max_page) : ?>
					<a href="?page=<?= ($page + 1) ?>"><i class="fa fa-arrow-right" aria-hidden="true"></i></a>
				<?php endif; ?>
            </div>
        </div>  
    </div>
    <?php include "partials/foot.php"; ?>
</body>
</html>