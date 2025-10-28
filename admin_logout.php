<?php
session_start();
include("db.php"); // safe to keep
session_destroy();
header("Location: admin_login.php");
exit();
