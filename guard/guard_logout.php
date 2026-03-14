<?php
session_start();

// Destroy all session data
session_destroy();

// Redirect to guard login page
header("Location: guard_login.php");
exit();
?>
