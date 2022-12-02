var table;
var table_view;
var table_trans;
var save_method;

$(document).ready(function () {

	table_view = $("#table").DataTable({
		processing: true,
		serverSide: true,
		responsive: true,
		lengthChange: false,
		autoWidth: false,
		bPaginate: false,
		bInfo: false,
		// "order": [],
		ajax: {
			url: base_url + "/users/list_user/",
			type: "POST",
		},
	});

});

function reload_table() {
	$("#table").DataTable().ajax.reload();
}
