<html>
<head>
    <title>5 Minutes - Your Account</title>
    <link rel="stylesheet" type="text/css" href="../style/style.css" />
	<meta charset="UTF-8">
</head>
<body>
	<?php 
	    require("../lib/common.php");
	    require("../vendor/autoload.php");
	     
	    // Check if user is logged in.
	    if(empty($_SESSION['session'])) { 
	        header("Location: .."); 
	        die("Redirecting to login."); 
	    } 

	    // Check if one-time has been submitted.
	    if(!empty($_POST)) { 
	    	$factory = new RandomLib\Factory;
			$generator = $factory->getLowStrengthGenerator();
			$generated = $generator->generateInt(0, 99999999);

		    $code = str_pad($generated, 8, '0', STR_PAD_LEFT);


		    $query = "SELECT code FROM codes WHERE userid = :id"; 
            $query_params = array(':id' => $_SESSION['session']['id']); 
             
            $regenerate = false; 
            try { 
                $stmt = $db->prepare($query); 
                $result = $stmt->execute($query_params); 
            } 
            catch(PDOException $e) {  
                die("<p class=\"dberror\">Database error. Failed to run query.</p>");
            }
            $row = $stmt->fetch(); 
            if($row) { 
            	$regenerate = true; 
            }

            if ($regenerate) {
        		$query = "UPDATE codes SET code = :code WHERE userid = :userid";
            	$query_params = array(':userid' => $_SESSION['session']['id'], ':code' => $code); 

				try { 
	                $stmt = $db->prepare($query); 
	                $result = $stmt->execute($query_params); 
	            } 
	            catch(PDOException $e) {  
	                die("<p class=\"dberror\">Database error. Failed to run query.</p>");
	            }
            }
            else {
                $query = "INSERT INTO codes (userid, code) VALUES (:userid, :code)"; 
                $query_params = array(':userid' => $_SESSION['session']['id'], ':code' => $code); 

	            try { 
	                $stmt = $db->prepare($query); 
	                $result = $stmt->execute($query_params); 
	            } 
	            catch(PDOException $e) {  
	                die("<p class=\"dberror\">Database error. Failed to run query.</p>");
	            } 
            }

    		$query = "UPDATE users SET session = :session, push = :push WHERE id = :id";
        	$query_params = array(':id' => $_SESSION['session']['id'], ':session' => null, ':push' => null); 

			try { 
                $stmt = $db->prepare($query); 
                $result = $stmt->execute($query_params); 
            } 
            catch(PDOException $e) {  
                die("<p class=\"dberror\">Database error. Failed to run query.</p>");
            }


            // Redirect the user. 
            header("Location: ../account"); 
	    }
	    else {
	    	$query = "SELECT code FROM codes WHERE userid = :id"; 
            $query_params = array(':id' => $_SESSION['session']['id']); 
             
            try { 
                $stmt = $db->prepare($query); 
                $result = $stmt->execute($query_params); 
            } 
            catch(PDOException $e) {  
                die("<p class=\"dberror\">Database error. Failed to run query.</p>");
            } 
             
            $row = $stmt->fetch(); 
            if($row) { 
	        	$code = str_pad($row['code'], 8, '0', STR_PAD_LEFT); 
            }
	    }
	?> 
	<div class="hex">
        <div class="account">
            <h1>Your Account</h1>
            <table>
                <tr>
                    <td class="details-left"><p>ID:</p></td>
                    <td class="details-right"><p id="id"><?php echo str_pad($_SESSION['session']['id'], 8, '0', STR_PAD_LEFT); ?></p></td>
                </tr> 
                <tr>
                    <td class="details-left"><p>Username:</p></td>
                    <td class="details-right"><p><?php echo $_SESSION['session']['user']; ?></p></td>
                </tr>  
                <tr>
                    <td class="details-left"><p>E-Mail:</p></td>
                    <td class="details-right"><p><?php echo $_SESSION['session']['email']; ?></p></td>
                </tr>  
                <tr>
                    <td class="details-left"><p>First Name:</p></td>
                    <td class="details-right"><p><?php echo $_SESSION['session']['firstname']; ?></p></td>
                </tr> 
                <tr>
                    <td class="details-left"><p>Last Name:</p></td>
                    <td class="details-right"><p><?php echo $_SESSION['session']['lastname']; ?></p></td>
                </tr>  
            </table>
            <p class="belowlinks"><a href="../edit-account">Edit Account</a> &sdot; <a href="../change-password">Change Password</a> &sdot; <a href="../logout">Logout</a></p>
            <p>To use your smartwatch application,<br />generate a one-time password below.</p>
            <form id="generate" action="index.php" method="post">
                <input class="button" name="generate" type="submit" value="Generate" />
            </form>
	        <table id="generated" cellspacing="0">
	            <tr>
	                <td class="code-left"><p><?php if ($code != "") echo "Code:"; ?></p></td>
	                <td class="code-right"><p><?php echo $code; ?></p></td>
	            </tr>  
	        </table>
        </div>
		<div class="footer-about">
			<p><a href="../about">About Us</a></p>
		</div>
    </div>
    <div class="footer">
        <p>Copyright &copy;2016, 5Minutes</p>
    </div>
    <div class="bgcopy">
        <p>Background image modified from original by <a href="https://www.flickr.com/photos/janitors/21027308089/in/photostream/">Kārlis Dambrāns</a></p>
    </div>
</body>
</html>