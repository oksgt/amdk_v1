<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="en">
<!--<![endif]-->
<script>
    var base_url = '<?php echo base_url() ?>';
</script>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>AMDK - Perumdam Tirta Satria</title>
    <meta name="description" content="Sufee Admin - HTML5 Admin Template">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" href="apple-icon.png">
    <link rel="shortcut icon" href="favicon.ico">

    <link rel="stylesheet" href="<?php echo base_url() ?>assets/theme/vendors/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url() ?>assets/theme/vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo base_url() ?>assets/theme/vendors/themify-icons/css/themify-icons.css">
    <link rel="stylesheet" href="<?php echo base_url() ?>assets/theme/vendors/flag-icon-css/css/flag-icon.min.css">
    <link rel="stylesheet" href="<?php echo base_url() ?>assets/theme/vendors/selectFX/css/cs-skin-elastic.css">
    <link rel="stylesheet" href="<?php echo base_url() ?>assets/theme/vendors/jqvmap/dist/jqvmap.min.css">
    <link rel="stylesheet" href="<?php echo base_url() ?>assets/theme/css/dataTables.bootstrap.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker3.min.css">

    <link rel="stylesheet" href="<?php echo base_url() ?>assets/theme/assets/css/style.css">

    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800' rel='stylesheet' type='text/css'>

</head>

<body>

    <!-- Left Panel -->

    <aside id="left-panel" class="left-panel" style="background-color: #2596be !important">
        <nav class="navbar navbar-expand-sm navbar-default" style="background-color: #2596be !important">

            <div class="navbar-header">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-menu" aria-controls="main-menu" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fa fa-bars"></i>
                </button>
                <a class="navbar-brand" href="./"><img src="<?php echo base_url() ?>assets/theme/img/logopdam_bg.png" alt="AMDK" width="45" height="30"> PERUMDAM TS</a>
                <!-- <a class="navbar-brand hidden" href="./"><img src="<?php echo base_url() ?>assets/theme/img/logopdam_bg.png" alt="AMDK" width="90" height="60"></a> -->
            </div>

            <div id="main-menu" class="main-menu collapse navbar-collapse">
                <?php include(APPPATH . '/views/admin_menu.php'); ?>
            </div><!-- /.navbar-collapse -->
        </nav>
    </aside><!-- /#left-panel -->

    <!-- Left Panel -->

    <!-- Right Panel -->

    <div id="right-panel" class="right-panel">

        <!-- Header-->
        <header id="header" class="header">

            <div class="header-menu">

                <div class="col-sm-7">

                </div>

                <div class="col-sm-5">
                    <div class="user-area dropdown float-right">
                        <a href="#" class="dropdown-toggle text-dark" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <!-- <img class="user-avatar rounded-circle" src="images/admin.jpg" alt="Nama"> -->
                            You're logged in as "<?= $this->session->userdata('name'); ?>"
                        </a>

                        <div class="user-menu dropdown-menu">
                            <a class="nav-link" href="<?= base_url('login/logout') ?>"><i class="fa fa-power-off"></i> Logout</a>
                        </div>
                    </div>


                </div>
            </div>

        </header><!-- /header -->
        <!-- Header-->


        <?php echo $contents; ?>


    </div><!-- /#right-panel -->

    <!-- Right Panel -->

    <script src="<?php echo base_url() ?>assets/theme/vendors/jquery/dist/jquery.min.js"></script>
    <script src="<?php echo base_url() ?>assets/theme/vendors/popper.js/dist/umd/popper.min.js"></script>
    <script src="<?php echo base_url() ?>assets/theme/vendors/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="<?php echo base_url() ?>assets/theme/assets/js/main.js"></script>


    <!-- <script src="<?php echo base_url() ?>assets/theme/vendors/chart.js/dist/Chart.bundle.min.js"></script>
    <script src="<?php echo base_url() ?>assets/theme/assets/js/dashboard.js"></script>
    <script src="<?php echo base_url() ?>assets/theme/assets/js/widgets.js"></script>
    <script src="<?php echo base_url() ?>assets/theme/vendors/jqvmap/dist/jquery.vmap.min.js"></script>
    <script src="<?php echo base_url() ?>assets/theme/vendors/jqvmap/examples/js/jquery.vmap.sampledata.js"></script>
    <script src="<?php echo base_url() ?>assets/theme/vendors/jqvmap/dist/maps/jquery.vmap.world.js"></script> -->

    <script src="<?php echo base_url() ?>assets/theme/js/datatable/datatables.min.js"></script>
    <script src="<?php echo base_url() ?>assets/theme/js/datatable/dataTables.bootstrap.min.js"></script>
    <script src="<?php echo base_url() ?>assets/theme/js/datatable/dataTables.buttons.min.js"></script>
    <script src="<?php echo base_url() ?>assets/theme/js/datatable/buttons.bootstrap.min.js"></script>
    <script src="<?php echo base_url() ?>assets/theme/js/datatable/datatables-init.js"></script>

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

    <?php
    $uriSegment = $this->uri->segment(1);
    if ($uriSegment == "products") { ?>
        <script src="<?= base_url('assets/js/') ?>product.js"></script>
    <?php } else if ($uriSegment == "stocks") { ?>
        <script src="<?= base_url('assets/js/') ?>stocks.js"></script>
    <?php } else if ($uriSegment == "transactions") { ?>
        <script src="<?= base_url('assets/js/') ?>transactions.js"></script>
    <?php } else if ($uriSegment == "users") { ?>
        <script src="<?= base_url('assets/js/') ?>users.js"></script>
    <?php } else if ($uriSegment == "") { ?>
        <script src="<?= base_url('assets/js/') ?>app.js"></script>
    <?php } else { ?>
        <script src="<?= base_url('assets/js/') ?>app.js"></script>
    <?php } ?>

</body>

</html>