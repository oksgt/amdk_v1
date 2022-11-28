var table;

(function ($) {
	$(document).ready(function () {
		table = $("#table").DataTable({
			processing: true,
			serverSide: true,
			responsive: true,
			lengthChange: false,
			autoWidth: false,
			// "order": [],
			ajax: {
				url: base_url + "/stocks/ajax_list",
				type: "POST",
			},
		});
	});

	$("input[name='input_stock']").on("input", function (e) {
		$(this).val(
			$(this)
				.val()
				.replace(/[^0-9]/g, "")
		);
	});

})(jQuery);

function reload_table() {
	$("#table").DataTable().ajax.reload();
}

function delete_data(id) {
	Swal.fire({
		title: "Do you want to delete data?",
		showCancelButton: true,
		confirmButtonText: "Yes, delete.",
	}).then((result) => {
		/* Read more about isConfirmed, isDenied below */
		if (result.isConfirmed) {
			$.ajax({
				type: "POST",
				data: { id: id },
				url: base_url + "/stocks/soft_delete",
				success: function (data) {
                    var obj = JSON.parse(data);
                    if(obj.result){
                        Swal.fire("Deleted!", "", "success");
                        reload_table();
                    } else {
                        Swal.fire("Failed!", "", "error");
                    }
				},
			});
		}
	});
}
