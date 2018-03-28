<?php
	session_start();
	require_once "../models/Auth.php";
	
	// Check if admin
	if (!Auth::admin()){
		Auth::logout();
	}

	function pageController(){
		require_once "../models/Log.php";
		require_once "../models/Input.php";
		// Count
		$count = Log::count();
		$limit = 10;
		$max_page = ceil($count / $limit);

		// Sanitizing	
		$page = Input::has('page') ? Input::get('page') : 1; // grabs url value if exists, if not set to 1
		$page = (is_numeric($page)) ? $page : 1; // is value numeric, if not set to 1
		$page = ($page > 0) ? $page : 1; // is value greater than zero, if not set to 1
		$page = ($page <= $max_page) ? $page : $max_page; // is value less than or equal maximum amount of pages, if not set to max page
		
		// Offset
		$offset = $page * $limit - $limit;
		$logs = Log::limitAndOffset($limit, $offset);

		return array(
			'page' => $page,
			'logs' => $logs,
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
	            	<h4 class="text-center">Logs</h4>
	            	<br>
            	</div>
            	<br>
            	<br>
            	<br>
            	<br>
            	<?php if ($page != 1 && $page != 0) : ?>
					<a href="?page=<?= ($page - 1) ?>"><i class="fa fa-arrow-circle-o-left fa-3x" aria-hidden="true"></i></a>
				<?php endif; ?>
				<?php if ($page != $max_page) : ?>
					<a href="?page=<?= ($page + 1) ?>" style="float:right;"><i class="fa fa-arrow-circle-o-right fa-3x" aria-hidden="true"></i></a>
				<?php endif; ?>
				<table class="table">
					<thead>
						<tr>
							<th>User ID</th>
							<th>Created At</th>
							<th>IP Address</th>
							<th>User Agent</th>
							<th>Searched For</th>
							<th>View</th>
							<th>Log Type</th>
						</tr>
					</thead>
	            	<?php foreach ($logs as $log) : ?>
						<tbody>
							<tr>
								<td><?= Input::escape($log['user_id']); ?></td>
								<td><?= Input::escape($log['created_at']); ?></td>	
								<td><?= Input::escape($log['ip_address']); ?></td>
								<td><?= Input::escape($log['user_agent']); ?></td>
								<td><?= Input::escape($log['searched_for']); ?></td>
								<td><?= Input::escape($log['view']); ?></td>
								<td><?= Input::escape($log['log_type']); ?></td>
							</tr>
						</tbody>
					<?php endforeach; ?>
				</table>
				<?php if ($page != 1 && $page != 0) : ?>
					<a href="?page=<?= ($page - 1) ?>"><i class="fa fa-arrow-circle-o-left fa-3x" aria-hidden="true"></i></a>
				<?php endif; ?>
				<?php if ($page != $max_page) : ?>
					<a href="?page=<?= ($page + 1) ?>" style="float:right;"><i class="fa fa-arrow-circle-o-right fa-3x" aria-hidden="true"></i></a>
				<?php endif; ?>
            </div>
        </div>  
    </div>
    
	<?php include "partials/foot.php"; ?>
</body>
</html>