
(function ($) {
    function load_today_deliv(){
        alert('ss');
    }

    $(document).ready(function () {
        const fileInput = document.getElementById('file-input');

        fileInput.addEventListener('change', (e) =>
            alert(e.target.files),
        );
    });
    
    
});
