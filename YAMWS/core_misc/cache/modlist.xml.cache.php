<?php
return array (
"db" => array (
"name" => "db",
"type" => "std",
"file" => "db_loader.php",
"version" => "0.0.1",
"settings" => array (
"Driver" => array (
"name" => "Driver",
"desc" => "Internal db driver name",
"type" => "string",
"value" => "mysql",
),
"DriversFolder" => array (
"name" => "DriversFolder",
"type" => "string",
"desc" => "Path to folder with db drivers",
"value" => "shared/db_drivers",
),
),
),
"error_logger" => array (
"name" => "error_logger",
"file" => "error_logger.php",
"type" => "core",
"version" => "0.0.1",
),
"blog" => array (
"name" => "blog",
"type" => "std",
"file" => "blog.php",
"version" => "0.0.1",
),
);
?>