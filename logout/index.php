<?php 
    require("../lib/common.php"); 
     
    // Remove user data from session.
    unset($_SESSION['session']); 
     
    header("Location: .."); 
    die("Redirecting to login."); 