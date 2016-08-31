<html>
<head>
	<title>5 Minutes - Login</title>
	<link rel="stylesheet" type="text/css" href="style/style.css" />
	<meta charset="UTF-8">
</head>
<body>
	<?php 
	    require("lib/common.php"); 
	     
	    // Determine if login has been submitted.
	    if(!empty($_POST)) { 

	        // Prepare query for user's data.
	        $query = "SELECT id, user, pass, email, firstname, lastname FROM users WHERE user = :user";
	        $query_params = array(':user' => $_POST['user']); 
	         
	        // Execute the query.
	        try { 
	            $stmt = $db->prepare($query); 
	            $result = $stmt->execute($query_params); 
	        } 
	        catch(PDOException $e) { 
                die("<p class=\"dberror\">Database error. Failed to run query.</p>");
	        } 
	         
	        // Boolean whether user has logged in successfully.
	        $login_ok = false; 
	         
	        // Get user data from database and log in.
	        $row = $stmt->fetch(); 
	        if($row) { 	             
	            if(password_verify($_POST['pass'], $row['pass'])) {
	                $login_ok = true; 
	            } 
	        } 

            // Remove sensitive data just to be sure.
            unset($row['pass']); 
	         
	        // If successful, redirect to private page.
	        if($login_ok) { 
	            // Use this to check if user is still logged in.
	            $_SESSION['session'] = $row; 
	             
	            // Redirect the user. 
	            header("Location: account"); 
	        } 
	        else { 
                $user_error = "Incorrect password or username.";
	        } 
	    } 
	     
	?> 
	<div class="hex">
		<div class="login">
			<h1>Login</h1>
			<form action="index.php" method="post">
				<span class="form-error"><?php echo $user_error; ?></span>
                <table>
                    <tr>
                        <td class="form-left"><p>Username:</p></td>
                        <td class="form-right"><input name="user" class="input" size="12" type="text" value="<?php echo htmlspecialchars($_POST['user'], ENT_HTML5); ?>" /></td>
                        <td></td>
                    </tr>  
                    <tr>
                        <td class="form-left"><p>Password:</p></td>
                        <td class="form-right"><input name="pass" class="input" size="12" type="password" /></td>
                    </tr> 
                </table>
				<input class="button" type="submit" value="Login" />
			</form>
			<p class="belowbutton"><a href="register">Register</a></p>
		</div>
		<div class="footer-about">
			<p><a href="about">About Us</a></p>
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