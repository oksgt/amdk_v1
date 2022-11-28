<!DOCTYPE html>
<html lang="en">

<head>
    <title>AMDK - Perumdam Tirta Satria</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--===============================================================================================-->
    <link rel="icon" type="image/png" href="<?= base_url('assets/login_page/')?>images/icons/favicon.ico" />
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/login_page/')?>vendor/bootstrap/css/bootstrap.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/login_page/')?>fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/login_page/')?>fonts/Linearicons-Free-v1.0.0/icon-font.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/login_page/')?>vendor/animate/animate.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/login_page/')?>vendor/css-hamburgers/hamburgers.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/login_page/')?>vendor/animsition/css/animsition.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/login_page/')?>vendor/select2/select2.min.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/login_page/')?>vendor/daterangepicker/daterangepicker.css">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/login_page/')?>css/util.css">
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/login_page/')?>css/main.css">
    <!--===============================================================================================-->
</head>

<body>

    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100 p-b-160 p-t-50">
                <form id="login-form" class="login100-form validate-form" action="<?= base_url('login/action')?>" method="post">
                    <span class="login100-form-title p-b-43">
                        Account Login
                    </span>

                    <?= $this->session->flashdata('message'); ?>

                    <div class="wrap-input100 rs1 validate-input" data-validate="Username is required">
                        <input class="input100" type="text" name="username" >
                        <span class="label-input100">Username</span>
                    </div>


                    <div class="wrap-input100 rs2 validate-input" data-validate="Password is required">
                        <input class="input100" type="password" name="password">
                        <span class="label-input100">Password</span>
                    </div>

                    <div class="container-login100-form-btn">
                        <button class="login100-form-btn" type="submit">
                            Sign in
                        </button>
                    </div>

                    <div class="text-center w-full p-t-23">
                        <!-- <a href="#" class="txt1">
                            Forgot password?
                        </a> -->
                        <label class="txt1" for="">Copyright @ <?= Date('Y')?>; Perumdam Tirta Satria Kab. Banyumas</label>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!--===============================================================================================-->
    <script src="<?= base_url('assets/login_page/')?>vendor/jquery/jquery-3.2.1.min.js"></script>
    <!--===============================================================================================-->
    <script src="<?= base_url('assets/login_page/')?>vendor/animsition/js/animsition.min.js"></script>
    <!--===============================================================================================-->
    <script src="<?= base_url('assets/login_page/')?>vendor/bootstrap/js/popper.js"></script>
    <script src="<?= base_url('assets/login_page/')?>vendor/bootstrap/js/bootstrap.min.js"></script>
    <!--===============================================================================================-->
    <script src="<?= base_url('assets/login_page/')?>vendor/select2/select2.min.js"></script>
    <!--===============================================================================================-->
    <script src="<?= base_url('assets/login_page/')?>vendor/daterangepicker/moment.min.js"></script>
    <script src="<?= base_url('assets/login_page/')?>vendor/daterangepicker/daterangepicker.js"></script>
    <!--===============================================================================================-->
    <script src="<?= base_url('assets/login_page/')?>vendor/countdowntime/countdowntime.js"></script>
    <!--===============================================================================================-->
    <script src="<?= base_url('assets/login_page/')?>js/main.js"></script>

    <script>
        $( document ).ready(function() {
            $('#login-form').find("input[type=text]").val("");
            $('#login-form').find("input[type=password]").val("");
        });
    </script>

</body>

</html>