<?php
session_start();
require "../models/Auth.php";
require "../models/Log.php";

if (!Auth::check()){
	Auth::logout();
}

// User is searching
if (isset($_POST['search']) && strlen($_POST['search']) > 1 && strlen($_POST['search']) < 256){
	$post_search = htmlspecialchars(strip_tags($_POST['search']));

    if(!Auth::admin()){
        $log = new Log();
        $log->ip_address = $_SERVER['REMOTE_ADDR'];
        $log->user_id = Auth::user();
        $log->user_agent = $_SERVER['HTTP_USER_AGENT'];
        $log->created_at = date('Y-m-d H:i:s');
        $log->searched_for = $post_search;
        $log->log_type = 'SEARCH';
        $log->save();
    }

    $results = [];
    $instance = new static;
    $instance->id = 1;
    $instance->name = 'Joe';
    $instance->number = 123;
    array_push($results, $instance);
    $instance = new static;
    $instance->id = 2;
    $instance->name = 'Jill';
    $instance->number = 456
    array_push($results, $instance);

    return $results;
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
                <br>
                <h4 class="text-center">My App</h4>
                <br>
                <form method="POST" class="form-inline">
                    <div class="form-group col-md-10">
                        <input style="width: 100%;" type="text" class="form-control" name="search" id="search" placeholder="Search for something..." autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-primary">Search</button>  
                </form> 
                <br>
	        </div>
        	<div class="row">
	            <div class="col-md-6 offset-md-6">
        			<?php if (!empty($results)): ?>
					    <table class="table table-striped table-hover" id="chklist">
							<thead>
								<tr>
									<th align="left">Col1</th>
									<th align="left">Col2</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($results as $result) : ?>
									<tr>
							            <td align="left"><?= $result->name ?></td>
							            <td align="left"><?= $result->number ?></td>
							            <td align="left"><a href="#" data-toggle="modal" data-target="#modal-<?= $result->number ?>">See more</a></td>
							        </tr>
									<div class="modal" id="modal-<?= $result->number ?>" tabindex="1" role="dialog">
									    <div class="modal-dialog modal-lg" role="document">
									        <div class="modal-content">
									            <div class="modal-body">
									                <div class="row">
							                			<h4 class="modal-title" id="myModalLabel"><?= $result->name ?></h4>
									                </div>
									            </div>
									        </div>
									    </div>
									</div>
						        <?php endforeach ?>
							</tbody>
						</table>
			    	<?php endif ?>
	    		</div>
			</div>
		</div>
        <?php include "partials/foot.php"; ?>
    </body>
</html>