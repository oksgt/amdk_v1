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
				<?php
				$session = $this->session->userdata();
				if ($session['role_id'] == 1) { ?>
					<a class="navbar-brand" href="<?php echo base_url('/transactions') ?>"><img src="<?php echo base_url() ?>assets/theme/img/logopdam_bg.png" alt="AMDK" width="45" height="30"> AMDK - Perumdam TS</a>
				<?php } else { ?>
					<a class="navbar-brand" href="<?php echo base_url('/dashboard') ?>"><img src="<?php echo base_url() ?>assets/theme/img/logopdam_bg.png" alt="AMDK" width="45" height="30"> AMDK - Perumdam TS</a>
				<?php }
				?>
				<?php  ?>

			</div>

			<div id="main-menu" class="main-menu collapse navbar-collapse">
				<?php
				$session = $this->session->userdata();
				if ($session['role_id'] == 1) {
					include(APPPATH . '/views/admin_menu.php');
				} else {
					include(APPPATH . '/views/staff_menu.php');
				}
				?>
				<?php  ?>
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

							<?php
							$session = $this->session->userdata();
							if ($session['role_id'] == 1) { ?>
								You're logged in as "<?= $this->session->userdata('name'); ?>"
							<?php } else { ?>
								You're logged in as "<?= $this->session->userdata('name'); ?>"
							<?php }
							?>
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

	<!-- <script src="<?php echo base_url() ?>assets/theme/vendors/jquery/dist/jquery.js"></script> -->
	<script src="https://code.jquery.com/jquery-3.6.1.js" integrity="sha256-3zlB5s2uwoUzrXK3BT7AX3FyvojsraNFxCc2vC/7pNI=" crossorigin="anonymous"></script>
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
	<script src="<?php echo base_url() ?>assets/theme/js/jquery.redirect.js"></script>
	<!-- <script src="https://js.pusher.com/7.2/pusher.min.js"></script> -->

	<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

	<script type="text/javascript">
		var $ = jQuery;
	</script>

	<script>
		// $(document).ready(function(){
		//     Pusher.logToConsole = true;

		//     var pusher = new Pusher('cfbfa9d8250ed3b8bf69', {
		//         cluster: 'ap1',
		//         forceTLS: true
		//     });

		//     var channel = pusher.subscribe('my-channel');
		//     channel.bind('my-event', function(data) {
		//         // alert(JSON.stringify(data));
		//         alert(data.message);
		//     });
		// });
		$('#modalPelunasan').on('show.bs.modal', function(event) {
			var button = $(event.relatedTarget);
			var noTrans = button.data('trans-number');
			var modal = $(this);
    		modal.find('#noTrans').val(noTrans);
		});

		// Jquery Dependency

		$("input[data-type='currency']").on({
			keyup: function() {
				formatCurrency($(this));
			},
			blur: function() {
				formatCurrency($(this), "blur");
			},
		});

		function formatNumber(n) {
			// format number 1000000 to 1,234,567
			return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		}

		function formatCurrency(input, blur) {
			// appends $ to value, validates decimal side
			// and puts cursor back in right position.

			// get input value
			var input_val = input.val();

			// don't validate empty input
			if (input_val === "") {
				return;
			}

			// original length
			var original_len = input_val.length;

			// initial caret position
			var caret_pos = input.prop("selectionStart");

			// check for decimal
			if (input_val.indexOf(".") >= 0) {
				// get position of first decimal
				// this prevents multiple decimals from
				// being entered
				var decimal_pos = input_val.indexOf(".");

				// split number by decimal point
				var left_side = input_val.substring(0, decimal_pos);
				var right_side = input_val.substring(decimal_pos);

				// add commas to left side of number
				left_side = formatNumber(left_side);

				// validate right side
				right_side = formatNumber(right_side);

				// On blur make sure 2 numbers after decimal
				if (blur === "blur") {
					right_side += "00";
				}

				// Limit decimal to only 2 digits
				right_side = right_side.substring(0, 2);

				// join number by .
				input_val = "Rp " + left_side + "." + right_side;
			} else {
				// no decimal entered
				// add commas to number
				// remove all non-digits
				input_val = formatNumber(input_val);
				input_val = "Rp " + input_val;

				// final formatting
				if (blur === "blur") {
					input_val += ".00";
				}
			}

			// send updated string to input
			input.val(input_val);

			// put caret back in the right position
			var updated_len = input_val.length;
			caret_pos = updated_len - original_len + caret_pos;
			input[0].setSelectionRange(caret_pos, caret_pos);
		}
	</script>

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
	<?php } else if ($uriSegment == "deliveries") { ?>
		<script src="<?= base_url('assets/js/') ?>deliveries.js"></script>
	<?php } else if ($uriSegment == "dashboard") { ?>
		<script src="<?= base_url('assets/js/') ?>dashboard.js"></script>
	<?php } else if ($uriSegment == "report") { ?>
		<script src="<?= base_url('assets/js/') ?>report.js"></script>
	<?php } else { ?>
		<script src="<?= base_url('assets/js/') ?>app.js"></script>
	<?php } ?>

</body>

</html>
