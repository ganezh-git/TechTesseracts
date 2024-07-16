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

// Query to get the most recent truck detection for the toll location
$query = "SELECT truck FROM truckdata WHERE location = 'toll' ORDER BY timestamp DESC LIMIT 1";
$result = $conn->query($query);
$latest_toll_truck = $result->num_rows > 0 ? htmlspecialchars($result->fetch_assoc()['truck']) : '';

// Query to get truck numbers from supervisor_cabin
$supervisor_trucks = [];
$query = "SELECT Truck_Number FROM supervisor_cabin";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $supervisor_trucks[] = htmlspecialchars($row['Truck_Number']);
    }
}

// Query to get the data from truckdata table
$query = "SELECT * FROM truckdata";
$result = $conn->query($query);

// Initialize data structures
$docks = array_fill(1, 10, '');
$parking = array_fill(1, 10, '');

// Populate data structures with truck numbers
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (strpos($row['location'], 'dock') !== false) {
            $dock_number = (int) filter_var($row['location'], FILTER_SANITIZE_NUMBER_INT);
            $docks[$dock_number] = htmlspecialchars($row['truck']);
        } elseif (strpos($row['location'], 'parking') !== false) {
            $parking_number = (int) filter_var($row['location'], FILTER_SANITIZE_NUMBER_INT);
            $parking[$parking_number] = htmlspecialchars($row['truck']);
        }
    }
}

// Determine if the latest truck at the toll is in the supervisor_cabin table
$toll_class = in_array($latest_toll_truck, $supervisor_trucks) ? 'toll supervisor' : 'toll';

// Close the connection
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Warehouse Layout</title>
    <style>
        table {
            width: 90%;
            border-collapse: collapse;
            margin: auto;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
            width: 80px;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        .dock {
            background-color: #e0f7fa;
            position: relative;
        }
        .parking {
            background-color: #ffeb3b;
            position: relative;
        }
        .supervisor-cabin {
            background-color: #ffebee;
        }
        .path {
            background-color: gray;
            height: 20px;
        }
        .security-gate {
            background-color: #d1c4e9;
        }
        .truck-number {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: brown;
            color: white;
            padding: 5px;
            border-radius: 5px;
            font-weight: bold;
            z-index: 1;
        }
        .truck-number img {
            width: 50px; /* Adjust size as needed */
            display: block;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        .toll {
            background-color: orange;
        }
        .toll.supervisor {
            background-color: green;
        }
    </style>
</head>
<body>
    <table>
        <!-- Header Row -->
        <tr>
            <td rowspan=2>
                <b>Supervisor Cabin</b>
                <form id="dataForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <table>
                        <tr>
                            <td><label for="Truck_Number">Truck Number:</label></td>
                            <td>
                                <select id="Truck_Number" name="Truck_Number" placeholder="Choose truck number" required>
                                    <option value="">Choose a Truck</option>
                                    <?php
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
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Dock Number:</td>
                            <td>
                                <select id="dock_no" name="Dock_Number" required>
                                    <option value="D001">D001</option>
                                    <option value="D002">D002</option>
                                    <option value="D003">D003</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan=2><input type="submit" value="Submit"></td>
                        </tr>
                    </table>
                </form>
            </td>
            <th colspan="12">Warehouse</th>
        </tr>

        <!-- Docks Row -->
        <tr>
            <?php for ($i = 1; $i <= 10; $i++): ?>
                <td class="dock" style="height: 80px; vertical-align: top; position: relative;">
                    Dock <?php echo $i; ?><br>
                    <?php if (!empty($docks[$i])): ?>
                        <div class="truck-number">
                            <img src="truck.png" alt="Truck" />
                            <?php echo $docks[$i]; ?>
                        </div>
                    <?php endif; ?>
                </td>
            <?php endfor; ?>
        </tr>

        <!-- Pathway Row -->
        <tr>
            <td colspan="1" class="path"></td>    
            <td colspan="10" class="path"></td>
        </tr>
        <tr>
            <td colspan="1" class="path">
                <table>
                    <tr>
                        <td>Trucks No.: 

                <?php if (!empty($latest_toll_truck)): ?>
                        <?php echo $latest_toll_truck; ?>
                    
                <?php endif; ?>



                        <td style="width:80px;">Take Next Right</td>
                    </tr>
                </table>
            </td>    
            <td colspan="10"></td>
        </tr>
        <tr>
            <td colspan="1" class="<?php echo $toll_class; ?>" style="height: 5px; vertical-align: top; position: relative;">Toll</td>    
            <td colspan="10"></td>
        </tr>
        <!-- Toll Row -->
        <tr>
            <td class="path" style="height: 80px; vertical-align: top; position: relative;">
                <?php if (!empty($latest_toll_truck)): ?>
                    <div class="truck-number"> 
                        <img src="truck.png" alt="Truck" />
                        <?php echo $latest_toll_truck; ?>
                    </div>
                <?php endif; ?>
                Toll
            </td>
            <td colspan="10"></td>
        </tr>
        <tr>
            <td colspan="1" class="path">&nbsp;</td>    
            <td colspan="10"></td>
        </tr>
        <!-- Pathway Row -->
        <tr>
            <td colspan="11" class="path"></td>
        </tr>

        <!-- Parking Row -->
        <tr>

        <td colspan=10><table style="width:100%"><tr>
            <?php for ($i = 1; $i <= 10; $i++): ?>
                <td class="parking" style="height: 80px; vertical-align: bottom; position: relative;">
                    <?php if (!empty($parking[$i])): ?>
                        <div class="truck-number">
                            <img src="truck.png" alt="Truck" />
                            <?php echo $parking[$i]; ?>
                        </div>
                    <?php endif; ?>
                    Parking <?php echo $i; ?>
                </td>
            <?php endfor; ?>
                    </tr></table>
            <td class="path"></td>
        </tr>

<!-- Security Gate Row -->
<tr>
            <td colspan="7"></td>
            <td colspan="3" class="security-gate" style="height: 80px;">
                <font size=4><b>Security Gate Entry</b></font>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <table>
                        <tr>
                            <td style="padding: 0; text-align: left;"><label for="truck_number">Truck Number:</label></td>
                            <td style="padding: 0; text-align: left;"><input type="text" id="truck_number" name="Truck_Number" placeholder="Enter truck number" required></td>
                        </tr>
                        <tr>
                            <td style="padding: 0; text-align: left;">
                                <label for="ble_tag">BLE Tag:</label>
                            </td>
                            <td style="padding: 0; text-align: left;">
                                <select id="ble_tag" name="Tag_Number" required>
                                    <option value="BC5729028A5C">BC5729028A5C</option>
                                    <option value="DD340206C455">DD340206C455</option>
                                    <option value="F0F8F2CAD52C">F0F8F2CAD52C</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <input type="submit" value="Submit">
                </form>
                <?php
                // Handle form submission
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
                        echo '<p>Truck No: <strong> ' . $truck_number . ' </strong> mapped to Tag <strong> ' . $tag_number . ' </strong> successfully entered into the security gate.</p>';
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
            </td>
            <td class="path"><img src=truck.png height=400% width=100%></td>
        </tr> 
    </table>
</body>
</html>