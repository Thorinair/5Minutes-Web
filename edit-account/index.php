<html>
<head>
    <title>5 Minutes - Edit Account</title>
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
                $query = "SELECT id FROM users WHERE email = :email"; 
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
                    if ($row['id'] != $_SESSION['session']['id']) {
                        $email_error = "*Already exists";
                        $process = false;
                    }
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
                $query = "UPDATE users SET email = :email, firstname = :firstname, lastname = :lastname WHERE id = :id";
                $query_params = array(':id' => $_SESSION['session']['id'], ':email' => $_POST['email'], ':firstname' => $_POST['firstname'], ':lastname' => $_POST['lastname']);

                try { 
                    $stmt = $db->prepare($query); 
                    $result = $stmt->execute($query_params); 
                } 
                catch(PDOException $e) { 
                    die("<p class=\"dberror\">Database error. Failed to run query.</p>");
                }  

                // Update the session.
                $_SESSION['session']['email'] = $_POST['email']; 
                $_SESSION['session']['firstname'] = $_POST['firstname']; 
                $_SESSION['session']['lastname'] = $_POST['lastname']; 
                 
                header("Location: ../account"); 
            }             
        } 
         
    ?> 
    <div class="hex">
        <div class="edit-account">
            <h1>Edit Account</h1>
            <form action="index.php" method="post">
                <table>
                    <tr>
                        <td class="form-left"><p>E-Mail:</p></td>
                        <td class="form-right"><input name="email" class="input" size="12" type="text" value="<?php if (empty($_POST)) echo $_SESSION['session']['email']; else echo htmlspecialchars($_POST['email'], ENT_HTML5); ?>" /></td>
                        <td><span class="form-error"><?php echo $email_error; ?></span></td>
                    </tr>  
                    <tr>
                        <td class="form-left"><p>First Name:</p></td>
                        <td class="form-right"><input name="firstname" class="input" size="12" type="text" value="<?php if (empty($_POST)) echo $_SESSION['session']['firstname']; else echo htmlspecialchars($_POST['firstname'], ENT_HTML5); ?>" /></td>
                        <td><span class="form-error"><?php echo $firstname_error; ?></span></td>
                    </tr> 
                    <tr>
                        <td class="form-left"><p>Last Name:</p></td>
                        <td class="form-right"><input name="lastname" class="input" size="12" type="text" value="<?php if (empty($_POST)) echo $_SESSION['session']['lastname']; else echo htmlspecialchars($_POST['lastname'], ENT_HTML5); ?>" /></td>
                        <td><span class="form-error"><?php echo $lastname_error; ?></span></td>
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