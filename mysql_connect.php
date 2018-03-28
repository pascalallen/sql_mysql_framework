<?php
	// MySQL database credentials
	
	define('DB_HOST', '127.0.0.1');
	define('DB_NAME', 'my_db');
	define('DB_USER', 'my_uid');
	define('DB_PASS', 'my_pwd');

	$mysqlDbc = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME, DB_USER, DB_PASS);