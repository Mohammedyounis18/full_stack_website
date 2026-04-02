<?php
session_start();
session_destroy();
header("Location: login.php");
exit; // I added the exit bit to ensure no further code is executed after redirect
?>