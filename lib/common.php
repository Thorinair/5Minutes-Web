<?php 

    // Setup database connection parameters.
    $dbuser = REDACTED; 
    $dbpass = REDACTED; 
    $dbhost = "localhost"; 
    $dbname = REDACTED; 

    // Enable UTF-8 encoding.
    $dboptions = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'); 
     
    // Attempt to connect to database. 
    try { 
        $db = new PDO("mysql:host={$dbhost};dbname={$dbname};charset=utf8", $dbuser, $dbpass, $dboptions); 
    } 
    catch (PDOException $e) { 
        die("<p class=\"dberror\">Database error. Failed to connect to database.</p>");
    } 
     
    // Setup aditional attributes.
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 
     
    // Disable magic quotes.
    if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) { 
        function undo_magic_quotes_gpc(&$array) { 
            foreach($array as &$value) { 
                if(is_array($value)) { 
                    undo_magic_quotes_gpc($value); 
                } 
                else { 
                    $value = stripslashes($value); 
                } 
            } 
        } 
     
        undo_magic_quotes_gpc($_POST); 
        undo_magic_quotes_gpc($_GET); 
        undo_magic_quotes_gpc($_COOKIE); 
    } 
     
    // Use UTF-8 in browser.
    header('Content-Type: text/html; charset=utf-8'); 
     
    // Start the session.
    session_start(); 

    // Don't close php tag to prevent redirect issues.