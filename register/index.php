<html>
<head>
    <title>5 Minutes - Register</title>
    <link rel="stylesheet" type="text/css" href="../style/style.css" />
    <meta charset="UTF-8">
</head>
<body>
    <?php 
        require("../lib/common.php"); 
         
        // Check if register has been submitted.
        if (!empty($_POST)) { 

            $process = true;

            // User
            if (empty($_POST['user'])) { 
                $user_error = "*Required"; 
                $process = false;
            } 
            else if (!preg_match('/^[a-zA-Z0-9]{4,24}$/', $_POST['user'])) {
                $user_error = "*Invalid username"; 
                $process = false;
            }
            else {
                // Check if user exists.
                $query = "SELECT 1 FROM users WHERE user = :user"; 
                $query_params = array(':user' => $_POST['user']); 
                 
                try { 
                    $stmt = $db->prepare($query); 
                    $result = $stmt->execute($query_params); 
                } 
                catch(PDOException $e) { 
                    die("<p class=\"dberror\">Database error. Failed to run query.</p>");
                } 
                 
                $row = $stmt->fetch(); 
                if($row) { 
                    $user_error = "*User already exists";
                    $process = false;
                } 
            }

            // Password
            if (empty($_POST['pass'])) { 
                $pass_error = "*Required"; 
                $process = false;
            } 
            else if (strlen($_POST['pass']) < 4) {
                $pass_error = "*Too short";  
                $process = false;
            }

            // Repeat Password
            if (empty($_POST['pass_repeat'])) { 
                $pass_repeat_error = "*Required"; 
                $process = false;
            }
            else if (strlen($_POST['pass_repeat']) < 4) {
                $pass_repeat_error = "*Too short";  
                $process = false;
            }

            // Check if passwords match.
            if ($_POST['pass'] != $_POST['pass_repeat']) {
                $pass_error = "*Passwords don't match"; 
                $pass_repeat_error = "";  
                $process = false;
            }

            // E-Mail
            if (empty($_POST['email'])) { 
                $email_error = "*Required"; 
                $process = false;
            }
            else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) { 
                $email_error = "*Invalid e-mail"; 
                $process = false;
            } 
            else {
                // Check if email exists.
                $query = "SELECT 1 FROM users WHERE email = :email"; 
                $query_params = array(':email' => $_POST['email']); 
                 
                try { 
                    $stmt = $db->prepare($query); 
                    $result = $stmt->execute($query_params); 
                } 
                catch(PDOException $e) { 
                    die("<p class=\"dberror\">Database error. Failed to run query.</p>");
                } 
                 
                $row = $stmt->fetch(); 
                if($row) { 
                    $email_error = "*Already Exists";
                    $process = false;
                } 
            }

            // Name
            if (empty($_POST['firstname'])) { 
                $firstname_error = "*Required"; 
                $process = false;
            } 

            // Surname
            if (empty($_POST['lastname'])) { 
                $lastname_error = "*Required"; 
                $process = false;
            } 
             
            if ($process) { 
                 
                // Hash the password.
                $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT, ['cost' => 11]);
                 
                // Insert user to table. 
                $query = "INSERT INTO users (user, pass, email, firstname, lastname) VALUES (:user, :pass, :email, :firstname, :lastname)"; 
                $query_params = array(':user' => $_POST['user'], ':pass' => $pass, ':email' => $_POST['email'], ':firstname' => $_POST['firstname'], ':lastname' => $_POST['lastname']); 
                 
                try { 
                    $stmt = $db->prepare($query); 
                    $result = $stmt->execute($query_params); 
                } 
                catch(PDOException $e) { 
                    die("<p class=\"dberror\">Database error. Failed to run query.</p>");
                } 
                 
                // Redirect back to login 
                header("Location: .."); 
                die("Redirecting to login.php"); 
            }
        } 
         
    ?> 
    <div class="hex">
        <div class="register">
            <h1>Register</h1>
            <form action="index.php" method="post">
                <table>
                    <tr>
                        <td class="form-left"><p>Username:</p></td>
                        <td class="form-right"><input name="user" class="input" size="12" type="text" value="<?php echo htmlspecialchars($_POST['user'], ENT_HTML5); ?>" /></td>
                        <td><span class="form-error"><?php echo $user_error; ?></span></td>
                    </tr>  
                    <tr>
                        <td class="form-left"><p>Password:</p></td>
                        <td class="form-right"><input name="pass" class="input" size="12" type="password" value="<?php echo htmlspecialchars($_POST['pass'], ENT_HTML5); ?>" /></td>
                        <td><span class="form-error"><?php echo $pass_error; ?></span></td>
                    </tr>  
                    <tr>
                        <td class="form-left"><p>Repeat Pass:</p></td>
                        <td class="form-right"><input name="pass_repeat" class="input" size="12" type="password" value="<?php echo htmlspecialchars($_POST['pass_repeat'], ENT_HTML5); ?>" /></td>
                        <td><span class="form-error"><?php echo $pass_repeat_error; ?></span></td>
                    </tr> 
                    <tr>
                        <td class="form-left"><p>E-Mail:</p></td>
                        <td class="form-right"><input name="email" class="input" size="12" type="text" value="<?php echo htmlspecialchars($_POST['email'], ENT_HTML5); ?>" /></td>
                        <td><span class="form-error"><?php echo $email_error; ?></span></td>
                    </tr>  
                    <tr>
                        <td class="form-left"><p>First Name:</p></td>
                        <td class="form-right"><input name="firstname" class="input" size="12" type="text" value="<?php echo htmlspecialchars($_POST['firstname'], ENT_HTML5); ?>" /></td>
                        <td><span class="form-error"><?php echo $firstname_error; ?></span></td>
                    </tr> 
                    <tr>
                        <td class="form-left"><p>Last Name:</p></td>
                        <td class="form-right"><input name="lastname" class="input" size="12" type="text" value="<?php echo htmlspecialchars($_POST['lastname'], ENT_HTML5); ?>" /></td>
                        <td><span class="form-error"><?php echo $lastname_error; ?></span></td>
                    </tr>  
                </table>
                <input class="button" type="submit" value="Register" />
            </form>
            <p class="belowbutton"><a href="..">Login</a></p>
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