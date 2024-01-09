<?php

/**
 * convert songbook in database to json file
 */


$db = mysqli_connect("localhost", "root", "1234", "tamarack");
if (!$db) {
	die(mysqli_connect_error());
}

$result = mysqli_query($db,"SELECT * FROM songbook");
$array = mysqli_fetch_all($result, MYSQLI_ASSOC);

$file = fopen("songs.json", 'w');
fwrite($file, json_encode($array, JSON_PRETTY_PRINT));
fclose($file);


?>