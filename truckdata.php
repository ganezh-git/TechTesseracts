<!DOCTYPE html>
<html>
<head>
    <title>Truck Data</title>
    <script>
        setTimeout(function() {
            location.reload();
        }, 10000);
    </script>
</head>
<body>
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

// Step 1: Get the latest 30 seconds of data from the subscribe table
$query = "SELECT tag, gateway, topic, rssi, timestamp 
          FROM subscribe 
          WHERE timestamp >= (NOW() - INTERVAL 30 SECOND)";
$result = $conn->query($query);

// Initialize data structure
$data = [];

// Step 2 and 3: Process data to count hits and collect RSSI values for each tag by gateway
while ($row = $result->fetch_assoc()) {
    $tag = $row['tag'];
    $gateway = $row['gateway'];
    $topic = $row['topic'];
    $rssi = $row['rssi'];

    if (!isset($data[$tag])) {
        $data[$tag] = [];
    }

    if (!isset($data[$tag][$gateway])) {
        $data[$tag][$gateway] = [
            'count' => 0,
            'rssis' => [],
            'topic' => $topic
        ];
    }

    $data[$tag][$gateway]['count']++;
    $data[$tag][$gateway]['rssis'][] = $rssi;
}

// Step 4: Determine the best gateway for each tag
$processed_data = [];
foreach ($data as $tag => $gateways) {
    $max_count = 0;
    $best_gateways = [];

    // Find the maximum count and identify gateways with that count
    foreach ($gateways as $gateway => $info) {
        if ($info['count'] > $max_count) {
            $max_count = $info['count'];
            $best_gateways = [$gateway];
        } elseif ($info['count'] == $max_count) {
            $best_gateways[] = $gateway;
        }
    }

    // Step 5: Resolve ties by comparing RSSI values
    if (count($best_gateways) > 1) {
        $best_rssi = -INF;
        $selected_gateway = '';
        foreach ($best_gateways as $gateway) {
            $avg_rssi = array_sum($data[$tag][$gateway]['rssis']) / count($data[$tag][$gateway]['rssis']);
            if ($avg_rssi > $best_rssi) {
                $best_rssi = $avg_rssi;
                $selected_gateway = $gateway;
            }
        }
    } else {
        $selected_gateway = $best_gateways[0];
    }

    $processed_data[$tag] = [
        'gateway' => $selected_gateway,
        'topic' => $data[$tag][$selected_gateway]['topic'],
        'count' => $data[$tag][$selected_gateway]['count'],
        'rssi' => max($data[$tag][$selected_gateway]['rssis']) // Best RSSI value for the selected gateway
    ];
}

// Step 6: Insert processed data into the truckdata and truckdata_analysis tables
foreach ($processed_data as $tag => $info) {
    // Find the truck number associated with the tag
    $truck_query = "SELECT Truck_Number 
                    FROM security_gate 
                    WHERE Tag_Number = '$tag' 
                    ORDER BY timestamp DESC 
                    LIMIT 1";
    $truck_result = $conn->query($truck_query);
    $truck_row = $truck_result->fetch_assoc();
    $truck_number = $truck_row ? $truck_row['Truck_Number'] : 'Unknown';

    // Prevent inserting data with unknown truck number
    if ($truck_number !== 'Unknown') {
        // Insert data into truckdata table
        $insert_query = "INSERT INTO truckdata (truck, tag, gateway, location, rssi, count) 
                         VALUES (
                         '$truck_number', 
                         '$tag', 
                         '{$info['gateway']}', 
                         '{$info['topic']}', 
                         '{$info['rssi']}', 
                         '{$info['count']}')";
        if ($conn->query($insert_query) === TRUE) {
            echo "New record created successfully for tag $tag in truckdata table<br>";
        } else {
            echo "Error: " . $insert_query . "<br>" . $conn->error . "<br>";
        }

        // Insert data into truckdata_analysis table
        foreach ($data[$tag] as $gateway => $details) {
            $best_rssi = max($details['rssis']);
            $analysis_insert_query = "INSERT INTO truckdata_analysis (truck, tag, gateway, location, count, rssi) 
                                      VALUES (
                                      '$truck_number', 
                                      '$tag', 
                                      '$gateway', 
                                      '{$details['topic']}', 
                                      '{$details['count']}', 
                                      '$best_rssi')";
            if ($conn->query($analysis_insert_query) === TRUE) {
                echo "New record created successfully for tag $tag in truckdata_analysis table<br>";
            } else {
                echo "Error: " . $analysis_insert_query . "<br>" . $conn->error . "<br>";
            }
        }
    }
}

// Close the connection
$conn->close();
?>
</body>
</html>
