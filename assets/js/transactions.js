var table;
var table_view;
var table_trans;
var save_method;

$(document).ready(function () {

	$('.datepicker').datepicker({
		language: "en",
		autoclose: true,
		format: "dd/mm/yyyy"
	});
	
	form_validation();

	table_view = $("#table-view").DataTable({
		processing: true,
		serverSide: true,
		responsive: true,
		lengthChange: false,
		autoWidth: false,
		bPaginate: false,
		bInfo: false,
		// "order": [],
		ajax: {
			url: base_url + "/transactions/trans_details_list/"+$('#trans_number').val(),
			type: "POST",
		},
	});

	table = $("#table").DataTable({
		processing: true,
		serverSide: true,
		responsive: true,
		lengthChange: false,
		autoWidth: false,
		bPaginate: false,
		bInfo: false,
		// "order": [],
		ajax: {
			url: base_url + "/transactions/trans_details_list/"+$('#trans_number').val(),
			type: "POST",
		},
	});

	table_trans = $("#table_trans").DataTable({
		processing: true,
		serverSide: true,
		responsive: true,
		lengthChange: false,
		autoWidth: false,
		bPaginate: false,
		bInfo: false,
		// "order": [],
		ajax: {
			url: base_url + "/transactions/trans_list/",
			type: "POST",
		},
	});

	grand_total();
});

$("input[name='input_qty']").on("input", function (e) {
	$(this).val(
		$(this)
			.val()
			.replace(/[^0-9]/g, "")
	);
});

function form_validation() {
	$("#trans-form").on("submit", function (event) {
		event.preventDefault();
		event.stopPropagation();

		var input_list = ["product", "input_qty"];

		var input_list_error = ["product_error_detail", "input_qty_error_detail"];

		$.ajax({
			url: base_url + "transactions/validation/",
			method: "POST",
			data: $(this).serialize(),
			dataType: "json",
			beforeSent: function () {
				$("#btn-save").attr("disabled", true);
			},
			success: function (data) {
				if (data.error) {
					for (let index = 0; index < input_list.length; index++) {
						const input_ = input_list[index];
						const input_error = input_list_error[index];
						if (data[input_error] !== "") {
							$("[id=" + input_error + "]").html(data[input_error]);
							$("[id=" + input_ + "]").addClass("is-invalid");
						} else {
							$("[id=" + input_error + "]").html("");
							$("[id=" + input_ + "]").removeClass("is-invalid");
							$("[id=" + input_ + "]").addClass("is-valid");
						}
					}
				}

				if (data.success) {
					for (let index = 0; index < input_list.length; index++) {
						const input_ = input_list[index];
						const input_error = input_list_error[index];

						$("[id=" + input_error + "]").html("");
						$("[id=" + input_ + "]").removeClass("is-invalid");
						$("[id=" + input_ + "]").addClass("is-valid");
					}

					$.ajax({
						url: base_url + "transactions/check_stock/"+$('#product').val()+"/"+$('#input_qty').val(),
						method: 'GET',
						dataType: 'json',
						success: function (data) {
							console.log(data.result);
							if(data.result){
								save();
							} else {
								Swal.fire("Oups!", data.message, "warning");
							}
						}
					});

					
					// close();
				}

				$("#btn-save").attr("disabled", false);
			},
		});
	});
}

function close(){
	$("#addProduct").modal("hide");
}

function save() {
	var url;
	url = base_url + "transactions/save_item/";

	var form = $("#trans-form")[0];
	var formData = new FormData(form);
	$.ajax({
		url: url,
		method: "POST",
		data: formData,
		dataType: "json",
		contentType: false,
		processData: false,
		success: function (data) {
			// var obj = JSON.parse(data);
			if (data.result) {
				Swal.fire("Good job!", "Data saved successfully!", "success");
				reload_table();
				grand_total();
				$('#trans-form')[0].reset();
				$("#addProduct").modal("hide");
				$('body').removeClass('modal-open');
				$('.modal-backdrop').remove();
			} else {
				Swal.fire("Oups!", data.message, "warning");
			}
		},
	});
}

function reload_table() {
	$("#table").DataTable().ajax.reload();
}

function show_edit(id){
	// console.log('oke');
	Swal.fire({
		title: 'Update Quantity',
		input: 'text',
		inputAttributes: {
		  autocapitalize: 'off'
		},
		showCancelButton: true,
		confirmButtonText: 'Update',
		showLoaderOnConfirm: true,
		preConfirm: (jumlah) => {
			$.ajax({
				type: 'POST',
				url: base_url + 'transactions/update_qty',
				dataType: "json",
				data:({
					"qty": jumlah,
					"id" : id
				}),
				success: function (data) {
					if(data.result){
						Swal.fire("Good job!", "Item Updated!", "success");
						reload_table();
						grand_total();
					} else {
						Swal.fire("Oups!", data.message, "warning");
					}
				}
			});
			return false;
		},
		allowOutsideClick: () => !Swal.isLoading()
	  });
    // $('#trans-form')[0].reset();
    // save_method = 'add';
	// $('#btn-save').html('<b class="fa fa-save"></b> Save');
	// $('#btn-save').removeClass('bg-gradient-warning');
	// $('#btn-save').addClass('bg-gradient-primary');

	// document.getElementById("qrcode-img").src = base_url + "assets/template/img/undraw_Images_re_0kll.svg"; 
	// $('#btn-print').css('display', 'none');
	// $('#qrcode-caption').html("");

	// reset_validation();
	// $('.modal-title').text('Add Im');
    // $('#editProduct').modal('show');
}

function grand_total(){
	$.ajax({
		url: base_url + "transactions/sum_transaction_detail/"+$('#trans_number').val(),
		method: 'GET',
		dataType: 'json',
		success: function (data) {
			console.log(data.result);

			// Create our number formatter.
			
			const formatRupiah = (money) => {
				return new Intl.NumberFormat('id-ID',
				{ style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }
				).format(money);
			}
			
			$('.grand_total').text(formatRupiah(data.result));
		}
	});
}

function check_stock(){
	$.ajax({
		url: base_url + "transactions/check_stock/"+$('#product').val()+"/"+$('#input_qty').val(),
		method: 'GET',
		dataType: 'json',
		success: function (data) {
			console.log(data.result);
		}
	});
}