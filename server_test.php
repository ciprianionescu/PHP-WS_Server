<?php
error_reporting(E_ERROR);
require("WS_Server/WS_Server.class.php");

$server = new WS_Server("192.168.0.159", 8080);
$server->startListening();

?>