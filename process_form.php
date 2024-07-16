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

// Get form data
$truck_number = $_POST['truck_number'];
$dock_number = $_POST['dock_number'];

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO supervisor_cabin (Truck_Number, Dock_Number) VALUES (?, ?)");
$stmt->bind_param("ss", $truck_number, $dock_number);

// Execute the statement
if ($stmt->execute()) {
    echo "New record created successfully";
} else {
    echo "Error: " . $stmt->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();

// Redirect back to the tms.php page
header("Location: tms.php");
exit();
?>
