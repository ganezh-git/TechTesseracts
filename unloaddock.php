<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unload Truck</title>
</head>
<body>
    <h2>Unload Truck</h2>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="Truck_Number">Select Truck Number:</label>
        <select id="Truck_Number" name="Truck_Number">
            <option value="">Select a Truck Number</option>
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

            // Query to select truck numbers from security_gate
            $query = "SELECT DISTINCT Truck_Number FROM security_gate";
            $result = $conn->query($query);

            // Populate the combo box with truck numbers
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $truck_number = htmlspecialchars($row['Truck_Number']);
                    echo '<option value="' . $truck_number . '">' . $truck_number . '</option>';
                }
            } else {
                echo '<option value="">No Trucks Available</option>';
            }

            // Close the connection
            $conn->close();
            ?>
        </select><br><br>
        <input type="submit" name="submit" value="Unload Dock">
    </form>

    <?php
    // Initialize $truck_number
    $truck_number = '';

    // Check if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['submit'])) {
            // Validate input
            $truck_number = $_POST['Truck_Number'];

            if (!empty($truck_number)) {
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

                // Delete from security_gate table
                $delete_security_gate = "DELETE FROM security_gate WHERE Truck_Number = ?";
                $stmt1 = $conn->prepare($delete_security_gate);
                $stmt1->bind_param("s", $truck_number);

                // Delete from supervisor_cabin table
                $delete_supervisor_cabin = "DELETE FROM supervisor_cabin WHERE Truck_Number = ?";
                $stmt2 = $conn->prepare($delete_supervisor_cabin);
                $stmt2->bind_param("s", $truck_number);

                // Execute deletion queries
                $stmt1->execute();
                $stmt2->execute();

                // Check if both deletions were successful
                $stmt1_success = $stmt1->affected_rows > 0;
                $stmt2_success = $stmt2->affected_rows > 0;

                if ($stmt1_success && $stmt2_success) {
                    echo '<p>Truck Number ' . $truck_number . ' unloaded successfully from both tables.</p>';
                    // Reset $truck_number to empty after successful unload
                    $truck_number = '';
                    $_POST['Truck_Number'] ='';
                   
                } else {
                    // Handle specific errors
                    $stmt1_error = $stmt1->error ? htmlspecialchars($stmt1->error) : '';
                    $stmt2_error = $stmt2->error ? htmlspecialchars($stmt2->error) : '';
                    echo '<p>Error unloading Truck Number ' . $truck_number . ': ';
                    if (!$stmt1_success && !$stmt2_success) {
                        echo 'Both deletions failed. Security Gate: ' . $stmt1_error . ', Supervisor Cabin: ' . $stmt2_error;
                    } elseif (!$stmt1_success) {
                        echo 'Security Gate deletion failed: ' . $stmt1_error;
                    } else {
                        echo 'Supervisor Cabin deletion failed: ' . $stmt2_error;
                    }
                    echo '</p>';
                }
              
                // Close statements
                $stmt1->close();
                $stmt2->close();

                // Close connection
                $conn->close();

                // Auto-refresh the page after 2 seconds
               
            } else {
                echo '<p>Please select a Truck Number.</p>';
            }
        }
    }
    ?>
</body>
</html>