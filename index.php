<?php include "index-controller.php"; ?>
<html>
<head>
    <link href="resources/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="resources/style.css" rel="stylesheet">
    <script src="resources/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</head>
<body>

<?php include "includes/app-title.php"; ?>

<h2 class="text-center">
    A Cryptographic Application
</h2>

<div class="row mt-5">
    <div class="col-10 offset-1 col-sm-8 offset-sm-2  col-md-4 offset-md-4">

        <div class="login-box">
            <form method="post" action="login.php">

                <div class="row mb-4 mt-2">
                    <div class="col-4">
                        <label for="username">User name:</label>
                    </div>
                    <div class="col">
                        <input name="username" type="text" class="form-control"/>
                    </div>
                </div>
                <div class="row text-end">
                    <div class="col">
                        <button class="btn btn-dark">
                            Login
                        </button>
                    </div>
                </div>
            </form>

    </div>


    <?php include "includes/affiliation.php";?>

</div>

</body>
</html>
