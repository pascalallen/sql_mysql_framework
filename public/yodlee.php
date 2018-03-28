<?php
    session_start();
    require "../models/Auth.php";
    require "../models/User.php";
    require "../models/Yodlee.php";
    require "../models/Input.php";

    if (!Auth::check()){
    	Auth::logout();
    }

    $user = User::find(Auth::user());

    if(!empty($user) && $user->cob_session_created_at > date("Y-m-d H:i:s",strtotime("100 minutes ago")) && $user->user_session_created_at > date("Y-m-d H:i:s",strtotime("30 minutes ago"))){
        $user_session = $user->user_session;
        $cob_session = $user->cob_session;
        $fastlink_value = $user->fastlink_value;
    }

    if (!empty($_POST['yodleeSignin'])) {
        $loginName = Input::has('loginName') ? Input::getString('loginName') : null;
        $password = Input::has('password') ? Input::getString('password') : null;
        if (Yodlee::login($loginName, $password, Auth::user())) {
            header("Refresh:0");
        }
    }

    if (!empty($_POST['transactionsSubmit'])) {
        $accountId = Input::has('accountId') ? Input::getString('accountId') : null;
        $container = Input::has('container') ? Input::getString('container') : null;
        $fromDate = Input::has('fromDate') ? Input::getString('fromDate') : null;
        $toDate = Input::has('toDate') ? Input::getString('toDate') : null;
        $cobSession = Input::has('cobSession') ? Input::getString('cobSession') : null;
        $userSession = Input::has('userSession') ? Input::getString('userSession') : null;
        $transactions = Yodlee::getTransactions($accountId, $container, $fromDate, $toDate, $cobSession, $userSession);
    }

    if (!empty($_POST['statementsSubmit'])) {
        $container = Input::has('container') ? Input::getString('container') : null;
        $accountId = Input::has('accountId') ? Input::getString('accountId') : null;
        $fromDate = Input::has('fromDate') ? Input::getString('fromDate') : null;
        $cobSession = Input::has('cobSession') ? Input::getString('cobSession') : null;
        $userSession = Input::has('userSession') ? Input::getString('userSession') : null;
        $statements = Yodlee::getStatements($accountId, $container, $fromDate, $cobSession, $userSession);
    }

    if (!empty($_POST['deleteSubmit'])) {
        $accountId = Input::has('accountId') ? Input::getString('accountId') : null;
        $cobSession = Input::has('cobSession') ? Input::getString('cobSession') : null;
        $userSession = Input::has('userSession') ? Input::getString('userSession') : null;
        Yodlee::deleteAccount($accountId, $cobSession, $userSession);
    }

?>
<!DOCTYPE html>
<html>
	<head>
	    <?php include "partials/head.php"; ?>
	</head>
	<body>
    	<?php include "partials/navbar.php"; ?>
	    <div class="col-sm-push-1 col-md-11 column">
	        <div class="row">
                <?php if (!empty($user_session) && !empty($cob_session)): ?>
                    <p>Go ahead, try getting transactions! <br>From Date: 01/01/2010 <br>To Date: 01/01/2018</p>
                    <form action="https://node.developer.yodlee.com/authenticate/restserver/" method="POST" target="_blank">
                        <input type="hidden" name="app" value="10003600">
                        <input type="hidden" name="rsession" value="<?= $user_session ?>">
                        <input type="hidden" name="token" value="<?= $fastlink_value ?>"> 
                        <input type="hidden" name="redirectReq" value="true"> 
                        <button type="submit" class="btn btn-primary btn-sm" name="submit">Link Bank Account</button>
                    </form>
                    <table class="table table-responsive table-striped table-condensed">
                        <thead>                         
                            <tr>
                                <th>Bank</th>
                                <th>Available</th>
                                <th>Current</th>
                                <th>Last Updated</th>
                                <th>Acct. Name</th>
                                <th>Acct. No.</th>
                                <th>Acct. Status</th>
                                <th>Acct. Type</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach(Yodlee::getBankAccounts($cob_session, $user_session)['account'] as $account): ?>
                                <?php if ($account['CONTAINER'] == 'bank'): ?>
                                    <tr><td><?= $account['providerName'] ?></td><td><?= $account['availableBalance']['amount'] ?></td><td><?= $account['currentBalance']['amount'] ?></td><td><?= date("m/d/Y", strtotime($account['lastUpdated'])) ?></td><td><?= $account['accountName'] ?></td><td><?= $account['accountNumber'] ?></td><td><?= $account['accountStatus'] ?></td><td><?= $account['accountType'] ?></td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#transactionDates">Transactions</button>
                                            <!-- Transaction Dates Modal -->
                                            <div class="modal fade" id="transactionDates" tabindex="-1" role="dialog" aria-labelledby="transactionDatesLabel" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="transactionDatesTitle">When?</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form method="POST">
                                                                <input type="hidden" name="cobSession" value="<?= $cob_session ?>">
                                                                <input type="hidden" name="userSession" value="<?= $user_session ?>">
                                                                <input type="hidden" name="accountId" value="<?= $account['id'] ?>">
                                                                <input type="hidden" name="container" value="<?= $account['CONTAINER'] ?>">
                                                                <label for="fromDate">From:</label>
                                                                <input type="date" name="fromDate">
                                                                <label for="toDate">To:</label>
                                                                <input type="date" name="toDate">
                                                                <button type="submit" name="transactionsSubmit" class="btn btn-sm btn-primary" value="1">Submit</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <br>
                                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#statementDates">Statements</button>
                                            <!-- Transaction Dates Modal -->
                                            <div class="modal fade" id="statementDates" tabindex="-1" role="dialog" aria-labelledby="statementDatesLabel" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="statementDatesTitle">How far back?</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form method="POST">
                                                                <input type="hidden" name="accountId" value="<?= $account['id'] ?>">
                                                                <input type="hidden" name="container" value="<?= $account['CONTAINER'] ?>">
                                                                <label for="fromDate">From:</label>
                                                                <input type="date" name="fromDate">
                                                                <input type="hidden" name="cobSession" value="<?= $cob_session ?>">
                                                                <input type="hidden" name="userSession" value="<?= $user_session ?>">
                                                                <button type="submit" name="statementsSubmit" class="btn btn-sm btn-primary" value="1">Submit</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <form method="POST">
                                                <input type="hidden" name="userSession" value="<?= $user_session ?>">
                                                <input type="hidden" name="cobSession" value="<?= $cob_session ?>">
                                                <input type="hidden" name="accountId" value="<?= $account['id'] ?>">
                                                <button type="submit" name="deleteSubmit" class="btn btn-sm btn-danger" value="1"> x </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endif ?>
                                <?php if ($account['CONTAINER'] == 'loan'): ?>
                                    <tr><td><?= $account['providerName'] ?></td><td>N/A</td><td><?= $account['balance']['amount'] ?></td><td><?= date("m/d/Y",strtotime($account['lastUpdated'])) ?></td><td><?= $account['accountName'] ?></td><td><?= $account['accountNumber'] ?></td><td><?= $account['accountStatus'] ?></td><td><?= $account['accountType'] ?></td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#transactionDates">Transactions</button>
                                            <!-- Transaction Dates Modal -->
                                            <div class="modal fade" id="transactionDates" tabindex="-1" role="dialog" aria-labelledby="transactionDatesLabel" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="transactionDatesTitle">When?</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form method="POST">
                                                                <input type="hidden" name="cobSession" value="<?= $cob_session ?>">
                                                                <input type="hidden" name="userSession" value="<?= $user_session ?>">
                                                                <input type="hidden" name="accountId" value="<?= $account['id'] ?>">
                                                                <input type="hidden" name="container" value="<?= $account['CONTAINER'] ?>">
                                                                <label for="fromDate">From:</label>
                                                                <input type="date" name="fromDate">
                                                                <label for="toDate">To:</label>
                                                                <input type="date" name="toDate">
                                                                <button type="submit" name="transactionsSubmit" class="btn btn-sm btn-primary" value="1">Submit</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <br>
                                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#statementDates">Statements</button>
                                            <!-- Transaction Dates Modal -->
                                            <div class="modal fade" id="statementDates" tabindex="-1" role="dialog" aria-labelledby="statementDatesLabel" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="statementDatesTitle">How far back?</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form method="POST">
                                                                <input type="hidden" name="accountId" value="<?= $account['id'] ?>">
                                                                <input type="hidden" name="container" value="<?= $account['CONTAINER'] ?>">
                                                                <label for="fromDate">From:</label>
                                                                <input type="date" name="fromDate">
                                                                <input type="hidden" name="cobSession" value="<?= $cob_session ?>">
                                                                <input type="hidden" name="userSession" value="<?= $user_session ?>">
                                                                <button type="submit" name="statementsSubmit" class="btn btn-sm btn-primary" value="1">Submit</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <form method="POST">
                                                <input type="hidden" name="userSession" value="<?= $user_session ?>">
                                                <input type="hidden" name="cobSession" value="<?= $cob_session ?>">
                                                <input type="hidden" name="accountId" value="<?= $account['id'] ?>">
                                                <button type="submit" name="deleteSubmit" class="btn btn-sm btn-danger" value="1"> x </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endif ?>
                                <?php if ($account['CONTAINER'] == 'creditCard'): ?>
                                    <tr><td><?= $account['providerName'] ?></td><td>N/A</td><td><?= $account['balance']['amount'] ?></td><td><?= date("m/d/Y",strtotime($account['lastUpdated'])) ?></td><td><?= $account['accountName'] ?></td><td><?= $account['accountNumber'] ?></td><td><?= $account['accountStatus'] ?></td><td><?= $account['accountType'] ?></td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#transactionDates">Transactions</button>
                                            <!-- Transaction Dates Modal -->
                                            <div class="modal fade" id="transactionDates" tabindex="-1" role="dialog" aria-labelledby="transactionDatesLabel" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="transactionDatesTitle">When?</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form method="POST">
                                                                <input type="hidden" name="cobSession" value="<?= $cob_session ?>">
                                                                <input type="hidden" name="userSession" value="<?= $user_session ?>">
                                                                <input type="hidden" name="accountId" value="<?= $account['id'] ?>">
                                                                <input type="hidden" name="container" value="<?= $account['CONTAINER'] ?>">
                                                                <label for="fromDate">From:</label>
                                                                <input type="date" name="fromDate">
                                                                <label for="toDate">To:</label>
                                                                <input type="date" name="toDate">
                                                                <button type="submit" name="transactionsSubmit" class="btn btn-sm btn-primary" value="1">Submit</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <br>
                                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#statementDates">Statements</button>
                                            <!-- Transaction Dates Modal -->
                                            <div class="modal fade" id="statementDates" tabindex="-1" role="dialog" aria-labelledby="statementDatesLabel" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="statementDatesTitle">How far back?</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form method="POST">
                                                                <input type="hidden" name="accountId" value="<?= $account['id'] ?>">
                                                                <input type="hidden" name="container" value="<?= $account['CONTAINER'] ?>">
                                                                <label for="fromDate">From:</label>
                                                                <input type="date" name="fromDate">
                                                                <input type="hidden" name="cobSession" value="<?= $cob_session ?>">
                                                                <input type="hidden" name="userSession" value="<?= $user_session ?>">
                                                                <button type="submit" name="statementsSubmit" class="btn btn-sm btn-primary" value="1">Submit</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <form method="POST">
                                                <input type="hidden" name="userSession" value="<?= $user_session ?>">
                                                <input type="hidden" name="cobSession" value="<?= $cob_session ?>">
                                                <input type="hidden" name="accountId" value="<?= $account['id'] ?>">
                                                <button type="submit" name="deleteSubmit" class="btn btn-sm btn-danger" value="1"> x </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endif ?>
                            <?php endforeach ?>
                        </tbody>
                    </table>

                    <?php if (!empty($transactions)): ?>
                        <table class="table table-responsive table-striped table-condensed">
                            <thead>                         
                                <tr>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Category</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Merchant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($transactions['transaction'] as $transaction): ?>
                                    <tr>
                                        <td>
                                            Type: <?= $transaction['type'] ?>
                                            <br>
                                            Subtype: <?= $transaction['subType'] ?>
                                            <br>
                                            Base Type: <?= $transaction['baseType'] ?>
                                        </td>
                                        <td><?= number_format($transaction['amount']['amount'],2) ?></td>
                                        <td>
                                            Cat. Type: <?= $transaction['categoryType'] ?>
                                            <br>
                                            Cat. ID: <?= $transaction['categoryId'] ?>
                                            <br>
                                            Cat.: <?= $transaction['category'] ?>
                                            <br>
                                            Cat. Source: <?= $transaction['categorySource'] ?>
                                        </td>
                                        <td>
                                            Created: <?= date("m/d/Y",strtotime($transaction['createdDate'])) ?>
                                            <br>
                                            Last Updated: <?= date("m/d/Y",strtotime($transaction['lastUpdated'])) ?>
                                            <br>
                                            Date: <?= date("m/d/Y",strtotime($transaction['date'])) ?>
                                            <br>
                                            Post Date: <?= date("m/d/Y",strtotime($transaction['postDate'])) ?>
                                        </td>
                                        <td><?= $transaction['description']['original'] ?></td>
                                        <td><?= $transaction['status'] ?></td>
                                        <td>
                                            <?= (isset($transaction['merchant']['id']) ? 'Merchant Id: '.$transaction['merchant']['id'] : '') ?>
                                            <br>
                                            <?= (isset($transaction['merchant']['source']) ? 'Merchant Source: '.$transaction['merchant']['source'] : '') ?>
                                            <br>
                                            <?= (isset($transaction['merchant']['name']) ? 'Merchant Name: '.$transaction['merchant']['name'] : '') ?>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    <?php endif ?>

                    <?php if (!empty($statements)): ?>
                        <table class="table table-responsive table-striped table-condensed">
                            <thead>                         
                                <tr>
                                    <th>Statement Date</th>
                                    <th>Due Date</th>
                                    <th>Last Payment</th>
                                    <th>Last Updated</th>
                                    <th>Min. Pay</th>
                                    <th>Due</th>
                                    <th>Last Pay</th>
                                    <th>Advance</th>
                                    <th>APR</th>
                                    <th>Latest</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($statements['statement'] as $statement): ?>
                                    <tr>
                                        <td><?= date("m/d/Y",strtotime($statement['statementDate'])) ?></td>
                                        <td><?= date("m/d/Y",strtotime($statement['dueDate'])) ?></td>
                                        <td><?= date("m/d/Y",strtotime($statement['lastPaymentDate'])) ?></td>
                                        <td><?= date("m/d/Y",strtotime($statement['lastUpdated'])) ?></td>
                                        <td><?= number_format($statement['minimumPayment']['amount'],2) ?></td>
                                        <td><?= number_format($statement['amountDue']['amount'],2) ?></td>
                                        <td><?= number_format($statement['lastPaymentAmount']['amount'],2) ?></td>
                                        <td><?= number_format($statement['cashAdvance']['amount'],2) ?></td>
                                        <td><?= $statement['apr'] ?></td>
                                        <td><?= $statement['isLatest'] ?></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    <?php endif ?>

                <?php else: ?>


                    <div class="col-md-4 col-md-offset-4">

                        <?php if (isset($_SESSION['error_msg'])) : ?>
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true" onclick="this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode); return false;">&times;</span></button>
                                <?php echo $_SESSION['error_msg']; ?>
                            </div>  
                            <?php unset($_SESSION['error_msg']); ?>
                        <?php endif; ?>

                        <h3>Sign in to access your accounts</h3>
                        <form method="POST"> 
                            <div class="form-group">
                                <input type="text" class="form-control" id="loginName" name="loginName" value="">
                                <small id="loginName-title" class="form-text text-muted">Username</small>
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control" id="password" name="password" value="">
                                <small id="password-title" class="form-text text-muted">Password</small>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-sm" name="yodleeSignin" value="1">Sign in</button>
                            </div>
                        </form>
                    </div>

                <?php endif ?>
            </div>
        	<div class="row">
			</div>
		</div>
        <?php include "partials/foot.php"; ?>
    </body>
</html>