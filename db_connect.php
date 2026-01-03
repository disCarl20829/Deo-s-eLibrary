<?php
$host = "localhost";
$dbuser = "root";
$dbpass = "123456";
$db = "elibrary";

$const = new mysqli($host, $dbuser, $dbpass, $db);
if ($const->connect_error) {
    die("Connection failed: " . $const->connect_error);
}
?>