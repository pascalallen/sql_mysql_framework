<?php
	require_once "Model.php";
	class Admin extends Model
	{
		protected static $table = 'admins';
		protected static $database = 'MySQL';

		public static function findByEmail($email)
		{
			self::dbConnect();
	        $table = static::$table;
	        $query = "SELECT * FROM $table WHERE email = :email";
	        $stmt = self::$mysqlDbc->prepare($query);
	        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
	        $stmt->execute();
	        $result = $stmt->fetch(PDO::FETCH_ASSOC);
	        // Set the attributes on the calling object based on the result variable's contents
	        $instance = null;
	        if ($result)
	        {
	            $instance = new static;
	            $instance->id = $result['id'];
	            $instance->email = $result['email'];
	            $instance->password = $result['password'];
	        }
	        return $instance;
		}

	    public static function limitAndOffset($limit, $offset) // MySQL tables only (for now)
	    {
	        self::dbConnect();
	        $table = static::$table;
			$query = "SELECT * FROM $table LIMIT :limit OFFSET :offset";
			$stmt = self::$mysqlDbc->prepare($query);
			$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
			$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
	    }
	}
?>