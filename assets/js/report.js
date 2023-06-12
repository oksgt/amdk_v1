$(document).ready(function () {
    $('.datepicker').datepicker({
		language: "en",
		autoclose: true,
		format: "dd/mm/yyyy",
		todayHighlight: true,
	});

	// $('#my-form').submit(function(event) {
    //     // Prevent the form from submitting via HTTP
    //     event.preventDefault();

    //     // Get the form data as an object
    //     var formData = $(this).serialize();

    //     // Send an AJAX request to the server
    //     $.ajax({
    //         url: base_url + '/report/create_excel',
    //         type: 'POST',
    //         data: formData,
    //         success: function(response) {
    //             // console.log(response);
    //         },
    //         error: function(xhr, status, error) {
    //             console.log(xhr.responseText);
    //         }
    //     });
    // });
});