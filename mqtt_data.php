<?php
require_once('vendor/autoload.php'); // Adjust path if necessary

use Bluerhinos\phpMQTT;

// MQTT broker settings
$mqtt_server = 'localhost';  // MQTT broker address
$mqtt_port = 1883;           // MQTT broker port
$mqtt_topic = 'test';        // MQTT topic to subscribe to

// MQTT client settings
$mqtt_client_id = 'phpMQTT-subscriber-' . uniqid(); // Unique client ID
$mqtt_timeout = 5;           // Timeout in seconds

// MQTT connection
$mqtt = new phpMQTT($mqtt_server, $mqtt_port, $mqtt_client_id);

if ($mqtt->connect(true, NULL, NULL, NULL)) {
    $topics[$mqtt_topic] = array("qos" => 0, "function" => "procmsg");
    $mqtt->subscribe($topics, 0);

    while ($mqtt->proc()) {
        // Loop until no more messages are available or timeout occurs
    }

    $mqtt->close();
} else {
    echo "Failed to connect to MQTT broker!";
}

function procmsg($topic, $msg) {
    echo "Received message on topic: $topic - Message: $msg<br>";
}
?>
