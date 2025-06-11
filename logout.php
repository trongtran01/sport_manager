<?php
session_start();
session_destroy();
header("Location: /Sport_Manager/index.php");
exit;
