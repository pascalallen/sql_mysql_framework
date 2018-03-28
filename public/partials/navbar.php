    <?php require_once "../models/Auth.php"; ?>

    <div class="col-md-1 navbar column">

        <!-- home page -->
        <a href="/home.php" data-toggle="tooltip" data-placement="bottom" title="Home"><i class="far fa-star fa-2x" aria-hidden="true"></i></a>
        <hr>

        <!-- credit portal -->
        <a href="/app.php" data-toggle="tooltip" data-placement="bottom" title="App" data-html="true"><i class="far fa-credit-card fa-2x nav-icon" aria-hidden="true"></i></a>
        <br>
        <br>

        <?php if(Auth::admin()): ?>

            <!-- add users -->
            <a href="/users-index.php" data-toggle="tooltip" data-placement="bottom" title="Users" data-html="true"><i class="fas fa-users fa-2x nav-icon" aria-hidden="true"></i></a>
            <br>
            <br>

            <!-- logs -->
            <a href="/logs.php" data-toggle="tooltip" data-placement="bottom" title="Logs" data-html="true"><i class="fas fa-book fa-2x nav-icon" aria-hidden="true"></i></a>
            <br>
            <br>

            <!-- blocked users -->
            <a href="/blocked-users.php" data-toggle="tooltip" data-placement="bottom" title="Blocked Users" data-html="true"><i class="fas fa-ban fa-2x nav-icon" aria-hidden="true"></i></a>
            <br>
            <br>

            <!-- add admins -->
            <a href="/admins.php" data-toggle="tooltip" data-placement="bottom" title="Admins" data-html="true"><i class="fas fa-key fa-2x nav-icon" aria-hidden="true"></i></a>
            <br>
            <br>

        <?php endif ?>

        <!-- yodlee -->
        <a href="/yodlee.php" data-toggle="tooltip" data-placement="bottom" title="Yodlee"><i class="fas fa-university fa-2x nav-icon" aria-hidden="true"></i></a>
        <br>
        <br>

        <!-- contact -->
        <a href="" data-toggle="modal" title="Contact" data-target="#contactModal"><i class="far fa-envelope fa-2x nav-icon" aria-hidden="true"></i></a>
        <br>
        <br>

        <!-- logout -->
        <a href="/logout.php" data-toggle="tooltip" data-placement="bottom" title="Logout" data-html="true"><i class="fas fa-sign-out-alt fa-2x nav-icon" aria-hidden="true"></i></a>
        
    </div>