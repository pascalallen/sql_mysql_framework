<?php
	session_start();
	require_once "../models/Auth.php";
	Auth::logout();