<?php

require_once('MyChinaLocationShift.php');

$types = array(
    'shift' => ChinaLocationShift::SHIFT,
    'unshift' => ChinaLocationShift::UNSHIFT,
);

header('Content-Type: application/json');

if (empty($_GET['type']) || !array_key_exists($_GET['type'], $types)) {
    echo json_encode(array('success' => false, 'error' => 'Error type'));
} else {
    $type = $types[$_GET['type']];
    if (empty($_GET['latitude']) || empty($_GET['longitude'])) {
        echo json_encode(array('success' => false, 'error' => 'Error latitude or longitude'));
    } else {
        $shift = MyChinaLocationShift::getInstance();
        list($latitude, $longitude) = $shift->translate($type, floatval($_GET['latitude']), floatval($_GET['longitude']));
        echo json_encode(array('success' => true, 'latitude' => $latitude, 'longitude' => $longitude));
    }
}
