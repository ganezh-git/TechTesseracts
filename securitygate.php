<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Gate Entry</title>
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
        .message-box {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 4px;
            text-align: center;
        }
        .message-box.success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .message-box.error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Security Gate Entry</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="truck_number">Truck Number:</label>
            <input type="text" id="truck_number" name="Truck_Number" placeholder="Enter truck number" required><br>

            <label for="ble_tag">BLE Tag:</label>
            <select id="ble_tag" name="Tag_Number" required>
                <option value="BC5729028A5C">BC5729028A5C</option>
                <option value="DD340206C455">DD340206C455</option>
                <option value="F0F8F2CAD52C">F0F8F2CAD52C</option>
            </select><br>

            <input type="submit" value="Submit">
        </form>

        <?php
        // Handle form submission
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

            // Prepare SQL statement
            $insert_query = "INSERT INTO security_gate (Truck_Number, Tag_Number) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("ss", $truck_number, $tag_number);

            // Set parameters and execute
            $truck_number = $_POST['Truck_Number'];
            $tag_number = $_POST['Tag_Number'];
            $stmt->execute();

            // Check if insertion was successful
            if ($stmt->affected_rows > 0) {
                echo '<div class="message-box success">';
                echo '<p>Truck No: <strong> '  . $truck_number . ' </strong> mapped to Tag <strong> ' . $tag_number . ' </strong> successfully entered into the security gate.</p>';
                echo '</div>';
            } else {
                echo '<div class="message-box error">';
                echo '<p>Error inserting Truck Number ' . $truck_number . ' with BLE Tag ' . $tag_number . ': ' . $conn->error . '</p>';
                echo '</div>';
            }

            // Close statement and connection
            $stmt->close();
            $conn->close();
        }
        ?>
    </div>
</body>
</html>