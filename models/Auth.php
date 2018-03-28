<?php
	class Auth
	{
		private static $username;
		private static $password;
		public static $error = "Please try again.";
		private static $log_type = 'LOGIN';
		
		public static function attempt($username, $password)
		{	
			require_once "Input.php";
			$username = Input::getString('username');
			$password = Input::getString('password');

			// CHECK IF $username IS NUMBERS, LETTERS, SPACES, or email
			if(preg_match('/^[a-zA-Z0-9 ]+$/', $username) || filter_var($username, FILTER_VALIDATE_EMAIL)){
				self::$username = $username; // assign input as username
			}else { // error out if incorrect input
				self::$error .= "<br> The username entered is not valid.";
				$_SESSION['error_msg'] = self::$error;
				return false;
			}

			// CHECK IF $password IS LONG ENOUGH
			if (strlen($password) > 3) {
				self::$password = $password; // assign input as password
			}else { // error out if incorrect input
				self::$error .= "<br> The password entered does not meet requirements.";
				$_SESSION['error_msg'] = self::$error;
				return false;
			}

			// CHECK IF USER
			require_once "User.php";
			require_once "LoginAttempt.php";
			// find user by email
			if (User::findByEmail(self::$username)) { 

				$user = User::findByEmail(self::$username);

				// error out if there were 6 tries in the last 10 minutes, will need to wait 10 minutes
			    if(LoginAttempt::excessive($user->id)){ 
			    	self::$error = "Your account has been locked. Please try again in 10 minutes.";
			    	$_SESSION['error_msg'] = self::$error;
			    	return false;
			    }

			     // block user at 12 total attempts
			    if(LoginAttempt::userCount($user->id) == 11){
			    	$userBlock = new LoginAttempt();
			    	$userBlock->ip_address = $_SERVER['REMOTE_ADDR'];
			    	$userBlock->last_failed_login = date("Y-m-d H:i:s");
			    	$userBlock->user_id = $user->id;
			    	$userBlock->is_blocked = 1;
			    	$userBlock->save();
			    }else if(LoginAttempt::userCount($user->id) > 11){  // error out if user attempts login after 12 failed attempts
			    	self::$error = "Failed attempts exceeded. Please contact your administrator.";
			    	$_SESSION['error_msg'] = self::$error;
			    	return false;
			    }

			    // if input is correct, assign session variable and log
			    if(self::$username == $user->email && password_verify(self::$password, $user->password)){
			    	require_once "Log.php";

					$_SESSION['UserId'] = $user->id;

					$log = new Log();
					$log->user_id = $user->user_id;
					$log->ip_address = $_SERVER['REMOTE_ADDR'];
					$log->user_agent = $_SERVER['HTTP_USER_AGENT'];
					$log->created_at = date("Y-m-d H:i:s");
					$log->log_type = self::$log_type;
					$log->save();
				    return true;
				}else if (LoginAttempt::userCount($user->id) < 12){ // password is wrong, add login attempt to db
			    	$attempt = new LoginAttempt();
			    	$attempt->ip_address = $_SERVER['REMOTE_ADDR'];
			    	$attempt->last_failed_login = date("Y-m-d H:i:s");
			    	$attempt->user_id = $user->id;
			    	$attempt->save();
					self::$error .= "<br> The password entered does not meet requirements.";
					$_SESSION['error_msg'] = self::$error;
					return false;
				}

			}

			// CHECK IF ADMIN
			require_once "Admin.php";
			if (Admin::findByEmail(self::$username)) { // find admin email by input

				$admin = Admin::findByEmail(self::$username);

				require_once "LoginAttempt.php";
			    if(LoginAttempt::adminCount($admin->id) >= 3){ // block after 3 failed attempts
					self::$error = "Your account has been locked.";
					$_SESSION['error_msg'] = self::$error;
					return false;
				}

				if(self::$username == $admin->email && password_verify(self::$password, $admin->password)){
					$_SESSION['AdminId'] = $admin->id;
					return true;
				}else if (LoginAttempt::adminCount($admin->id) < 3){
					$loginAttempt = new LoginAttempt();
					$loginAttempt->ip_address = $_SERVER['REMOTE_ADDR'];
					$loginAttempt->last_failed_login = date("Y-m-d H:i:s");
					$loginAttempt->admin_id = $admin->id;
					$loginAttempt->save();
				}

			}

			// show default error message
			$_SESSION['error_msg'] = self::$error;
			return false;

		}

		//check for any authenticity
		public static function check()
		{
			if(isset($_SESSION['AdminId'])){
				return true;
			}elseif(isset($_SESSION['UserId'])){
				return true;
			}
			return false;
		}
		
		public static function user()
		{
			return isset($_SESSION['UserId']) ? $_SESSION['UserId'] : null;
		}

		public static function admin()
		{
			return isset($_SESSION['AdminId']) ? $_SESSION['AdminId'] : null;
		}
		
		public static function logout()
		{
			$_SESSION = array();
		    // If it's desired to kill the session, also delete the session cookie.
		    // Note: This will destroy the session, and not just the session data!
		    if (ini_get("session.use_cookies")) {
		        $params = session_get_cookie_params();
		        setcookie(session_name(), '', time() - 42000,
		            $params["path"], $params["domain"],
		            $params["secure"], $params["httponly"]
		        );
		    }
		    // Finally, destroy the session.
		    session_destroy();
		    header('Location: index.php');
		}
	}
?>