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
		search: false,
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

function terminate_user(id) {
	Swal.fire({
		title: "Do you want to terminate user?",
		showCancelButton: true,
		confirmButtonText: "Yes, terminate.",
	}).then((result) => {
		/* Read more about isConfirmed, isDenied below */
		if (result.isConfirmed) {
			$.ajax({
				type: "POST",
				data: { id: id },
				url: base_url + "/users/terminate",
				success: function (data) {
                    var obj = JSON.parse(data);
                    if(obj.result){
                        Swal.fire("User Terminated!", "", "success");
                        reload_table();
                    } else {
                        Swal.fire("Failed!", "", "error");
                    }
				},
			});
		}
	});
}

function activate_user(id) {
	Swal.fire({
		title: "Do you want to activate user?",
		showCancelButton: true,
		confirmButtonText: "Yes, activate.",
	}).then((result) => {
		/* Read more about isConfirmed, isDenied below */
		if (result.isConfirmed) {
			$.ajax({
				type: "POST",
				data: { id: id },
				url: base_url + "/users/activate",
				success: function (data) {
                    var obj = JSON.parse(data);
                    if(obj.result){
                        Swal.fire("User Activated!", "", "success");
                        reload_table();
                    } else {
                        Swal.fire("Failed!", "", "error");
                    }
				},
			});
		}
	});
}
