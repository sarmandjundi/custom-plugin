<!DOCTYPE html>
<html lang="en">

<head>
    <!-- ... (your existing head content) ... -->
</head>

<body>
    <form id="fileForm">
        <input type="file" name="fileToUpload" id="fileToUpload">
        <input type="button" value="Överför fil" onclick="uploadFile()">
    </form>

    <script>
        function uploadFile() {
            var input = document.getElementById('fileToUpload');
            var file = input.files[0];

            if (file) {
                var formData = new FormData();
                formData.append('fileToUpload', file);

                // Use AJAX to send the file to the server
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'upload.php', true);
                xhr.onload = function() {
                    // Handle the response from the server
                    console.log(xhr.responseText);
                };
                xhr.send(formData);

                // Now, you might want to send additional data or trigger server-side actions
                // For example, you could send a request to your PHP class endpoint
                var additionalData = {
                    action: 'sample-action-a',
                    // ... other data you want to send ...
                };

                var xhr2 = new XMLHttpRequest();
                xhr2.open('POST', 'your-server-endpoint.php', true);
                xhr2.setRequestHeader('Content-Type', 'application/json');
                xhr2.onload = function() {
                    // Handle the response from the server
                    console.log(xhr2.responseText);
                };
                xhr2.send(JSON.stringify(additionalData));
            } else {
                alert('Vänligen välj en fil för överföring.');
            }
        }
    </script>
</body>

</html>