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

// Step 1: Get last 30 seconds data
$query = "SELECT topic, message, rssi, timestamp FROM subscribe WHERE timestamp >= (NOW() - INTERVAL 30 SECOND)";
$result = $conn->query($query);

// Initialize data structure
$topics = [];
$specific_data = ["BC5729028A5C", "DD340206C455", "F0F8F2CAD52C"];

// Step 2: Filter and group by specific message values and RSSI
while ($row = $result->fetch_assoc()) {
    $topic = $row['topic'];
    if (!isset($topics[$topic])) {
        $topics[$topic] = array_fill_keys($specific_data, -INF); // Initialize with negative infinity for comparison
    }
    foreach ($specific_data as $data_value) {
        if (strpos($row['message'], $data_value) !== false) {
            $topics[$topic][$data_value] = max($topics[$topic][$data_value], $row['rssi']);
        }
    }
}

// Close the connection
$conn->close();

// Step 3: Determine which tag is nearest to which topic based on the highest RSSI
$nearest_parking = [];
foreach ($specific_data as $tag) {
    $max_rssi = -INF;
    $nearest_topic = '';
    foreach ($topics as $topic => $rssi_values) {
        if ($rssi_values[$tag] > $max_rssi) {
            $max_rssi = $rssi_values[$tag];
            $nearest_topic = $topic;
        }
    }
    if ($nearest_topic) {
        $nearest_parking[$tag] = $nearest_topic;
    }
}

// HTML Layout
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 20px;
            text-align: center;
        }
        .dock {
            background-color: #f0f0f0;
        }
        .parking {
            background-color: #e0f7fa;
        }
        .security-gate {
            background-color: #ffeb3b;
        }
        .vehicle {
            background-color: yellow;
            padding: 5px;
            border-radius: 50%;
            display: inline-block;
            margin: 5px;
        }
        .path {
            background-color: gray;
            height: 100%;
        }
    </style>
</head>
<body>
    <h2>Warehouse Layout</h2>
    <table>
        <tr>
            <th>Dock 1</th>
            <th>Dock 2</th>
            <th>Dock 3</th>
            <!-- Add more docks as needed -->
        </tr>
        <tr>
            <td class="dock">
                <?php if (isset($nearest_parking["BC5729028A5C"]) && $nearest_parking["BC5729028A5C"] == "dock1"): ?>
                    <div class="vehicle">BC5729028A5C</div>
                <?php endif; ?>
                <?php if (isset($nearest_parking["DD340206C455"]) && $nearest_parking["DD340206C455"] == "dock1"): ?>
                    <div class="vehicle">DD340206C455</div>
                <?php endif; ?>
                <?php if (isset($nearest_parking["F0F8F2CAD52C"]) && $nearest_parking["F0F8F2CAD52C"] == "dock1"): ?>
                    <div class="vehicle">F0F8F2CAD52C</div>
                <?php endif; ?>
            </td>
            <td class="dock">
                <?php if (isset($nearest_parking["BC5729028A5C"]) && $nearest_parking["BC5729028A5C"] == "dock2"): ?>
                    <div class="vehicle">BC5729028A5C</div>
                <?php endif; ?>
                <?php if (isset($nearest_parking["DD340206C455"]) && $nearest_parking["DD340206C455"] == "dock2"): ?>
                    <div class="vehicle">DD340206C455</div>
                <?php endif; ?>
                <?php if (isset($nearest_parking["F0F8F2CAD52C"]) && $nearest_parking["F0F8F2CAD52C"] == "dock2"): ?>
                    <div class="vehicle">F0F8F2CAD52C</div>
                <?php endif; ?>
            </td>
            <td class="dock">
                <?php if (isset($nearest_parking["BC5729028A5C"]) && $nearest_parking["BC5729028A5C"] == "dock3"): ?>
                    <div class="vehicle">BC5729028A5C</div>
                <?php endif; ?>
                <?php if (isset($nearest_parking["DD340206C455"]) && $nearest_parking["DD340206C455"] == "dock3"): ?>
                    <div class="vehicle">DD340206C455</div>
                <?php endif; ?>
                <?php if (isset($nearest_parking["F0F8F2CAD52C"]) && $nearest_parking["F0F8F2CAD52C"] == "dock3"): ?>
                    <div class="vehicle">F0F8F2CAD52C</div>
                <?php endif; ?>
            </td>
            <!-- Add more docks as needed -->
        </tr>
        <tr>
            <td colspan="3" class="path">Path</td>
        </tr>
        <tr>
            <th>Parking Zone 1</th>
            <th>Parking Zone 2</th>
            <th>Parking Zone 3</th>
            <!-- Add more parking zones as needed -->
        </tr>
        <tr>
            <td class="parking">
                <?php if (isset($nearest_parking["BC5729028A5C"]) && $nearest_parking["BC5729028A5C"] == "parking1"): ?>
                    <div class="vehicle">BC5729028A5C</div>
                <?php endif; ?>
                <?php if (isset($nearest_parking["DD340206C455"]) && $nearest_parking["DD340206C455"] == "parking1"): ?>
                    <div class="vehicle">DD340206C455</div>
                <?php endif; ?>
                <?php if (isset($nearest_parking["F0F8F2CAD52C"]) && $nearest_parking["F0F8F2CAD52C"] == "parking1"): ?>
                    <div class="vehicle">F0F8F2CAD52C</div>
                <?php endif; ?>
            </td>
            <td class="parking">
                <?php if (isset($nearest_parking["BC5729028A5C"]) && $nearest_parking["BC5729028A5C"] == "parking2"): ?>
                    <div class="vehicle">BC5729028A5C</div>
                <?php endif; ?>
                <?php if (isset($nearest_parking["DD340206C455"]) && $nearest_parking["DD340206C455"] == "parking2"): ?>
                    <div class="vehicle">DD340206C455</div>
                <?php endif; ?>
                <?php if (isset($nearest_parking["F0F8F2CAD52C"]) && $nearest_parking["F0F8F2CAD52C"] == "parking2"): ?>
                    <div class="vehicle">F0F8F2CAD52C</div>
                <?php endif; ?>
            </td>
            <td class="parking">
                <?php if (isset($nearest_parking["BC5729028A5C"]) && $nearest_parking["BC5729028A5C"] == "parking3"): ?>
                    <div class="vehicle">BC5729028A5C</div>
                <?php endif; ?>
                <?php if (isset($nearest_parking["DD340206C455"]) && $nearest_parking["DD340206C455"] == "parking3"): ?>
                    <div class="vehicle">DD340206C455</div>
                <?php endif; ?>
                <?php if (isset($nearest_parking["F0F8F2CAD52C"]) && $nearest_parking["F0F8F2CAD52C"] == "parking3"): ?>
                    <div class="vehicle">F0F8F2CAD52C</div>
                <?php endif; ?>
            </td>
            <!-- Add more parking zones as needed -->
        </tr>
        <tr>
            <td colspan="3" class="path">Path</td>
        </tr>
        <tr>
            <td colspan="3" class="security-gate">Security Gate</td>
        </tr>
    </table>
</body>
</html>
