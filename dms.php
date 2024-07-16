<!DOCTYPE html>
<html>
<head>
    <title>DMS</title>
    <style>
        table {
            width: 80%;
            border-collapse: collapse;
            margin: auto;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
    </style>
    <script>
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</head>
<body>
    <h2 style="text-align: center;">Best Truck Detections by Location</h2>
    <table>
        <thead>
            <tr>
                <th>Location</th>
                <th>Truck</th>
                <th>Tag</th>
                <th>Gateway</th>
                <th>Timestamp</th>
                <th>RSSI</th>
            </tr>
        </thead>
        <tbody>
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

            // Query to get the best detection (highest RSSI) for each truck in the last 30 seconds
            $query = "
                SELECT t1.truck, t1.tag, t1.gateway, t1.location, t1.timestamp, t1.rssi
                FROM truckdata t1
                INNER JOIN (
                    SELECT truck, MAX(rssi) as max_rssi
                    FROM truckdata
                    WHERE timestamp >= (NOW() - INTERVAL 30 SECOND)
                    GROUP BY truck
                ) t2
                ON t1.truck = t2.truck AND t1.rssi = t2.max_rssi
                WHERE t1.timestamp >= (NOW() - INTERVAL 30 SECOND)
                ORDER BY FIELD(t1.location, 'parking1', 'toll', 'dock2'), t1.truck
            ";

            $result = $conn->query($query);

            // Check if there are results
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $location = $row['location'];
                    // Rename locations
                    if ($location == 'parking1') {
                        $location = 'Gateway1';
                    } elseif ($location == 'toll') {
                        $location = 'Gateway2';
                    } elseif ($location == 'dock2') {
                        $location = 'Gateway3';
                    }
                    echo "<tr>
                        <td>" . htmlspecialchars($location) . "</td>
                        <td>" . htmlspecialchars($row['truck']) . "</td>
                        <td>" . htmlspecialchars($row['tag']) . "</td>
                        <td>" . htmlspecialchars($row['gateway']) . "</td>
                        <td>" . htmlspecialchars($row['timestamp']) . "</td>
                        <td>" . htmlspecialchars($row['rssi']) . "</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align: center;'>No data available.</td></tr>";
            }

            // Close the connection
            $conn->close();
            ?>
        </tbody>
    </table>
</body>
</html>
