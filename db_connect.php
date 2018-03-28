<?php
	// SQL database credentials

	$host = "127.0.0.1";

	$connectionInfo = array(
		"UID"					   =>"my_uid",
		"PWD"					   =>"my_pwd",
		"Database"				   =>"my_db",
		"MultipleActiveResultSets" =>'1',
		"ConnectionPooling"		   =>'1',
		"TraceOn" 				   => "0",
		"ReturnDatesAsStrings" 	   => true
	);

	$dbc = sqlsrv_connect($host, $connectionInfo);