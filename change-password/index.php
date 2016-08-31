<html>
<head>
    <title>5 Minutes - Change Password</title>
    <link rel="stylesheet" type="text/css" href="../style/style.css" />
    <meta charset="UTF-8">
</head>
<body>
    <?php 
        require("../lib/common.php"); 
         
        // Check if user is logged in.
        if(empty($_SESSION['session'])) { 
            header("Location: .."); 
            die("Redirecting to login."); 
        } 
         
        // Check if edit has been submitted.
        if(!empty($_POST)) { 

            $process = true;

            // Prepare query for user's data.
            $query = "SELECT pass FROM users WHERE id = :id";
            $query_params = array(':id' => $_SESSION['session']['id']); 
             
            // Execute the query.
            try { 
                $stmt = $db->prepare($query); 
                $result = $stmt->execute($query_params); 
            } 
            catch(PDOException $e) { 
                die("<p class=\"dberror\">Database error. Failed to run query.</p>");
            } 
             
            // Get user data from database and log in.
            $row = $stmt->fetch(); 
            if($row) {               
                if(!password_verify($_POST['pass_old'], $row['pass'])) {
                    $pass_old_error = "Incorrect password"; 
                    $process = false;
                } 
            } 

            // Remove sensitive data just to be sure.
            unset($row['pass']); 

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

            if ($process) {

                // Hash the password.
                $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT, ['cost' => 11]);

                $query = "UPDATE users SET pass = :pass WHERE id = :id";
                $query_params = array(':id' => $_SESSION['session']['id'], ':pass' => $pass);
                 
                try { 
                    $stmt = $db->prepare($query); 
                    $result = $stmt->execute($query_params); 
                } 
                catch(PDOException $e) { 
                    die("<p class=\"dberror\">Database error. Failed to run query.</p>");
                } 

                header("Location: ../account"); 
            }             
        } 
         
    ?> 
    <div class="hex">
        <div class="edit-account">
            <h1>Change Password</h1>
            <form action="index.php" method="post">
                <table>
                    <tr>
                        <td class="form-left"><p>Old Password:</p></td>
                        <td class="form-right"><input name="pass_old" class="input" size="12" type="password" /></td>
                        <td><span class="form-error"><?php echo $pass_old_error; ?></span></td>
                    </tr>  
                    <tr>
                        <td><br /></td>
                    </tr> 
                    <tr>
                        <td class="form-left"><p>New Password:</p></td>
                        <td class="form-right"><input name="pass" class="input" size="12" type="password" value="<?php echo htmlspecialchars($_POST['pass'], ENT_HTML5); ?>" /></td>
                        <td><span class="form-error"><?php echo $pass_error; ?></span></td>
                    </tr>  
                    <tr>
                        <td class="form-left"><p>Repeat Pass:</p></td>
                        <td class="form-right"><input name="pass_repeat" class="input" size="12" type="password" value="<?php echo htmlspecialchars($_POST['pass_repeat'], ENT_HTML5); ?>" /></td>
                        <td><span class="form-error"><?php echo $pass_repeat_error; ?></span></td>
                    </tr>  
                </table>
                <input class="button" type="submit" value="Save" />
            </form>
            <p class="belowbutton"><a href="../account">Back</a></p>
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