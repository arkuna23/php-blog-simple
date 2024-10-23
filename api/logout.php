<?php

session_start();

setcookie('session', '', time() - 3600, "/", "", true, true);
