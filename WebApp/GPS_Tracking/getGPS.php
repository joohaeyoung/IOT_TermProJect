<?php

header("Content-Type: text/html; charset=UTF-8");

$host = "mysql:host=localhost;dbname=jhy753";
$user = "jhy753";
$password= "1q2w3e4r5t";
$conn = new PDO($host, $user, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));;

$stmt = $conn->prepare('SELECT * FROM gps');
$stmt->execute();
$list = $stmt->fetchAll();

foreach($list as $gps){
	$data->latitude = $gps['latitude'];
	$data->longitude = $gps['longitude'];
}

echo json_encode($data);
?>