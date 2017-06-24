<?php
	$mysqli = new mysqli('localhost', 'jhy753', '1q2w3e4r5t', 'jhy753');
	$mysqli -> query('INSERT INTO gps(latitude, longitude) VALUE('.$_GET['latitude'].', '.$_GET['longitude'].')');
?>