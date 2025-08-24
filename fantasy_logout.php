<?php
session_start();
session_unset();
session_destroy();
header('Location: /fantasy_login.php');
exit;