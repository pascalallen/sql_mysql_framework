<?php 
	require_once "Model.php";
	class User extends Model
	{
		protected static $table = 'users';
		protected static $database = 'MySQL';
		
		protected static function findUserByUsername()
		{
			self::dbConnect();
	        $table = static::$table;
	        $query = "SELECT * FROM $table WHERE username = :username";
	        $stmt = self::$mysqlDbc->prepare($query);
	        $stmt->bindValue(':username', $username, PDO::PARAM_INT);
	        $stmt->execute();
	        $result = $stmt->fetch(PDO::FETCH_ASSOC);
	        // Set the attributes on the calling object based on the result variable's contents
	        $instance = null;
	        if ($result)
	        {
	            $instance = new static;
	            $instance->attributes = $result;
	        }
	        return $instance;
		}

		public static function findByUniqId($unique_id)
		{
			self::dbConnect();
	        $table = static::$table;
	        $query = "SELECT * FROM $table WHERE unique_id = :unique_id";
	        $stmt = self::$mysqlDbc->prepare($query);
	        $stmt->bindValue(':unique_id', $unique_id, PDO::PARAM_STR);
	        $stmt->execute();
	        $result = $stmt->fetch(PDO::FETCH_ASSOC);
	        // Set the attributes on the calling object based on the result variable's contents
	        $instance = null;
	        if ($result)
	        {
	            $instance = new static;
	            $instance->id = $result['id'];
	            $instance->first_name = $result['first_name'];
	            $instance->last_name = $result['last_name'];
	            $instance->email = $result['email'];
	            $instance->password = $result['password'];
	            $instance->unique_id = $result['unique_id'];
	            $instance->cob_session = $result['cob_session'];
	            $instance->user_session = $result['user_session'];
	            $instance->fastlink_value = $result['fastlink_value'];
	            $instance->cob_session_created_at = $result['cob_session_created_at'];
	            $instance->user_session_created_at = $result['user_session_created_at'];
	            $instance->super_user = $result['super_user'];
	        }
	        return $instance;
		}

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
	            $instance->first_name = $result['first_name'];
	            $instance->last_name = $result['last_name'];
	            $instance->email = $result['email'];
	            $instance->password = $result['password'];
	            $instance->unique_id = $result['unique_id'];
	            $instance->cob_session = $result['cob_session'];
	            $instance->user_session = $result['user_session'];
	            $instance->fastlink_value = $result['fastlink_value'];
	            $instance->cob_session_created_at = $result['cob_session_created_at'];
	            $instance->user_session_created_at = $result['user_session_created_at'];
	            $instance->super_user = $result['super_user'];
	        }
	        return $instance;
		}

		public static function blocked()
		{
			self::dbConnect();
	        $table = static::$table;
	        $query = "SELECT * FROM $table WHERE id IN (SELECT user_id FROM login_attempts WHERE is_blocked > 0)";
	        $stmt = self::$mysqlDbc->prepare($query);
	        $stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
	}

    ///////////////////////////////////////////////////////////////////////////
    //                      EXAMPLES OF USAGE BELOW!!                     	 //
    ///////////////////////////////////////////////////////////////////////////

	/*
	 * Create new user and assign attributes
	 */
	// $user = new User();
	// $user->first_name = 'Pascal';
	// $user->last_name = 'Allen';
	// $user->email = 'thomaspascalallen@yahoo.com';
	// $user->save();

	/*
	 * Find user by id and update attributes
	 */
	// $user = User::find(13);
	// $user->first_name = 'Thomas';
	// $user->save();

	/*
	 * Get all users and echo their first names
	 */
	// $users = User::all();
	// foreach($users as $user)
	// {
	// 	echo $user->first_name;
	// }
?>