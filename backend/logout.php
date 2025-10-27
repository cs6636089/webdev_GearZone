<?php
session_start();
session_unset();
session_destroy(); 

header("Location: /~cs6636089/GearZone/index.html");
exit;
