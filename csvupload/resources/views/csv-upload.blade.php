<!DOCTYPE html>
<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CSV Upload</title>
    <style>
        #dropZone {
            padding: 40px;
            border: 2px dashed #666;
            text-align: center;
            margin-bottom: 20px;
        }
        #dropZone.hover { border-color: green; }
        table
        {
            width: 100%;
        }

        body
        {
            width: 90%;
            margin: auto;
        }
    </style>
</head>
<body>

<h2>CSV Upload</h2>

<div id="dropZone">Drag & Drop CSV here or Click Upload</div>
<input type="file" id="fileInput" accept=".csv" style="display:none;">

<p id="msg"></p>

<table border="1" width="100%">
    <thead>
        <tr>
            <th>Uploaded Time</th>
            <th>File Name</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody id="statusTable"></tbody>
</table>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

// ✅ Click to open file picker
$("#dropZone").on("click", function() {
    $("#fileInput").click();
});

$("#fileInput").on("change", function(e) {
    uploadFile(e.target.files[0]);
});

// ✅ Drag & Drop upload
$('#dropZone').on('dragover', e => {
    e.preventDefault();
    $('#dropZone').addClass('hover');
});
$('#dropZone').on('dragleave', () => $('#dropZone').removeClass('hover'));
$('#dropZone').on('drop', e => {
    e.preventDefault();
    $('#dropZone').removeClass('hover');
    uploadFile(e.originalEvent.dataTransfer.files[0]);
});

// ✅ Upload CSV
function uploadFile(file) {
    let formData = new FormData();
    formData.append('file', file);

    $.ajax({
        url: "{{ route('csv.upload') }}",
        method: "POST",
        data: formData,
        contentType: false,
        processData: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },

        success: () => {
            $('#msg').text("✅ File uploaded. Processing...");

            let check = setInterval(() => {
                $.get("{{ route('csv.status') }}", function(data) {
                    if (data.length > 0) {
                        let latest = data[0].status;

                        if (latest === 'completed') {
                            $('#msg').text("✅ Import completed!");
                            clearInterval(check);
                        }
                        else if (latest === 'failed') {
                            $('#msg').text("❌ Import failed!");
                            clearInterval(check);
                        }
                    }
                });
            }, 2000);
        },
        error: (xhr) => {
            let errorText = "❌ Upload failed";

            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                // Laravel validation error
                let firstError = Object.values(xhr.responseJSON.errors)[0][0];
                errorText = "❌ " + firstError;
            } else if (xhr.responseJSON && xhr.responseJSON.error) {
                errorText = "❌ " + xhr.responseJSON.error;
            } else if (xhr.responseText) {
                errorText = "❌ " + xhr.responseText;
            }

            $('#msg').text(errorText);
            loadStatus();
        }
    });
}

// ✅ Load upload status list
function loadStatus() {
    $.get("{{ route('csv.status') }}", function(data) {
        $('#statusTable').empty();

        data.forEach(row => {
            $('#statusTable').append(`
                <tr>
                    <td>${row.created_at}</td>
                    <td>${row.file_name}</td>
                    <td>${row.status}</td>
                </tr>
            `);
        });
    });
}

// Poll every 3 seconds
setInterval(loadStatus, 1000);
loadStatus();

</script>

</body>
</html>
