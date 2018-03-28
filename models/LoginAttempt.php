<?php
	require_once "Model.php";
	class LoginAttempt extends Model
	{
		protected static $table = 'login_attempts';
		protected static $database = 'MySQL';
	
		public static function excessive($user_id)
		{
			self::dbConnect();
	        $table = static::$table;
	        $query = "SELECT * FROM $table WHERE user_id = :user_id AND last_failed_login > DATE_SUB(NOW(), INTERVAL 10 MINUTE)";
	        $stmt = self::$mysqlDbc->prepare($query);
	        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
	        $stmt->execute();
	        if ($stmt->rowCount() >= 6) {
	        	return true;
	        }
	        return false;
		}

		public static function userCount($user_id)
		{
			self::dbConnect();
	        $table = static::$table;
	        $query = "SELECT * FROM login_attempts WHERE user_id = :user_id";
	        $stmt = self::$mysqlDbc->prepare($query);
	        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
	        $stmt->execute();
	        return $stmt->rowCount();
		}

		public static function adminCount($admin_id)
		{
			self::dbConnect();
	        $table = static::$table;
	        $query = "SELECT * FROM login_attempts WHERE admin_id = :admin_id";
	        $stmt = self::$mysqlDbc->prepare($query);
	        $stmt->bindValue(':admin_id', $admin_id, PDO::PARAM_INT);
	        $stmt->execute();
	        return $stmt->rowCount();
		}

		public static function clearAttempts($user_id)
		{
	        // Get connection to the database
	        self::dbConnect();
	        $table = static::$table;
	        $query = "DELETE FROM $table WHERE user_id = :user_id";
	        $stmt = self::$mysqlDbc->prepare($query);
	        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
	        $stmt->execute();
		}
	}