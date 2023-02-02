var table;
var table_view;
var table_trans;
var save_method;

var table_list_trans;
var table_list_carier;

$(document).ready(function () {

	console.log('oke');

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
			url: base_url + "/deliveries/ajax_list/",
			type: "POST",
		},
	});
    // table_list_order();
    // table_list_carier();
	function assign(id){
		alert(id);
	}
});





(function ($) {
	function reload_table() {
		$("#table").DataTable().ajax.reload();
	}

	

})(jQuery);

function open_list_order(){
	$('#modal_select_order').modal('show');
}

function table_list_order(){
    table_list_trans = $("#table_list_trans").DataTable({
		processing: true,
		serverSide: true,
		responsive: true,
		lengthChange: false,
		autoWidth: false,
		bPaginate: false,
		bInfo: false,
		// "order": [],
		ajax: {
			url: base_url + "/deliveries/ajax_list_order/",
			type: "POST",
		},
	});
}

function table_list_carier(){
    table_list_carier = $("#table_list_carier").DataTable({
		processing: true,
		serverSide: true,
		responsive: true,
		lengthChange: false,
		autoWidth: false,
		bPaginate: false,
		bInfo: false,
		// "order": [],
		ajax: {
			url: base_url + "/deliveries/ajax_list_carier/",
			type: "POST",
		},
	});
}

