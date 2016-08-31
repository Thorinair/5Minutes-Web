<?php 
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Max-Age: 86400");
    if (strtolower($_SERVER['REQUEST_METHOD']) == 'options') {
        exit();
    }

    require("../lib/common.php");
    require("../vendor/autoload.php");

    $push_appID = REDACTED;
    $push_appSecret = REDACTED;


    function output($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        die("");
    } 

    function dberror() {
        $out = [ "response" => "db_error", ];
        output($out);
    }  

    function getServer($regID) {
        $id = substr($regID, 0, 2);

        switch ($id) {
            case "00": return "https://useast.push.samsungosp.com:8090/spp/pns/api/push"; break;
            case "02": return "https://apsoutheast.push.samsungosp.com:8090/spp/pns/api/push"; break;
            case "03": return "https://euwest.push.samsungosp.com:8090/spp/pns/api/push"; break;
            case "04": return "https://apnortheast.push.samsungosp.com:8090/spp/pns/api/push"; break;
            case "05": return "https://apkorea.push.samsungosp.com:8090/spp/pns/api/push"; break;
            case "06": return "https://apchina.push.samsungosp.com.cn:8090/spp/pns/api/push"; break;
            case "50": return "https://useast.gateway.push.samsungosp.com:8090/spp/pns/api/push"; break;
            case "52": return "https://apsoutheast.gateway.push.samsungosp.com:8090/spp/pns/api/push"; break;
            case "53": return "https://euwest.gateway.push.samsungosp.com:8090/spp/pns/api/push"; break;
            case "54": return "https://apnortheast.gateway.push.samsungosp.com:8090/spp/pns/api/push"; break;
            case "55": return "https://apkorea.gateway.push.samsungosp.com:8090/spp/pns/api/push"; break;
            case "56": return "https://apchina.gateway.push.samsungosp.com.cn:8090/spp/pns/api/push"; break;
        }
    }
     
    // Check if edit has been submitted.
    if(!empty($_POST)) { 

        /*
        * onetime
        * responses:
        *   onetime_okay - Onetime code accepted.
        *   onetime_incorrect - Onetime code is incorrect.
        */
        if ($_POST['request'] == "onetime") {
            $query = "SELECT id, userid FROM codes WHERE code = :code"; 
            $query_params = array(':code' => $_POST['code']); 
             
            try { 
                $stmt = $db->prepare($query);
                $result = $stmt->execute($query_params); 
            } 
            catch(PDOException $e) { 
                    $e->getMessage();
                dberror();
            } 
             
            $row = $stmt->fetch(); 
            if($row) { 
                $factory = new RandomLib\Factory;
                $generator = $factory->getLowStrengthGenerator();
                $generated = $generator->generateString(32, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

                $session = password_hash($generated, PASSWORD_DEFAULT, ['cost' => 11]);

                $query = "UPDATE users SET session = :session WHERE id = :id";
                $query_params = array(':id' => $row['userid'], ':session' => $session); 

                try { 
                    $stmt = $db->prepare($query); 
                    $result = $stmt->execute($query_params); 
                } 
                catch(PDOException $e) { 
                    dberror();
                }

                $query = "DELETE FROM codes WHERE id = :id";
                $query_params = array(':id' => $row['id']); 

                try { 
                    $stmt = $db->prepare($query); 
                    $result = $stmt->execute($query_params); 
                } 
                catch(PDOException $e) {  
                    dberror();
                }

                $out = [ 
                    "response" => "onetime_okay",
                    "user" => $row['userid'],
                    "pass" => $generated,
                    ];
                output($out);
            }
            else {
                $out = [ "response" => "onetime_incorrect", ];
                output($out);
            }
        }

        /*
        * update_push
        * responses:
        *   update_push_okay - Push ID successfully updated.
        *   update_push_expired - Session has expired.
        *   update_push_error - Generic error.
        */
        elseif ($_POST['request'] == "update_push") {
            $query = "SELECT session FROM users WHERE id = :id";
            $query_params = array(':id' => $_POST['user']); 
             
            try { 
                $stmt = $db->prepare($query); 
                $result = $stmt->execute($query_params); 
            } 
            catch(PDOException $e) { 
                dberror();
            } 
             
            $row = $stmt->fetch(); 
            if($row) {               
                if(password_verify($_POST['pass'], $row['session'])) {
                    unset($row['session']); 

                    // Do processing here.
                    $query = "UPDATE users SET push = :push WHERE id = :id";
                    $query_params = array(':id' => $_POST['user'], ':push' => $_POST['push']); 

                    try { 
                        $stmt = $db->prepare($query); 
                        $result = $stmt->execute($query_params); 
                    } 
                    catch(PDOException $e) { 
                        dberror();
                    }

                    $out = [ "response" => "update_push_okay", ];
                    output($out);
                } 
                else {
                    unset($row['session']); 

                    $out = [ "response" => "update_push_expired", ];
                    output($out);
                }
            }

            unset($row['session']); 

            $out = [ "response" => "update_push_error", ];
            output($out);
        }

        /*
        * contact_get_list
        * responses:
        *   contact_get_list_okay - Contact retrieval successful.
        *   contact_get_list_expired - Session has expired.
        *   contact_get_list_error - Generic error.
        */
        elseif ($_POST['request'] == "contact_get_list") {

            $query = "SELECT session, contacts FROM users WHERE id = :id";
            $query_params = array(':id' => $_POST['user']); 
             
            try { 
                $stmt = $db->prepare($query); 
                $result = $stmt->execute($query_params); 
            } 
            catch(PDOException $e) { 
                dberror();
            } 
             
            $row = $stmt->fetch(); 
            if($row) {               
                if(password_verify($_POST['pass'], $row['session'])) {
                    unset($row['session']);

                    // Do processing here.
                    $contactids = unserialize($row['contacts']);
                    $contacts = [];

                    foreach ($contactids as $contactid) {
                        $query = "SELECT firstname, lastname FROM users WHERE id = :id";
                        $query_params = array(':id' => $contactid); 
                         
                        try {
                            $stmt = $db->prepare($query); 
                            $result = $stmt->execute($query_params); 
                        } 
                        catch(PDOException $e) { 
                            dberror();
                        }

                        $row = $stmt->fetch(); 
                        if($row) {
                            $contact = [
                                "id" => $contactid,
                                "firstname" => $row['firstname'],
                                "lastname" => $row['lastname'],
                                ];

                            $contacts[] = $contact;  
                        }
                        else {
                            $out = [ "response" => "contact_get_list_error", ];
                            output($out);                            
                        }
                    }

                    $out = [ 
                        "response" => "contact_get_list_okay", 
                        "contacts" => $contacts, 
                        ];
                    output($out);
                } 
                else {
                    unset($row['session']); 

                    $out = [ "response" => "contact_get_list_expired", ];
                    output($out);
                }
            }

            unset($row['session']); 

            $out = [ "response" => "contact_get_list_error", ];
            output($out);
        }

        /*
        * contact_request
        * responses:
        *   contact_request_okay - Contact request successful.
        *   contact_request_self - Contact requested is the requester.
        *   contact_request_miss - Contact requested doesn't exist.
        *   contact_request_already - Contacts already added.
        *   contact_request_expired - Session has expired.
        *   contact_request_error - Generic error.
        */
        elseif ($_POST['request'] == "contact_request") {
            
            if ($_POST['user'] == $_POST['contact']) {
                $out = [ "response" => "contact_request_self", ];
                output($out);
            }

            $query = "SELECT session, firstname, lastname FROM users WHERE id = :id";
            $query_params = array(':id' => $_POST['user']); 
             
            try { 
                $stmt = $db->prepare($query); 
                $result = $stmt->execute($query_params); 
            } 
            catch(PDOException $e) { 
                dberror();
            } 
             
            $row = $stmt->fetch(); 
            if($row) {               
                if(password_verify($_POST['pass'], $row['session'])) {
                    unset($row['session']);

                    // Do processing here.
                    $firstname = $row['firstname'];
                    $lastname = $row['lastname'];

                    $query = "SELECT contacts, push FROM users WHERE id = :id";
                    $query_params = array(':id' => $_POST['contact']);
             
                    try { 
                        $stmt = $db->prepare($query); 
                        $result = $stmt->execute($query_params); 
                    } 
                    catch(PDOException $e) { 
                        dberror();
                    } 

                    $row = $stmt->fetch(); 
                    if(!$row) {  
                        $out = [ "response" => "contact_request_miss", ];
                        output($out);
                    }
                    else {
                        $contacts = unserialize($row['contacts']);
                        if (in_array($_POST['user'], $contacts)) {
                            $out = [ "response" => "contact_request_already", ];
                            output($out);
                        }
                        else {
                            $factory = new RandomLib\Factory;
                            $generator = $factory->getLowStrengthGenerator();
                            $generated = $generator->generateString(16, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

                            $url = getServer($row['push']); 
                            $body = json_encode(array(
                                "regID" => $row['push'],
                                "requestID" => $generated,
                                "message" => "badgeOption=ALERT&badgeNumber=1&action=LAUNCH&alertMessage=" . urlencode($firstname . " " . $lastname . " wants to add you to contacts."),
                                "appData" => json_encode(array(
                                        "type" => "contact_request",
                                        "contact" => $_POST['user'],
                                        "firstname" => $firstname,
                                        "lastname" => $lastname
                                    ))
                            ));  

                            $curl = curl_init($url);
                            curl_setopt($curl, CURLOPT_HEADER, false);
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($curl, CURLOPT_HTTPHEADER,
                                    array(
                                        "Content-type: application/json",
                                        "appID: " . $push_appID,
                                        "appSecret: " . $push_appSecret
                                        )
                                    );
                            curl_setopt($curl, CURLOPT_POST, true);
                            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

                            curl_exec($curl);

                            $out = [ "response" => "contact_request_okay", ];
                            output($out);
                        }
                    }
                }
                else {
                    unset($row['session']); 

                    $out = [ "response" => "contact_request_expired", ];
                    output($out);
                } 
            }

            unset($row['session']); 

            $out = [ "response" => "contact_request_error", ];
            output($out);
        }

        /*
        * contact_reject
        * responses:
        *   contact_reject_okay - Contact reject successful.
        *   contact_reject_expired - Session has expired.
        *   contact_reject_error - Generic error.
        */
        elseif ($_POST['request'] == "contact_reject") {
            $query = "SELECT session, firstname, lastname FROM users WHERE id = :id";
            $query_params = array(':id' => $_POST['user']); 
             
            try { 
                $stmt = $db->prepare($query); 
                $result = $stmt->execute($query_params); 
            } 
            catch(PDOException $e) { 
                dberror();
            } 
             
            $row = $stmt->fetch(); 
            if($row) {               
                if(password_verify($_POST['pass'], $row['session'])) {
                    unset($row['session']);

                    // Do processing here.
                    $firstname = $row['firstname'];
                    $lastname = $row['lastname'];

                    $query = "SELECT contacts, push FROM users WHERE id = :id";
                    $query_params = array(':id' => $_POST['contact']);
             
                    try { 
                        $stmt = $db->prepare($query); 
                        $result = $stmt->execute($query_params); 
                    } 
                    catch(PDOException $e) { 
                        dberror();
                    } 

                    $row = $stmt->fetch(); 
                    if($row) {
                        $factory = new RandomLib\Factory;
                        $generator = $factory->getLowStrengthGenerator();
                        $generated = $generator->generateString(16, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

                        $url = getServer($row['push']); 
                        $body = json_encode(array(
                            "regID" => $row['push'],
                            "requestID" => $generated,
                            "message" => "badgeOption=ALERT&badgeNumber=1&action=LAUNCH&alertMessage=" . urlencode($firstname . " " . $lastname . " rejected your contact request."),
                            "appData" => json_encode(array(
                                    "type" => "contact_reject",
                                    "firstname" => $firstname,
                                    "lastname" => $lastname
                                ))
                        ));  

                        $curl = curl_init($url);
                        curl_setopt($curl, CURLOPT_HEADER, false);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_HTTPHEADER,
                                array(
                                    "Content-type: application/json",
                                    "appID: " . $push_appID,
                                    "appSecret: " . $push_appSecret
                                    )
                                );
                        curl_setopt($curl, CURLOPT_POST, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

                        curl_exec($curl);

                        $out = [ "response" => "contact_reject_okay", ];
                        output($out);
                    }
                }
                else {
                    unset($row['session']); 

                    $out = [ "response" => "contact_reject_expired", ];
                    output($out);
                } 
            }

            unset($row['session']); 

            $out = [ "response" => "contact_reject_error", ];
            output($out);
        }

        /*
        * contact_accept
        * responses:
        *   contact_accept_okay - Contacts successfully added.
        *   contact_accept_already - Contacts already added.
        *   contact_accept_expired - Session has expired.
        *   contact_accept_error - Generic error.
        */
        elseif ($_POST['request'] == "contact_accept") {
            $query = "SELECT session, firstname, lastname FROM users WHERE id = :id";
            $query_params = array(':id' => $_POST['user']); 
             
            try { 
                $stmt = $db->prepare($query); 
                $result = $stmt->execute($query_params); 
            } 
            catch(PDOException $e) { 
                dberror();
            } 
             
            $row = $stmt->fetch(); 
            if($row) {               
                if(password_verify($_POST['pass'], $row['session'])) {
                    unset($row['session']);

                    // Do processing here.
                    $firstname = $row['firstname'];
                    $lastname = $row['lastname'];

                    $query = "SELECT contacts FROM users WHERE id = :id";
                    $query_params = array(':id' => $_POST['contact']);
             
                    try { 
                        $stmt = $db->prepare($query); 
                        $result = $stmt->execute($query_params); 
                    } 
                    catch(PDOException $e) { 
                        dberror();
                    } 

                    $row = $stmt->fetch(); 
                    if($row) {  
                        $contacts = unserialize($row['contacts']);
                        if (in_array($_POST['user'], $contacts)) {
                            $out = [ "response" => "contact_accept_already", ];
                            output($out);
                        }
                    }

                    $query = "SELECT contacts FROM users WHERE id = :id";
                    $query_params = array(':id' => $_POST['user']); 
                     
                    try { 
                        $stmt = $db->prepare($query); 
                        $result = $stmt->execute($query_params); 
                    } 
                    catch(PDOException $e) { 
                        dberror();
                    } 
                     
                    $row = $stmt->fetch(); 
                    if ($row) {
                        $contacts = unserialize($row['contacts']);
                        $contacts[] = $_POST['contact'];

                        $query = "UPDATE users SET contacts = :contacts WHERE id = :id";
                        $query_params = array(':id' => $_POST['user'], ':contacts' => serialize($contacts));

                        try { 
                            $stmt = $db->prepare($query); 
                            $result = $stmt->execute($query_params); 
                        } 
                        catch(PDOException $e) { 
                            dberror();
                        }

                        $query = "SELECT contacts, push FROM users WHERE id = :id";
                        $query_params = array(':id' => $_POST['contact']);
                 
                        try { 
                            $stmt = $db->prepare($query); 
                            $result = $stmt->execute($query_params); 
                        } 
                        catch(PDOException $e) { 
                            dberror();
                        } 

                        $row = $stmt->fetch(); 
                        if($row) {   
                            $contacts = unserialize($row['contacts']);
                            $contacts[] = $_POST['user'];

                            $query = "UPDATE users SET contacts = :contacts WHERE id = :id";
                            $query_params = array(':id' => $_POST['contact'], ':contacts' => serialize($contacts));
                 
                            try { 
                                $stmt = $db->prepare($query); 
                                $result = $stmt->execute($query_params); 
                            } 
                            catch(PDOException $e) { 
                                dberror();
                            } 

                            $factory = new RandomLib\Factory;
                            $generator = $factory->getLowStrengthGenerator();
                            $generated = $generator->generateString(16, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

                            $url = getServer($row['push']); 
                            $body = json_encode(array(
                                "regID" => $row['push'],
                                "requestID" => $generated,
                                "message" => "badgeOption=ALERT&badgeNumber=1&action=LAUNCH&alertMessage=" . urlencode($firstname . " " . $lastname . " accepted your contact request."),
                                "appData" => json_encode(array(
                                        "type" => "contact_accept",
                                        "firstname" => $firstname,
                                        "lastname" => $lastname
                                    ))
                            ));  

                            $curl = curl_init($url);
                            curl_setopt($curl, CURLOPT_HEADER, false);
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($curl, CURLOPT_HTTPHEADER,
                                    array(
                                        "Content-type: application/json",
                                        "appID: " . $push_appID,
                                        "appSecret: " . $push_appSecret
                                        )
                                    );
                            curl_setopt($curl, CURLOPT_POST, true);
                            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

                            curl_exec($curl);

                            $out = [ "response" => "contact_accept_okay", ];
                            output($out);
                        }
                    }
                } 
                else {
                    unset($row['session']); 

                    $out = [ "response" => "contact_accept_expired", ];
                    output($out);
                } 
            }

            unset($row['session']); 

            $out = [ "response" => "contact_accept_error", ];
            output($out);
        }

        /*
        * push_message
        * responses:
        *   push_message_okay - Push successfully sent.
        *   push_message_expired - Session has expired.
        *   push_message_error - Generic error.
        */
        elseif ($_POST['request'] == "push_message") {

            $query = "SELECT session, firstname, lastname FROM users WHERE id = :id";
            $query_params = array(':id' => $_POST['user']); 
             
            try { 
                $stmt = $db->prepare($query); 
                $result = $stmt->execute($query_params); 
            } 
            catch(PDOException $e) { 
                dberror();
            } 
             
            $row = $stmt->fetch(); 
            if($row) {               
                if(password_verify($_POST['pass'], $row['session'])) {
                    unset($row['session']);

                    // Do processing here.
                    $firstname = $row['firstname'];
                    $lastname = $row['lastname'];

                    $contacts = explode(',', $_POST['contacts']);

                    foreach ($contacts as $contact) {
                        $query = "SELECT push FROM users WHERE id = :id";
                        $query_params = array(':id' => $contact); 
                         
                        try {
                            $stmt = $db->prepare($query); 
                            $result = $stmt->execute($query_params); 
                        } 
                        catch(PDOException $e) { 
                            dberror();
                        }

                        $row = $stmt->fetch(); 
                        if($row) {
                            if ($row['push'] != null) {
                                $factory = new RandomLib\Factory;
                                $generator = $factory->getLowStrengthGenerator();
                                $generated = $generator->generateString(16, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

                                $url = getServer($row['push']); 
                                $body = json_encode(array(
                                    "regID" => $row['push'],
                                    "requestID" => $generated,
                                    "message" => "badgeOption=ALERT&badgeNumber=1&action=LAUNCH&alertMessage=" . urlencode($firstname . " " . $lastname . " notified you: ") . $_POST['message'],
                                    "appData" => json_encode(array(
                                            "contact" => $_POST['user'],
                                            "type" => "push_message",
                                            "plateType" => $_POST['type'],
                                            "color" => $_POST['color'],
                                            "message" => $_POST['message'],
                                            "plateid" => $_POST['plateid'],
                                            "firstname" => $firstname,
                                            "lastname" => $lastname
                                        ))
                                ));  

                                $curl = curl_init($url);
                                curl_setopt($curl, CURLOPT_HEADER, false);
                                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($curl, CURLOPT_HTTPHEADER,
                                        array(
                                            "Content-type: application/json",
                                            "appID: " . $push_appID,
                                            "appSecret: " . $push_appSecret
                                            )
                                        );
                                curl_setopt($curl, CURLOPT_POST, true);
                                curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

                                curl_exec($curl);
                            }
                        }
                        else {
                            $out = [ "response" => "push_message_error", ];
                            output($out);                            
                        }
                    }

                    $out = [ "response" => "push_message_okay", ];
                    output($out);
                } 
                else {
                    unset($row['session']); 

                    $out = [ "response" => "push_message_expired", ];
                    output($out);
                } 
            }

            unset($row['session']); 

            $out = [ "response" => "push_message_error", ];
            output($out);
        }

        /*
        * event_decline
        * responses:
        *   event_decline_okay - Contact reject successful.
        *   event_decline_expired - Session has expired.
        *   event_decline_error - Generic error.
        */
        elseif ($_POST['request'] == "event_decline") {
            $query = "SELECT session, firstname, lastname FROM users WHERE id = :id";
            $query_params = array(':id' => $_POST['user']); 
             
            try { 
                $stmt = $db->prepare($query); 
                $result = $stmt->execute($query_params); 
            } 
            catch(PDOException $e) { 
                dberror();
            } 
             
            $row = $stmt->fetch(); 
            if($row) {               
                if(password_verify($_POST['pass'], $row['session'])) {
                    unset($row['session']);

                    // Do processing here.
                    $firstname = $row['firstname'];
                    $lastname = $row['lastname'];

                    $query = "SELECT contacts, push FROM users WHERE id = :id";
                    $query_params = array(':id' => $_POST['contact']);
             
                    try { 
                        $stmt = $db->prepare($query); 
                        $result = $stmt->execute($query_params); 
                    } 
                    catch(PDOException $e) { 
                        dberror();
                    } 

                    $row = $stmt->fetch(); 
                    if($row) {
                        $factory = new RandomLib\Factory;
                        $generator = $factory->getLowStrengthGenerator();
                        $generated = $generator->generateString(16, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

                        $url = getServer($row['push']); 
                        $body = json_encode(array(
                            "regID" => $row['push'],
                            "requestID" => $generated,
                            "message" => "badgeOption=ALERT&badgeNumber=1&action=LAUNCH&alertMessage=" . urlencode($firstname . " " . $lastname . " declined your event: " . $_POST['message']),
                            "appData" => json_encode(array(
                                    "type" => "event_decline",
                                    "message" => $_POST['message'],
                                    "plateid" => $_POST['plateid'],
                                    "firstname" => $firstname,
                                    "lastname" => $lastname
                                ))
                        ));  

                        $curl = curl_init($url);
                        curl_setopt($curl, CURLOPT_HEADER, false);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_HTTPHEADER,
                                array(
                                    "Content-type: application/json",
                                    "appID: " . $push_appID,
                                    "appSecret: " . $push_appSecret
                                    )
                                );
                        curl_setopt($curl, CURLOPT_POST, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

                        curl_exec($curl);

                        $out = [ "response" => "event_decline_okay", ];
                        output($out);
                    }
                }
                else {
                    unset($row['session']); 

                    $out = [ "response" => "event_decline_expired", ];
                    output($out);
                } 
            }

            unset($row['session']); 

            $out = [ "response" => "event_decline_error", ];
            output($out);
        }

        /*
        * event_accept
        * responses:
        *   event_accept_okay - Contact reject successful.
        *   event_accept_expired - Session has expired.
        *   event_accept_error - Generic error.
        */
        elseif ($_POST['request'] == "event_accept") {
            $query = "SELECT session, firstname, lastname FROM users WHERE id = :id";
            $query_params = array(':id' => $_POST['user']); 
             
            try { 
                $stmt = $db->prepare($query); 
                $result = $stmt->execute($query_params); 
            } 
            catch(PDOException $e) { 
                dberror();
            } 
             
            $row = $stmt->fetch(); 
            if($row) {               
                if(password_verify($_POST['pass'], $row['session'])) {
                    unset($row['session']);

                    // Do processing here.
                    $firstname = $row['firstname'];
                    $lastname = $row['lastname'];

                    $query = "SELECT contacts, push FROM users WHERE id = :id";
                    $query_params = array(':id' => $_POST['contact']);
             
                    try { 
                        $stmt = $db->prepare($query); 
                        $result = $stmt->execute($query_params); 
                    } 
                    catch(PDOException $e) { 
                        dberror();
                    } 

                    $row = $stmt->fetch(); 
                    if($row) {
                        $factory = new RandomLib\Factory;
                        $generator = $factory->getLowStrengthGenerator();
                        $generated = $generator->generateString(16, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');

                        $url = getServer($row['push']); 
                        $body = json_encode(array(
                            "regID" => $row['push'],
                            "requestID" => $generated,
                            "message" => "badgeOption=ALERT&badgeNumber=1&action=LAUNCH&alertMessage=" . urlencode($firstname . " " . $lastname . " accepted your event: " . $_POST['message']),
                            "appData" => json_encode(array(
                                    "type" => "event_accept",
                                    "message" => $_POST['message'],
                                    "plateid" => $_POST['plateid'],
                                    "firstname" => $firstname,
                                    "lastname" => $lastname
                                ))
                        ));  

                        $curl = curl_init($url);
                        curl_setopt($curl, CURLOPT_HEADER, false);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_HTTPHEADER,
                                array(
                                    "Content-type: application/json",
                                    "appID: " . $push_appID,
                                    "appSecret: " . $push_appSecret
                                    )
                                );
                        curl_setopt($curl, CURLOPT_POST, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

                        curl_exec($curl);

                        $out = [ "response" => "event_accept_okay", ];
                        output($out);
                    }
                }
                else {
                    unset($row['session']); 

                    $out = [ "response" => "event_accept_expired", ];
                    output($out);
                } 
            }

            unset($row['session']); 

            $out = [ "response" => "event_accept_error", ];
            output($out);
        }
    } 

    $out = [ "response" => "no_request", ];
    output($out);