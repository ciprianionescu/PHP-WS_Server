<?php
error_reporting(E_ERROR);
require("WS_Server/WS_Server.class.php");

$server = new WS_Server("localhost", 8080);
$server->startListening();

?>