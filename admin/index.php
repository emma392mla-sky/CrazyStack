<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balance</title>
</head>
<body>

    <div id="balance-data">Loading...</div>

    <script>
        fetch('balance.php')
            .then(response => response.json())
            .then(data => {
                const display = document.getElementById('balance-data');
                
                if (data.status === 'success') {
                    display.innerHTML = 
                        "Environment: " + data.environment + "<br>" +
                        "Currency: " + data.currency + "<br>" +
                        "Main Balance: " + data.main_balance + "<br>" +
                        "Collection Balance: " + data.collection_balance;
                } else {
                    display.innerHTML = "Error: " + data.message;
                }
            })
            .catch(error => {
                document.getElementById('balance-data').innerHTML = "Connection Error: " + error;
            });
    </script>

</body>
</html>