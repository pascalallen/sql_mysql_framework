<?php
	session_start();
	require_once "../models/User.php";

	$id = intval($_GET['q']);

    $u = User::find($id);

?>
<table class="table">
	<tr>
		<th>ID</th>
		<th>First Name</th>
		<th>Last Name</th>
		<th>Email</th>
		<th>Super User</th>
	</tr>
	<tr>
		<td><?= $u->id; ?></td>
		<td><?= $u->first_name; ?></td>
		<td><?= $u->last_name; ?></td>
		<td><?= $u->email; ?></td>
		<td><?= $u->super_user; ?></td>
    </tr>
</table>