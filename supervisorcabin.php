<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Cabin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .form-container h1 {
            margin-bottom: 20px;
            font-size: 24px;
            text-align: center;
        }
        .form-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-container input[type="text"],
        .form-container select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-container input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        .form-container input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            border-radius: 5px;
            text-align: center;
        }
        .modal-content p {
            margin: 0;
            padding: 10px 0;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
        }
        .modal-button {
            background-color: #007BFF;
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin-top: 10px;
            cursor: pointer;
            border-radius: 5px;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="form-container">
        <h1>Supervisor Cabin</h1>
        <form id="dataForm">
            <label for="Truck_Number">Truck Number:</label>
            <select id="Truck_Number" name="Truck_Number" placeholder="Choose truck number" required>
                <option value="">Choose a Truck</option>
                <?php
                // Database connection details
                $db_server = 'localhost';
                $db_username = 'root';
                $db_password = '';
                $db_name = 'hackathon';

                // Create connection
                $conn = new mysqli($db_server, $db_username, $db_password, $db_name);

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Query to select truck numbers
                $query = "SELECT sg.Truck_Number
                        FROM security_gate sg
                        LEFT JOIN supervisor_cabin sc ON sg.Truck_Number = sc.Truck_Number
                        WHERE sc.Truck_Number IS NULL;";
                $result = $conn->query($query);

                // Check if there are results and populate the combo box
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<option value="' . htmlspecialchars($row['Truck_Number']) . '">' . htmlspecialchars($row['Truck_Number']) . '</option>';
                    }
                } else {
                    echo '<option value="">No Trucks Available</option>';
                }

                // Close the connection
                $conn->close();
                ?>
            </select><br><br>

            <label for="dock_no">Dock Number:</label>
            <select id="dock_no" name="Dock_Number" required>
                <option value="D001">D001</option>
                <option value="D002">D002</option>
                <option value="D003">D003</option>
            </select><br>

            <input type="submit" value="Submit">
        </form>
    </div>

    <!-- Modal for displaying message -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <p id="modalMessage"></p>
            <button class="modal-button" onclick="closeModal()">OK</button>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#dataForm').on('submit', function(event) {
                event.preventDefault(); // Prevent the form from submitting the traditional way

                $.ajax({
                    url: 'insertsc.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#modalMessage').html(response); // Display response in modal
                        $('#messageModal').show(); // Show modal
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        $('#modalMessage').text("An error occurred while processing the request.");
                        $('#messageModal').show();
                    }
                });
            });

            // Close the modal when the user clicks the close button or anywhere outside the modal
            $('.close').click(function() {
                $('#messageModal').hide();
            });
            $(window).click(function(event) {
                if (event.target.id == 'messageModal') {
                    $('#messageModal').hide();
                }
            });
        });

        function closeModal() {
            $('#messageModal').hide();
        }
    </script>
</body>
</html>