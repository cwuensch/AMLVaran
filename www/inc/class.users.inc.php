<?php

/**
 * Handles user interactions within the app
 *
 * PHP version 5
 *
 * @author Jan Stenner
 *
 */
class DkhUsers
{
    /**
     * The database object
     *
     * @var object
     */
    private $_db;

    /**
     * Checks for a database object and creates one if none is found
     *
     * @param object $db
     * @return void
     */
    public function __construct($db=NULL)
    {
        if(is_object($db))
        {
            $this->_db = $db;
        }
        else
        {
            $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8";
            $this->_db = new PDO($dsn, DB_USER, DB_PASS);
        }
    }

    /**
     * Checks and inserts a new account email into the database
     *
     * @param string $email     The user's email address
     * @param string $password  The entered password
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function createAccount($email, $password)
    {
        $u = trim($email);

        if(filter_var($u, FILTER_VALIDATE_EMAIL) && strlen($password) > 5) {
            $v = sha1(mt_rand());

            require_once 'inc/phpass-0.3/PasswordHash.php';
            $hasher = new PasswordHash(8, FALSE);
            $pass = $hasher->HashPassword($password);

            $sql = "SELECT COUNT(Username) AS theCount
                    FROM users
                    WHERE Username=:email OR newemail=:email";
            if($stmt = $this->_db->prepare($sql)) {
                $stmt->bindParam(":email", $u, PDO::PARAM_STR);
                $stmt->execute();
                $row = $stmt->fetch();
                if($row['theCount']!=0) {
                    return array(0, "<h2> Error </h2>"
                        . "<p> Sorry, that email is already in use. "
                        . "Please try again. </p>");
                }
                if($this->sendVerificationEmail($u, $v) == 0) {
                    return array(0, "There was an error sending your"
                        . " verification email. Please contact"
                        . " us for support. We apologize for the "
                        . "inconvenience.");
                }
                $stmt->closeCursor();
                if($this->sendAdminEmail($u) == 0) {
                    return array(0, "There was an error sending an email to"
                        . " the amlvaran-admin, to give you edit-permissions for this site."
                        . " Please contact us for support. We apologize for the "
                        . "inconvenience.");
                }
                $stmt->closeCursor();
            }

            $sql = "INSERT INTO users(Username, ver_code, Password)
                    VALUES(:email, :ver, :pass)";
            if($stmt = $this->_db->prepare($sql)) {
                $stmt->bindParam(":email", $u, PDO::PARAM_STR);
                $stmt->bindParam(":ver", $v, PDO::PARAM_STR);
                $stmt->bindParam(":pass", $pass, PDO::PARAM_STR);
                $stmt->execute();
                $stmt->closeCursor();
                
                $this->createSettings($this->_db->lastInsertId());

                return array(1, "<h2> Success! </h2>"
                        . "<p> Your account was successfully "
                        . "created with the username <strong>$u</strong>."
                        . " Check your email!</p>");
            } else {
                return array(0, "<h2> Error </h2><p> Couldn't insert the "
                    . "user information into the database.</p>");
            }
        } else {
            return array(0, "<h2> Error </h2><p> It seems like you "
                    . "did not enter a valid Email address or a password "
                    . "shorter than 6 characters</p>");
        }
    }



    /**
     * Sends an email to a user with a link to verify their new account
     *
     * @param string $email    The user's email address
     * @param string $ver    The random verification code for the user
     *
     * @return boolean        TRUE on successful send and FALSE on failure
     */
    private function sendVerificationEmail($email, $ver)
    {
        $e = sha1($email); // For verification purposes
        $to = trim($email);

        $msg = <<<EMAIL
Dear Sir or Madam,
thanks for signing up for a AML VARAN account.
Please complete your registration by following the instructions below.

To get started, please activate your account and choose a
password by following the link below.

Notice that your current account is a read-only account.
You have no edit-permissions and cannot create new patients or upload a sample.
The amlvaran admin will check your registration, give you access to edit-permissions 
and send you a verification mail as soon as possible.

Activate your account: https://{$_SERVER['HTTP_HOST']}/verifyaccount.php?v=$ver&e=$e

If you have any questions, please contact amlvaran@uni-muenster.de.

--
Thanks!
EMAIL;

        require_once 'inc/swiftmailer-5.x/lib/swift_required.php';

        // Create the message
        $message = Swift_Message::newInstance()

          // Give the message a subject
          ->setSubject('[AMLVARAN] Please Verify Your Account')

          // Set the From address with an associative array
          ->setFrom(array('amlvaran@uni-muenster.de' => 'AML VARAN Registration'))

          // Set the To addresses with an associative array
          ->setTo(array($to))

          // Give it a body
          ->setBody($msg)

          // And optionally an alternative body
          // ->addPart('<q>Here is the message itself</q>', 'text/html')
        ;

        $transport = Swift_SmtpTransport::newInstance('imimail.uni-muenster.de', 25);

        $mailer = Swift_Mailer::newInstance($transport);

        return $mailer->send($message);
    }
    
    /**
     * Sends an email to the admin to give a new user edit-permissions
     *
     * @param string $email    The user's email address
     *
     * @return boolean        TRUE on successful send and FALSE on failure
     */
    private function sendAdminEmail($email)
    {
        $user = trim($email);
        $to = trim("amlvaran@uni-muenster.de");

        $msg = <<<EMAIL
Dear amlvaran-admin,

a new user has signed up.
Please give $user edit-permissions.


--
Thanks!
EMAIL;

        require_once 'inc/swiftmailer-5.x/lib/swift_required.php';

        // Create the message
        $message = Swift_Message::newInstance()

          // Give the message a subject
          ->setSubject('[AMLVARAN] Please Verify Your Account')

          // Set the From address with an associative array
          ->setFrom(array('amlvaran@uni-muenster.de' => 'AML VARAN Registration'))

          // Set the To addresses with an associative array
          ->setTo(array($to))

          // Give it a body
          ->setBody($msg)

          // And optionally an alternative body
          // ->addPart('<q>Here is the message itself</q>', 'text/html')
        ;

        $transport = Swift_SmtpTransport::newInstance('imimail.uni-muenster.de', 25);

        $mailer = Swift_Mailer::newInstance($transport);

        return $mailer->send($message);
    }

    /**
     * Checks credentials and verifies a user account
     *
     * @return array    an array containing a status code and status message
     */
    public function verifyAccount()
    {
        $sql = "SELECT verified, ver_code, Username, UserID, editPermission
                FROM users
                WHERE SHA1(Username)=:user";

        if($stmt = $this->_db->prepare($sql))
        {
            $stmt->bindParam(':user', $_GET['e'], PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch();
            if(isset($row['verified']) && isset($row['ver_code']) && isset($row['Username']))
            {
                if($row['verified'] == 0) {
                    if($row['ver_code'] == $_GET['v']) {
                        $stmt->closeCursor();

                        $sql = "UPDATE users
                            SET verified=1
                            WHERE ver_code=:ver
                            AND SHA1(Username)=:user
                            LIMIT 1";

                        try
                        {
                            $stmt = $this->_db->prepare($sql);
                            $stmt->bindParam(":ver", $_GET['v'], PDO::PARAM_STR);
                            $stmt->bindParam(":user", $_GET['e'], PDO::PARAM_STR);
                            $stmt->execute();
                            $stmt->closeCursor();

                            // Logs the user in if verification is successful
                            $_SESSION['Username'] = $row['Username'];
                            $_SESSION['UserID'] = $row['UserID'];
                            $_SESSION['LoggedIn'] = 1;
                            $_SESSION['editPermission'] = $row['editPermission'];

                            return array(5, "<h2>Verification Success!</h2>"
                            . "<p>Logging you in and redirecting you to start page...</p>");
                        }
                        catch(PDOException $e)
                        {
                            return array(4, "Verification Error:"
                            . "Could not reach the database.");
                        }
                    }
                    else
                    {
                        return array(3, "<h2>Verification Error</h2>"
                        . "<p>Your verification code did not match.</p>");
                    }
                }
                else
                {
                    return array(2, "<h2>Verification Error</h2>"
                    . "<p>This account is already verified.</p>");
                }
            }
            else
            {
                return array(1, "<h2>Verification Error</h2>"
                    . "<p>Your verification request did not match any registered user.</p>");
            }
        }
        else
        {
            return array(0, "<h2>Error</h2><p>Database error.</p>");
        }
    }

    /**
     * Checks if the provided email is registered in the system
     *
     * @param String email  The email adress
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function checkEmail($email)
    {
        $sql = "SELECT UserID
            FROM users
            WHERE Username=:email
            LIMIT 1";
        try
        {
            $stmt = $this->_db->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            if($stmt->rowCount()==1)
            {
                return array(1, 1);
            } else {
                return array(1, 0);
            }
        }
        catch(PDOException $e)
        {
            return array(0, "<h2>Error:</h2>"
                . "<p>Could not fetch users.</p>");
        }
    }

    /**
     * Sends an email to a user with a link to change their password
     *
     * @param string $email    The user's email address
     * @param string $ver    The random verification code for the user
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function sendPasswordLink($email)
    {
        $v = sha1(mt_rand());

        require_once 'inc/phpass-0.3/PasswordHash.php';
        $hasher = new PasswordHash(8, FALSE);
        $vhashed = $hasher->HashPassword($v);

        $sql = "UPDATE users
                SET pw_ver_code=:v
                WHERE Username=:email
                LIMIT 1";
        try
        {
            $stmt = $this->_db->prepare($sql);
            $stmt->bindParam(':v', $vhashed, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            if($stmt->execute()) {
                if($stmt->rowCount()==1) {
                    $e = sha1($email); // For verification purposes
                    $to = trim($email);

                    $msg = <<<EMAIL
Dear Sir or Madam,
you receive this mail because you requested a new password on the AMLVARAN system.

Change your password by following this link:
https://{$_SERVER['HTTP_HOST']}/password.php?v=$v&e=$e

If you didn't request this password change, you can ignore this mail.
If you received this mail multiple times, please contact amlvaran@uni-muenster.de.

--
Thanks!
EMAIL;

                    require_once 'inc/swiftmailer-5.x/lib/swift_required.php';

                    // Create the message
                    $message = Swift_Message::newInstance()

                      // Give the message a subject
                      ->setSubject('[AMLVARAN] Changing your password')

                      // Set the From address with an associative array
                      ->setFrom(array('amlvaran@uni-muenster.de' => 'AML VARAN Service'))

                      // Set the To addresses with an associative array
                      ->setTo(array($to))

                      // Give it a body
                      ->setBody($msg)

                      // And optionally an alternative body
                      // ->addPart('<q>Here is the message itself</q>', 'text/html')
                    ;

                    $transport = Swift_SmtpTransport::newInstance('imimail.uni-muenster.de', 25);

                    $mailer = Swift_Mailer::newInstance($transport);

                    if($mailer->send($message)) {
                        return array(1, 1);
                    } else {
                        return array(0, "There was an error sending your password restore email. Please contact us for support. We apologize for the inconvenience.");
                    }
                } else {
                    return array(0, "<h2>Error:</h2><p>Could not create a password link.</p>");
                }
            } else {
                return array(0, "<h2>Error:</h2><p>Database Error.</p>");
            }
            $stmt->closeCursor();


        }
        catch(PDOException $e)
        {
            return array(0, "<h2>Error:</h2><p>Database Error.</p>");
        }
    }

    /**
     * Changes the user's password if it was forgotten and the validation code matches
     *
     * @param string $v    The random verification code for the user
     * @param string $e    The hashed email adress
     * @param string $password    The new password
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function updateForgottenPassword($v, $e, $password) {
        $sql = "SELECT UserID, editPermission, pw_ver_code
                FROM users
                WHERE SHA1(Username)=:user
                LIMIT 1";
        try
        {
            $stmt = $this->_db->prepare($sql);
            $stmt->bindParam(':user', $e, PDO::PARAM_STR);
            $stmt->execute();
            if($stmt->rowCount()==1)
            {
                $row = $stmt->fetch();

                require_once 'inc/phpass-0.3/PasswordHash.php';
                $hasher = new PasswordHash(8, FALSE);
                $check = $hasher->CheckPassword($v, $row['pw_ver_code']);

                if($check) {
                    if($row['editPermission'] == 1) {
                        $pass = $hasher->HashPassword($password);
                        $sql = "UPDATE users
                                SET Password=:pass, pw_ver_code=NULL
                                WHERE SHA1(Username)=:user
                                LIMIT 1";
                        try {
                            $stmt = $this->_db->prepare($sql);
                            $stmt->bindParam(":pass", $pass, PDO::PARAM_STR);
                            $stmt->bindParam(':user', $e, PDO::PARAM_STR);
                            if($stmt->execute()) {
                                if($stmt->rowCount()==1) {
                                    return array(1, 1);
                                } else {
                                    return array(0, "<h2>Error:</h2><p>Could not set new password.</p>");
                                }
                            } else {
                                return array(0, "<h2>Error:</h2><p>Database Error.</p>");
                            }
                        } catch(PDOException $e) {
                            return array(0, "<h2>Error:</h2><p>Database Error.</p>");
                        }
                    } else {
                        return array(0, "<h2>Error:</h2><p>You do not have permission to apply account changes.</p>");
                    }
                } else {
                    return array(0, "<h2>Error:</h2><p>This link is not valid (anymore).</p>");
                }
            }
            else
            {
                return array(0, "<h2>Error:</h2><p>No user with your email adress found.</p>");
            }
        }
        catch(PDOException $e)
        {
            return array(0, "<h2>Error:</h2><p>Database Error.</p>");
        }
    }

    /**
     * Changes the email adress
     *
     * @param string $newemail     The user's new email address
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function changeEmail($newemail)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $u = trim($newemail);

            if(filter_var($u, FILTER_VALIDATE_EMAIL)) {
                $v = sha1(mt_rand());

                require_once 'inc/phpass-0.3/PasswordHash.php';
                $hasher = new PasswordHash(8, FALSE);
                $vhashed = $hasher->HashPassword($v);

                $sql = "SELECT COUNT(Username) AS theCount
                    FROM users
                    WHERE Username=:email OR newemail=:email";
                if($stmt = $this->_db->prepare($sql)) {
                    $stmt->bindParam(":email", $u, PDO::PARAM_STR);
                    $stmt->execute();
                    $row = $stmt->fetch();
                    if($row['theCount']!=0) {
                        return array(0, "<h2> Error </h2>"
                            . "<p> Sorry, that email is already in use. "
                            . "Please try again. </p>");
                    }
                }

                $sql = "SELECT verified, editPermission
                        FROM users
                        WHERE UserID=:uid";
                if($stmt = $this->_db->prepare($sql)) {
                    $stmt->bindParam(":uid", $_SESSION['UserID'], PDO::PARAM_INT);
                    $stmt->execute();
                    $row = $stmt->fetch();
                    if($row['verified'] == 1 && $row['editPermission'] == 1) {
                        if($this->sendNewEmailVerification($u, $v) == 0) {
                            return array(0, "<h2> Error </h2>"
                                . "<p> There was an error sending your"
                                . " verification email. Please contact"
                                . " us for support. We apologize for the "
                                . "inconvenience. </p>");
                        }
                        $stmt->closeCursor();
                    } else {
                        return array(0, "<h2> Error </h2><p> Account is not verified or has no edit permission.</p>");
                    }
                }

                $sql = "UPDATE users
                        SET newemail=:newemail, ver_code=:vercode
                        WHERE UserID=:user
                        LIMIT 1";
                if($stmt = $this->_db->prepare($sql)) {
                    $stmt->bindParam(":newemail", $u, PDO::PARAM_STR);
                    $stmt->bindParam(":vercode", $vhashed, PDO::PARAM_STR);
                    $stmt->bindParam(":user", $_SESSION['UserID'], PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt->closeCursor();

                    return array(1, "<h2> Success! </h2>"
                            . "<p>We have sent you a verification mail to your new email address!</br>"
                            . "Please follow the instructions in the mail to activate it.</p>");
                } else {
                    return array(0, "<h2> Error </h2><p> Couldn't insert the "
                        . "user information into the database.</p>");
                }
            } else {
                return array(0, "<h2> Error </h2><p> It seems like you "
                        . "did not enter a valid Email address or a password "
                        . "shorter than 6 characters</p>");
            }
        } else {
            return array(0, "<h2>Error:</h2>"
                    . "<p>It seems like you are not logged in.</p>");
        }
    }

    /**
     * Sends an email to a user with a link to verify their new email address
     *
     * @param string $email    The user's email address
     * @param string $ver    The random verification code for the user
     *
     * @return boolean        TRUE on successful send and FALSE on failure
     */
    private function sendNewEmailVerification($email, $ver)
    {
        $e = sha1($email); // For verification purposes
        $to = trim($email);

        $msg = <<<EMAIL
Dear Sir or Madam,
you requested to change your Email address for your AML Varan account.

To get verify this Email address, please visit the following link:

https://{$_SERVER['HTTP_HOST']}/accountsettings.php?v=$ver&e=$e

If you have any questions, please contact amlvaran@uni-muenster.de.

--
Thanks!
EMAIL;

        require_once 'inc/swiftmailer-5.x/lib/swift_required.php';

        // Create the message
        $message = Swift_Message::newInstance()

          // Give the message a subject
          ->setSubject('[AMLVARAN] Please Verify Your New Email Address')

          // Set the From address with an associative array
          ->setFrom(array('amlvaran@uni-muenster.de' => 'AML VARAN Account Changes'))

          // Set the To addresses with an associative array
          ->setTo(array($to))

          // Give it a body
          ->setBody($msg)

          // And optionally an alternative body
          // ->addPart('<q>Here is the message itself</q>', 'text/html')
        ;

        $transport = Swift_SmtpTransport::newInstance('imimail.uni-muenster.de', 25);

        $mailer = Swift_Mailer::newInstance($transport);

        return $mailer->send($message);
    }

    /**
     * Changes the user's email if it was forgotten and the validation code matches
     *
     * @param string $v    The random verification code for the user
     * @param string $e    The hashed email adress
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function validateNewEmail($v, $e) {
        $sql = "SELECT UserID, ver_code, newemail
                FROM users
                WHERE SHA1(newemail)=:user
                LIMIT 1";
        try
        {
            $stmt = $this->_db->prepare($sql);
            $stmt->bindParam(':user', $e, PDO::PARAM_STR);
            $stmt->execute();
            if($stmt->rowCount()==1)
            {
                $row = $stmt->fetch();

                require_once 'inc/phpass-0.3/PasswordHash.php';
                $hasher = new PasswordHash(8, FALSE);
                $check = $hasher->CheckPassword($v, $row['ver_code']);

                if($check) {
                    $sql = "UPDATE users
                            SET Username=:newemail, newemail=NULL, ver_code=NULL
                            WHERE UserID=:userid
                            LIMIT 1";
                    try {
                        $stmt = $this->_db->prepare($sql);
                        $stmt->bindParam(":newemail", $row['newemail'], PDO::PARAM_STR);
                        $stmt->bindParam(':userid', $row['UserID'], PDO::PARAM_INT);
                        if($stmt->execute()) {
                            if($stmt->rowCount()==1) {
                                return array(1, "<h2> Success! </h2>"
                                    . "<p>Your email address was successfully updated!</br>"
                                    . "You will be redirected to the login page and have to log in again.</p>");
                            } else {
                                return array(0, "<h2>Error:</h2><p>Could not set new password.</p>");
                            }
                        } else {
                            return array(0, "<h2>Error:</h2><p>Database Error.</p>");
                        }
                    } catch(PDOException $e) {
                        return array(0, "<h2>Error:</h2><p>Database Error.</p>");
                    }
                } else {
                    return array(0, "<h2>Error:</h2><p>This link is not valid (anymore).</p>");
                }
            }
            else
            {
                return array(0, "<h2>Error:</h2><p>No user with your email adress found.</p>");
            }
        }
        catch(PDOException $e)
        {
            return array(0, "<h2>Error:</h2><p>Database Error.</p>");
        }
    }

    /**
     * Changes the password of the currently logged in user
     *
     * @param string $newPassword   The new password
     * @param string $password      The old password
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function changePassword($newPassword, $password)
    {
        $sql = "SELECT Password, verified
                FROM users
                WHERE UserID=:uid
                LIMIT 1";
        try
        {
            $stmt = $this->_db->prepare($sql);
            $stmt->bindParam(':uid', $_SESSION['UserID'], PDO::PARAM_INT);
            $stmt->execute();
            if($stmt->rowCount()==1)
            {
                $row = $stmt->fetch();

                require_once 'inc/phpass-0.3/PasswordHash.php';
                $hasher = new PasswordHash(8, FALSE);
                $check = $hasher->CheckPassword($password, $row['Password']);

                if($check) {
                    if($row['verified'] == 1) {
                        $pass = $hasher->HashPassword($newPassword);
                        $sql = "UPDATE users
                                SET Password=:pass
                                WHERE UserID=:uid
                                LIMIT 1";
                        try {
                            $stmt = $this->_db->prepare($sql);
                            $stmt->bindParam(":pass", $pass, PDO::PARAM_STR);
                            $stmt->bindParam(':uid', $_SESSION['UserID'], PDO::PARAM_INT);
                            if($stmt->execute()) {
                                if($stmt->rowCount()==1) {
                                    return array(1, "<h2>Success!</h2><p>Your password was successfully changed.</p>");
                                } else {
                                    return array(0, "<h2>Error:</h2><p>Could not set new password.</p>");
                                }
                            } else {
                                return array(0, "<h2>Error:</h2><p>Database Error.</p>");
                            }
                        } catch(PDOException $e) {
                            return array(0, "<h2>Error:</h2><p>Database Error.</p>");
                        }
                    } else {
                        return array(0, "<h2>Error:</h2><p>You do not have permission to apply account changes.</p>");
                    }
                } else {
                    return array(0, "<h2>Error:</h2><p>Wrong password.</p>");
                }
            }
            else
            {
                return array(0, "<h2>Error:</h2><p>No user found.</p>");
            }
        }
        catch(PDOException $e)
        {
            return array(0, "<h2>Error:</h2><p>Database Error.</p>");
        }
    }

    /**
     * Checks credentials and logs the user in
     *
     * @return int    containing login status information
     */
    public function accountLogin()
    {
        $sql = "SELECT UserID, Password, verified, editPermission
                FROM users
                WHERE Username=:user
                LIMIT 1";
        try
        {
            $stmt = $this->_db->prepare($sql);
            $stmt->bindParam(':user', $_POST['email'], PDO::PARAM_STR);
            $stmt->execute();
            if($stmt->rowCount()==1)
            {
                $row = $stmt->fetch();

                require_once 'inc/phpass-0.3/PasswordHash.php';
                $hasher = new PasswordHash(8, FALSE);
                $check = $hasher->CheckPassword($_POST['password'], $row['Password']);

                if($check) {
                    if($row['verified'] == 1) {
                        $_SESSION['Username'] = htmlentities($_POST['email'], ENT_QUOTES);
                        $_SESSION['UserID'] = $row['UserID'];
                        $_SESSION['LoggedIn'] = 1;
                        $_SESSION['editPermission'] = $row['editPermission'];
                        return 3;
                    } else {
                        return 2;
                    }
                } else {
                    return 1;
                }
            }
            else
            {
                return 1;
            }
        }
        catch(PDOException $e)
        {
            return 0;
        }
    }
    
    /**
     * Inserts default usersettings for filters into the Database. 
     *
     * @param int $uid   The UserID
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    private function createSettings($uid) 
    {
        $sql = "INSERT INTO usersettings(UserID, regionFilter, typeFilter, exclusionFilters, selectedColumns, sortList)
            VALUES(:uid, 1, 0, '[\"artifacts\",\"polymorphisms\"]', '[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29]', '[]')";
        if($stmt = $this->_db->prepare($sql)) {            
            $stmt->bindParam(":uid", $uid, PDO::PARAM_INT);           
            $stmt->execute();
            $stmt->closeCursor();
            
            return array(1, 'Success');
        } else {
            return array(0, "<h2> Error </h2><p> Couldn't insert the "
                . "usersettings into the database.</p>");
        }
    }
    
    
    
    /**
     * Inserts usersettings for filters into the Database. This function is used if the usersettings are missing while the user tries to use the Website
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function createMissingSettings($regionFilter, $typeFilter, $exclusionFilters, $selectedColumns, $sortList, $columnWidth) 
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $sql = "INSERT INTO usersettings(UserID, regionFilter, typeFilter, exclusionFilters, selectedColumns, sortList, columnWidth)
                VALUES(:uid, :region, :type, :exclusion, :columns, :sortList, :columnWidth)";
            if($stmt = $this->_db->prepare($sql)) {            
                $stmt->bindParam(':uid', $_SESSION['UserID'], PDO::PARAM_INT);  
                $stmt->bindParam(":region", $regionFilter, PDO::PARAM_INT);
                $stmt->bindParam(":type", $typeFilter, PDO::PARAM_INT);
                $stmt->bindParam(":exclusion", $exclusionFilters, PDO::PARAM_STR);
                $stmt->bindParam(":columns", $selectedColumns, PDO::PARAM_STR);
                $stmt->bindParam(":sortList", $sortList, PDO::PARAM_STR);
                $stmt->bindParam(":columnWidth", $columnWidth, PDO::PARAM_STR);
                $stmt->execute();

                return array(1, 'Success');
            } else {
                return array(0, "<h2> Error </h2><p> Couldn't load the usersettings. Couldn't insert new "
                    . "usersettings into the database.</p>");
            }
        }
        else{
            return array(0, "<h2>Error:</h2>"
                    . "<p>It seems like you are not logged in.</p>");
        }
    }
    
    /**
     * Fetches all settings info of the currently logged in user
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function loadSettings()
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $sql = "SELECT regionFilter, typeFilter, exclusionFilters, selectedColumns, sortList, columnWidth
                FROM usersettings
                WHERE UserID=:uid";
            try
            {
                $stmt = $this->_db->prepare($sql);
                $stmt->bindParam(':uid', $_SESSION['UserID'], PDO::PARAM_INT);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return array(1, $row);
            }
            catch(PDOException $e)
            {
                return array(0, "<h2>Error:</h2>"
                    . "<p>Could not fetch Usersettings.</p>");
            }
        } else {
            return array(0, "<h2>Error:</h2>"
                    . "<p>It seems like you are not logged in.</p>");
        }
    }
    
    /**
     * Updates the usersettings for the logged in  user
     *
     * @param int $regionFilter  The ID of the selected regionFilter
     * @param int $typeFilter  The ID of the selected typeFilter
     * @param String $exclusionFilters  The selected exclusionFilters
     * @param String $selectedColumns  The selected Columns
     * @param String $sortList  Information about how the Table is sorted
     *
     * @return array    An array containing a status code and the query results array or an error message
     */
    public function saveSettings($regionFilter, $typeFilter, $exclusionFilters, $selectedColumns, $sortList, $columnWidth)
    {
        if(isset($_SESSION['UserID']) && isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1) {
            $sql = "UPDATE usersettings
                SET regionFilter=:region, typeFilter=:type, exclusionFilters=:exclusion, selectedColumns=:columns, sortList=:sortList, columnWidth=:columnWidth
                WHERE UserID=:uid";
            try
            {
                $stmt = $this->_db->prepare($sql);
                $stmt->bindParam(':uid', $_SESSION['UserID'], PDO::PARAM_INT);
                $stmt->bindParam(":region", $regionFilter, PDO::PARAM_INT);
                $stmt->bindParam(":type", $typeFilter, PDO::PARAM_INT);
                $stmt->bindParam(":exclusion", $exclusionFilters, PDO::PARAM_STR);
                $stmt->bindParam(":columns", $selectedColumns, PDO::PARAM_STR);
                $stmt->bindParam(":sortList", $sortList, PDO::PARAM_STR);
                $stmt->bindParam(":columnWidth", $columnWidth, PDO::PARAM_STR);
                $stmt->execute();

                if($stmt->rowCount() > 0)
                {
                    return array(1, 'Success');
                }
                else
                {
                    return array(1, 'No changes made');
                }
            }
            catch(PDOException $e)
            {
                return array(0, "<h2>Error:</h2>"
                    . "<p>Something went wrong while updating the usersettings.</p>");
            }
        } else {
            return array(0, "<h2>Error:</h2>"
                    . "<p>It seems like you are not logged in.</p>");
        }
    }
    
}


?>
