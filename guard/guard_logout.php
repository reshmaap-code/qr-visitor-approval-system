<?php
session_start();
session_destroy();
header("Location: guard_login.php");
exit();
?>
