<?php

include_once "lib.php";

session_start();

$username = get_username();
json_data(true, "", $username);
