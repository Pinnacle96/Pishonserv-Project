<?php
session_start();
session_unset();
session_destroy();
header("Location: /pishonserv.com/auth/login.php");
exit();
