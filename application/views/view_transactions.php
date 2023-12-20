<div class="breadcrumbs">
	<div class="col-sm-4">
		<div class="page-header float-left">
			<div class="page-title">
				<h1>Transactions</h1>
			</div>
		</div>
	</div>
	<div class="col-sm-8">

	</div>
</div>

<div class="content mt-3">
	<div class="animated fadeIn">
		<div class="row">

			<div class="col-md-12">

				<div id="alert-container">
					<?=
					$this->session->flashdata('item');
					?>
				</div>


				<div class="card">
					<div class="card-header bg-white">
						<div class="btn-group-sm" role="group">
							<a type="button" class="btn btn-primary btn-sm" href="<?= base_url('transactions/add') ?>"><b class="ti-plus"></b> Add New Transaction</a>
							<!-- <button type="button" class="btn btn-primary btn-sm"><b class="ti-reload"></b> Push</button> -->
						</div>
					</div>
					<div class="card-body table-responsive">
						<table id="table_trans" class="table table-striped table-sm small">
							<thead>
								<tr>
									<th>Order</th>
									<!-- <th>#</th> -->
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
				</div>
			</div>


		</div>
	</div><!-- .animated -->
</div>

<!-- Modal -->
<div class="modal fade" id="modalPelunasan" tabindex="-1" role="dialog" aria-labelledby="modalPelunasanLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalPelunasanLabel">Input Pelunasan</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form>
					<div class="form-group">
						<label for="noTrans">Trans Number</label>
						<input type="text" class="form-control" id="noTrans" name="noTrans" readonly>
					</div>
					<div class="form-group">
						<label for="nominal">Payment Date</label>
						<input type="text" class="form-control" id="payment_date" name="payment_date"
						 value="<?= formatTglIndo(Date('Y-m-d')) ?>" readonly>
					</div>
					<div class="form-group">
						<label for="amount">Amount</label>
						<input type="text" class="form-control" name="amount" id="amount" data-type="currency" 
						placeholder="Payment amount" required>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" onclick="savePelunasan()">Save</button>
			</div>
		</div>
	</div>
</div>

<script>
	function savePelunasan() {
		var noTrans = document.getElementById('noTrans').value;
		var amount = document.getElementById('amount').value;

		var data = {
			noTrans: noTrans,
			amount: amount
		};

		// Kirim data dengan menggunakan Ajax POST
		$.ajax({
			url: base_url + "transactions/savePelunasan/",
			type: 'POST',
			data: data,
			dataType: 'json',
			success: function(response) {
				if (response.result) {
					Swal.fire("Good job!", "Data saved successfully!", "success").then(() => {
								location.reload();
							});
				} else {
					Swal.fire("Oups!", response.message, "warning");
				}
			},
			error: function() {
				Swal.fire("Oups!", response.message, "warning");
			}
		});
	}

	function delete_transaction_confirm(id) {
		Swal.fire({
			title: "Apakah anda yakin?",
			text: "Data transaksi akan dihapus!",
			icon: "warning",
			showCancelButton: true,
			confirmButtonText: "Yes",
		}).then((result) => {
			if (result.value) {
				$.ajax({
					url: base_url + "transactions/delete_trans/" + id,
					type: "POST",
					dataType: "json",
					success: function(data) {
						if (data.result) {
							Swal.fire("Good job!", "Data deleted successfully!", "success").then(() => {
								location.reload();
							});
						} else {
							Swal.fire("Oups!", data.message, "warning");
						}
					},
				});
			}
		});
	}
</script>
