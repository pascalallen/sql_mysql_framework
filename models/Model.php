<?php
	class Model
	{
	    protected static $dbc;
	    protected static $mysqlDbc;
	    protected static $table;
	    protected static $database;

	    // Array to store key/value data
	    private $attributes = [];

	    /*
	     * Open dbc
	     */
	    public function __construct()
	    {
	        self::dbConnect();
	    }

	    /*
	     * Connect to the DB
	     */
	    public static function dbConnect()
	    {
	        if (!self::$dbc)
	        {
	            // Create SQL dbc
	            require "../db_connect.php";
	            self::$dbc = $dbc;
	        }
	        if (!self::$mysqlDbc)
	        {
	            // Create MySQL dbc
	            require "../mysql_connect.php";
	            self::$mysqlDbc = $mysqlDbc;
	        }
	    }

	    // Magic setter to populate array
	    public function __set($name, $value)
	    {
	        // Set the $name key to hold $value in $data
	        $this->attributes[$name] = $value;
	    }

	    // Magic getter to retrieve values from $data
	    public function __get($name)
	    {
	        // Check for existence of array key $name
	        if (array_key_exists($name, $this->attributes)) {
	            return $this->attributes[$name];
	        }
	        return null;
	    }

	    /*
	     * Chooses whether to insert or update, works for MySQL and SQL dbc.
	     *
	     * MySQL will just need to call `->save()`.
	     *
	     * SQL will need to pass in the primary key ($column) and the
	     * primary key value ($id) because the primary keys may vary. 
	     * It will look something like this, `->save(152, 'userid')`.
	     */
	    public function save($id = null, $column = null)
	    {
	    	// Check for attributes
	    	if (!empty($this->attributes))
	    	{
	    		/*
	    		 * MySQL tables have 'id' as primary key.
	    		 * If there is an 'id' attribute then just update, otherwise insert new.
	    		 */
	    		if(static::$database == 'MySQL'){
		    		if(isset($this->attributes['id']))
		    		{
		    			$this->update($this->attributes['id']);
		    		} else
		    		{
		    			$this->insert();
		    		}
	    		}
	    		/* 
	    		 * SQL tables have varying primary keys.
	    		 * If there is a primary key attribute ($id) the just update, otherwise insert new.
	    		 */
	    		if (static::$database == 'SQL') {
		    		if(isset($this->attributes) && isset($id))
		    		{
		    			$this->update($id, $column);
		    		} else
		    		{
		    			$this->insert();
		    		}
	    		}
	    	}
	    }

	    /*
	     * Put new row in database
	     * 
	     */
	    protected function insert()
	    {
	    	if (static::$database == 'SQL') {
		        $newKeysArray = [];
		        $keysArray = array_keys($this->attributes);
				$insert_table = "INSERT INTO " . static::$table . " (";
				$insert_table .= implode(', ', $keysArray);
				$insert_table .= ") VALUES (";
		        foreach ($keysArray as $key) { $newKeysArray[] = '?'; }
				$insert_table .= implode(', ', $newKeysArray);
				$insert_table .= ");";
		        foreach ($this->attributes as $key => $value) { $params[] = $value; }
				sqlsrv_query(self::$dbc, $insert_table, $params);
	    	}
	    	if(static::$database == 'MySQL'){
		        $newKeysArray = [];
		        $keysArray = array_keys($this->attributes);
				$insert_table = "INSERT INTO " . static::$table . " (";
				$insert_table .= implode(', ', $keysArray);
				$insert_table .= ") VALUES (";
		        foreach ($keysArray as $key) { $newKeysArray[] = ':'.$key; }
				$insert_table .= implode(', ', $newKeysArray);
				$insert_table .= ");";
				$stmt = self::$mysqlDbc->prepare($insert_table);
		        foreach ($this->attributes as $key => $value) { $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR); }
				$stmt->execute();
	    	}
	    }

	    /*
	     * Update existing row in database.
	     * 
	     * MySQL updates using the 'id' primary key.
	     *
	     * SQL updates using varying primary keys, you will 
	     * need to provide the primary key value ($id) and the primary key column name ($column).
	     * 
	     */
	    protected function update($id, $column = null)
	    {
	    	if (static::$database == 'SQL') {
		        $updateArray = [];
		        $table = static::$table;
		        foreach ($this->attributes as $key => $value)
		        {	
		        	// do not add as attribute 
		        	if ($key != $column) {
			            $update = $key . ' = ?';
			            array_push($updateArray, $update);
		        	}
		        }
		        $update_table = implode(', ', $updateArray);
		        $update_table = "UPDATE $table SET $update_table WHERE $column = ?";
		        $params = [];
		        foreach ($this->attributes as $key => $value)
		        {
		        	// do not add as attribute 
		        	if ($key != $column) {
		        		array_push($params, $value);
	        		}
		        }
		        array_push($params, $id);
		        sqlsrv_query(self::$dbc, $update_table, $params);
	        }
	        if(static::$database == 'MySQL'){
		        $updateArray = [];
		        $table = static::$table;
		        foreach ($this->attributes as $key => $value)
		        {
		            $update = $key . ' = :' . $key;
		            array_push($updateArray, $update);
		        }
		        $update_table = implode(', ', $updateArray);
		        $update_table = "UPDATE $table SET $update_table WHERE id = :id";
		        $stmt = self::$mysqlDbc->prepare($update_table);
		        foreach ($this->attributes as $key => $value)
		        {
		            $stmt->bindValue(':' . $key, $this->attributes[$key], PDO::PARAM_STR);
		            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
		        }
		        $stmt->execute();
	        }
	    }

	    /*
	     * Find a record based on an id.
	     *
	     * MySQL finds a record using the 'id' primary key.
		 *
	     * SQL finds a record using varying primary keys, you will 
	     * need to provide the primary key value ($id) and the primary key column name ($column).
	     */
	    public static function find($id, $column = null)
	    {
	        self::dbConnect();
	        if (static::$database == 'SQL'){
		        $table = static::$table;
		        // Create select statement using prepared statements
		        $query = "SELECT * FROM $table WITH (NOLOCK) WHERE $column = ?";
		        // Store the resultset in a variable named $result
		        $params = array($id);
		        $stmt = sqlsrv_query(self::$dbc, $query, $params);
		        $result = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
		        // The following code will set the attributes on the calling object based on the result variable's contents
		        $instance = null;
		        if ($result)
		        {
		            $instance = new static;
		            $instance->attributes = $result;
		        }
		        return $instance;
		    }
	        if (static::$database == 'MySQL'){
		        $table = static::$table;
		        $query = "SELECT * FROM $table WHERE id = :id";
		        $stmt = self::$mysqlDbc->prepare($query);
		        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
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
	    }

	    /*
	     *
	     * Returns table name.
	     *
	     */
	    public static function getTableName()
	    {
	    	return static::$table;
	    }

	    /*
	     * Find all records in a table
	     */
	    public static function all()
	    {
	        self::dbConnect();
	    	if (static::$database == 'SQL'){
		        $table = static::$table;
		        $query = "SELECT * FROM $table WITH (NOLOCK)";
		        $stmt = sqlsrv_query(self::$dbc, $query);
		        // array to store objects in
		        $results = [];
		        // if there are results, get * from $table
		        while (sqlsrv_has_rows($stmt) && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
		        	// create object and put in $results array
		        	$instance = null;
		            $instance = new static;
		            $instance->attributes = $row;
			        array_push($results, $instance);
		        }
		        // Return all the matching records
		        return $results;
	        }
	        if (static::$database == 'MySQL'){
		        $table = static::$table;
		        $query = "SELECT * FROM $table";
		        $stmt = self::$mysqlDbc->prepare($query);
		        $stmt->execute();
		        return $stmt->fetchAll(PDO::FETCH_ASSOC);
	        }
	    }

	    /*
	     * Count rows in a table
	     */
	    public static function count()
	    {
	        self::dbConnect();
	        if (static::$database == 'SQL'){
		        $table = static::$table;
		        $query = "SELECT * FROM $table WITH (NOLOCK)";
		        $stmt = sqlsrv_query(self::$dbc, $query, array(), array("Scrollable" => SQLSRV_CURSOR_KEYSET));
		        return sqlsrv_num_rows($stmt);
	        }
	        if (static::$database == 'MySQL'){
		        $table = static::$table;
				$query = "SELECT count(*) FROM $table";
				$stmt = self::$mysqlDbc->query($query);
				return $stmt->fetchColumn();
			}
	    }

	    /*
	     * Deletes a record based on an id.
	     *
	     * MySQL finds a record using the 'id' primary key.
		 *
	     * SQL finds a record using varying primary keys, you will 
	     * need to provide the primary key value ($id) and the primary key column name ($column).
	     */
	    public static function delete($id, $column = null)
	    {
	        self::dbConnect();
	    	if (static::$database == 'SQL'){
		        // Get connection to the database
		        $table = static::$table;
		        $query = "DELETE FROM $table WHERE $column = ?";
		        $params = array($id);
		        $stmt = sqlsrv_query(self::$dbc, $query, $params);
	        }
	        if (static::$database == 'MySQL'){
	        	$table = static::$table;
				$query = "DELETE FROM $table WHERE id = :id";
				$stmt = self::$mysqlDbc->prepare($query);
				$stmt->bindValue(':id', $id, PDO::PARAM_INT);
				$stmt->execute();
	        }
	    }

	}
?>