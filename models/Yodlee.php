<?php 
	require_once "Model.php";
	class Yodlee extends Model
	{
		public static $cobSession;
		public static $cobrandId;
		public static $applicationId;
		public static $userSession;
		public static $userId;
		public static $fastlinkAppId;
		public static $fastlinkUrl;
		public static $fastlinkValue;

		/* 
		 * The cobrand login service authenticates a cobrand.
		 * Cobrand session in the response includes the cobrand session token (cobSession) 
		 * which is used in subsequent API calls like registering or signing in the user. 
		 * The cobrand session token expires every 100 minutes. This service can be 
		 * invoked to create a new cobrand session token. 
		 * Query parameters cobrandLogin and cobrandPassword are not supported; 
		 * support for the same is only available through body parameters. 
		 * Note: The content type has to be passed as application/json for the body parameter. 
		 */
		public static function getCobrandData()
		{
			$url = "https://developer.api.yodlee.com/ysl/restserver/v1/cobrand/login";

			$curl = curl_init();

			$parameters = [
				'cobrand' => [
					'cobrandLogin' => 'sbCobpascalallen',
					'cobrandPassword' => '2b55b9a3-0bc0-4e84-b9e4-32b32c87e2e3'
				]
			];

			curl_setopt_array($curl, array(
			    CURLINFO_HEADER_OUT => true,
			    CURLOPT_SSL_VERIFYPEER => false,
			    CURLOPT_URL => $url,
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_TIMEOUT => 360,
			    CURLOPT_HTTPHEADER => array(
			        'Content-Type: application/json'
			    ),
			    CURLOPT_POST => count($parameters),
			    CURLOPT_POSTFIELDS => json_encode($parameters)
			));

			$response = json_decode(curl_exec($curl), TRUE);

			if ($response) {
				self::$cobSession = $response['session']['cobSession'];
				self::$cobrandId = $response['cobrandId'];
				self::$applicationId = $response['applicationId'];
			}

		}

		/*
		 * The user login service allows the registered user to login into the application.
		 * The user session token expires every 30 minutes and can be regenerated using this service.
		 * Query parameters loginName and password are not supported;
		 * support for the same is only available through body parameters.
		 * userParam JSON body parameter is accepted as an input that has the following fields: 
		 *   a. locale 
		 *   b. loginName 
		 *   c. password 
		 * 
		 * Note: The content type has to be passed as application/json for the body parameter. 
		*/
		public static function login($loginName, $password, $userId)
		{
			self::getCobrandData(); // sets cobSession variable

		    $url = "https://developer.api.yodlee.com/ysl/restserver/v1/user/login";

		    $curl = curl_init();

		    $parameters = [
		    	'user' => [
		    		'loginName' => $loginName,
		    		'password' => $password
		    	]
		    ];

		    curl_setopt_array($curl, array(
		        CURLOPT_SSL_VERIFYPEER => false,
		        CURLOPT_URL => $url,
		        CURLOPT_RETURNTRANSFER => 1,
		        CURLOPT_TIMEOUT => 360,
		        CURLOPT_HTTPHEADER => array(
		            'Content-Type: application/json',
		            'Authorization: {cobSession='.self::$cobSession.'}'	
		        ),
		        CURLOPT_POST => count($parameters),
		        CURLOPT_POSTFIELDS => json_encode($parameters)
		    ));

		    $response = json_decode(curl_exec($curl), TRUE);

		    if (isset($response['errorCode'])) {
		    	$_SESSION['error_msg'] = $response['errorMessage'];
		    	return false;
		    }else{
				self::$userSession = $response['user']['session']['userSession'];
				self::$userId = $response['user']['id'];
				self::getFastlinkToken();

				require_once "User.php";
				$user = User::find($userId);
				$user->cob_session = self::$cobSession;
				$user->user_session = self::$userSession;
				$user->fastlink_value = self::$fastlinkValue;
				$user->cob_session_created_at = date("Y-m-d H:i:s");
				$user->user_session_created_at = date("Y-m-d H:i:s");
				$user->save();
				return true;
		    }
		}

		/*
		 * The add account service is used to add manual accounts.
		 * The response of add account service includes the account name , account number and Yodlee generated account id.
		 * All manual accounts added will be included as part of networth calculation by default.
		 * Add manual account support is available for bank, card, investment, insurance, loan and bills container only.
		 */
		public static function getBankAccounts($cobSession, $userSession)
		{
			$url = "https://developer.api.yodlee.com/ysl/restserver/v1/accounts";

			$curl = curl_init();

			curl_setopt_array($curl, array(
			    CURLOPT_SSL_VERIFYPEER => false,
			    CURLOPT_URL => $url,
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_TIMEOUT => 360,
			    CURLOPT_HTTPHEADER => array(
			        'Content-Type: application/json',
			        'Authorization: {cobSession='.$cobSession.',userSession='.$userSession.'}'
			    )
			));

		    $response = curl_exec($curl);

			if ($response) {
				return json_decode($response, TRUE); // return bank account data as object
			}
		}

		/*
		 * The Get Access Tokens service is used to retrieve the access tokens for the application id(s) provided.
		 * URL in the response can be used to launch the application for which token is requested.
		 */
		public static function getFastlinkToken()
		{
			$url = "https://developer.api.yodlee.com/ysl/restserver/v1/user/accessTokens?appIds=10003600";

			$curl = curl_init();

			curl_setopt_array($curl, array(
			    CURLOPT_SSL_VERIFYPEER => false,
			    CURLOPT_URL => $url,
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_TIMEOUT => 360,
			    CURLOPT_HTTPHEADER => array(
			        'Content-Type: application/json',
			        'Authorization: {cobSession='.self::$cobSession.',userSession='.self::$userSession.'}'
			    )
			));

			$response = json_decode(curl_exec($curl), TRUE);

			if ($response) {
				self::$fastlinkAppId = $response['user']['accessTokens'][0]['appId'];
				self::$fastlinkUrl = $response['user']['accessTokens'][0]['url'];
				self::$fastlinkValue = $response['user']['accessTokens'][0]['value'];
			}
		}

	    /*
		 * The Transaction service is used to get a list of transactions for a user.
		 * By default, this service returns the last 30 days of transactions from today's date.
		 * The search is performed on these attributes: original, consumer, and simple descriptions.
		 * Values for categoryId parameter can be fetched from get transaction category list service.
		 * The categoryId is used to filter transactions based on system-defined category as well as user-defined category.
		 * User-defined categoryIds should be provided in the filter with the prefix "U". E.g. U10002 
		 * The skip and top parameters are useful for paginating transactions (i.e., to fetch small transaction 
		 * payloads for performance reasons)
		 * Double quotes in the merchant name will be prefixed by backslashes (\) in the response, 
		 * e.g. Toys \"R\" Us.
		 * Note 1.Category input is deprecated and replaced with categoryId. 2.TDE is made available for bank and card accounts and for the US market only.The address field in the response is available only when the TDE key is turned on.
		 * accountReconType input parameter is relevant for investment accounts, provided the reconciliation feature is turned on.
	     *
	 	 */
	    public static function getTransactions($accountId, $container, $fromDate, $toDate, $cobSession, $userSession)
	    {
	    	// date has to be YYYY-MM-DD format
	    	$url = "https://developer.api.yodlee.com/ysl/restserver/v1/transactions?accountId=".$accountId."&container=".$container."&fromDate=".$fromDate."&toDate=".$toDate."";

			$curl = curl_init();

			curl_setopt_array($curl, array(
			    CURLOPT_SSL_VERIFYPEER => false,
			    CURLOPT_URL => $url,
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_TIMEOUT => 360,
			    CURLOPT_HTTPHEADER => array(
			        'Content-Type: application/json',
			        'Authorization: {cobSession='.$cobSession.',userSession='.$userSession.'}'
			    )
			));

			$response = json_decode(curl_exec($curl), TRUE);

			return $response;
	    }

	    /*
	     *
		 * The statements service is used to get the list of statement related information. 
		 * By default, all the latest statements of active and to be closed accounts are retrieved for the user. 
		 * Certain sites do not have both a statement date and a due date. When a fromDate is passed as an 
		 * input, all the statements that have the due date on or after the passed date are retrieved. 
		 * For sites that do not have the due date, statements that have the statement date 
		 * on or after the passed date are retrieved. 
		 * The default value of "isLatest" is true. To retrieve historical statements isLatest needs to be set to false.
		 *
		 * container: creditCard/loan/bill/insurance	query	String
		 * isLatest: (true/false)	query	String
		 * status: ACTIVE/TO_BE_CLOSED/CLOSED	query	String
		 * fromDate: from date for statement retrieval (YYYY-MM-DD)
		 *
		 */
	    public static function getStatements($accountId, $container, $fromDate, $cobSession, $userSession, $isLatest = 'true', $status = 'ACTIVE')
	    {
	    	// date has to be YYYY-MM-DD format
	    	$url = "https://developer.api.yodlee.com/ysl/restserver/v1/statements?accountId=".$accountId."&container=".$container."&isLatest=".$isLatest."&status=".$status."&fromDate=".$fromDate."";
			$curl = curl_init();

			curl_setopt_array($curl, array(
			    CURLOPT_SSL_VERIFYPEER => false,
			    CURLOPT_URL => $url,
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_TIMEOUT => 360,
			    CURLOPT_HTTPHEADER => array(
			        'Content-Type: application/json',
			        'Authorization: {cobSession='.$cobSession.',userSession='.$userSession.'}'
			    )
			));

			$response = json_decode(curl_exec($curl), TRUE);

			return $response;
	    }

	    /*
	     *
		 * The delete account service allows an account to be deleted.
		 * This service does not return a response. The HTTP response code is 204 (Success with no content).
	     *
	 	 */
	    public static function deleteAccount($accountId, $cobSession, $userSession)
	    {
			$url = "https://developer.api.yodlee.com/ysl/restserver/v1/accounts/".$accountId;

			$curl = curl_init();

			curl_setopt_array($curl, array(
			    CURLOPT_SSL_VERIFYPEER => false,
			    CURLOPT_URL => $url,
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_TIMEOUT => 360,
			    CURLOPT_CUSTOMREQUEST => "DELETE",
			    CURLOPT_HTTPHEADER => array(
			        'Content-Type: application/json',
			        'Authorization: {cobSession='.$cobSession.',userSession='.$userSession.'}'
			    )
			));

			$response = curl_exec($curl);

			if ($response) {
				return header("Refresh:0");
			}
	    }
	}
?>