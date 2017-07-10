<?php
/*********************************************************************************************************
* Library that contains function used to query the MySQL database with mysql-improved PHP interface
**********************************************************************************************************/

/****************
 *   Constants
 ****************/

//db info
DEFINE ('DB_USER', 'root');
DEFINE ('DB_PASSWORD', '');
DEFINE ('DB_HOST', 'localhost');
DEFINE ('DB_NAME', 'consult-db');

//db errors
DEFINE ('DB_ERROR', -1);
DEFINE ('DB_CONNECTED', 1);

//query return values
DEFINE ('LOGIN_OK', 10);
DEFINE ('LOGIN_KO', -10);
DEFINE ('REGISTRATION_OK', 11);
DEFINE ('REGISTRATION_KO', -11);
DEFINE ('BOOK_OK', 12);
DEFINE ('BOOK_KO', -12);
DEFINE ('NOT_ALREADY_BOOKED', 13);
DEFINE ('ALREADY_BOOKED', -13);
DEFINE ('DELETE_BOOKING_OK', +14);
DEFINE ('DELETE_BOOKING_KO', -14);



/*******************
 * Global variables
 *******************/
$conn; //contains the DB connection


/********************************************************
 *                      Functions 
 ********************************************************/

/**
 * Connect to the DB
 */
function db_connect(){
    
    global $conn; //forzo utilizzo variabile fuori dallo scope
    $DB_STATUS;

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if(!$conn){

        //impossibile connettersi al database
        $DB_STATUS = DB_ERROR;
        
    }else {
        //connessione avvenuta
        $DB_STATUS = DB_CONNECTED;
    }

    return $DB_STATUS;
}


/**
 * Close DB connection
 */
function db_close(){
    global $conn; //forzo utilizzo variabile fuori dallo scope
    mysqli_close($conn);
}



/**
 * Verify if user is registered so that it can be logged in
 */
function db_login_user($email, $password){

    global $conn; //forzo utilizzo variabile fuori dallo scope

    //sanitize user credentials
    $san_email = mysqli_real_escape_string($conn, $email);
    $san_password = mysqli_real_escape_string($conn, $password);
    
    try {

        //estrai hashed password dal db
        $query = "SELECT password FROM users WHERE email = '".$san_email."';";

        $res = mysqli_query($conn, $query);
        $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
        $hashedPassword = $row['password'];

        //confronta hashed password presente nel db con quella inserita dall'utente
        if( password_verify($password, $hashedPassword) == TRUE){
        
            //user esiste, può essere autenticato
            mysqli_free_result($res);
            return LOGIN_OK;

        } else {

            //user non registrato / credenziali errate
            mysqli_free_result($res);
            return LOGIN_KO;
        }

    } catch (Exception $e){
        
        mysqli_free_result($res);
        return LOGIN_KO;
    }
}

/**
 * Register a new user
 */
function db_register_user($email, $password){

    global $conn; //forzo utilizzo variabile fuori dallo scope

    //sanitize user credentials
    $san_email = mysqli_real_escape_string($conn, $email);
    $san_password = mysqli_real_escape_string($conn, $password);

    //now create hashed password with random salt (using the "$2y$" crypt format, which is always 60 characters wide.)
    $hashedPassword = password_hash($san_password, PASSWORD_BCRYPT);
    
    try {

        //insert user credential into database
        $query = "INSERT INTO users VALUES('".$san_email."','".$hashedPassword."', NULL, NULL, NULL)";

        $res = mysqli_query($conn, $query);

        if($res == TRUE){

            //user esiste, può essere autenticato
            return REGISTRATION_OK;

        } else {

            //user non registrato / credenziali errate
            return REGISTRATION_KO;
        }

    } catch(Exception $e) {

        return REGISTRATION_KO;
    }
}


/*************************************************************************************************/


/**
 * Query the database and return a serialized JSON that contains all the currently booked consultation
 */
function db_get_consultation_info(){
    
    global $conn; //forzo utilizzo variabile fuori dallo 
    $users = array();
    
    try {   

        $query = "SELECT email, min_requested, min_allowed, start_time FROM users WHERE min_allowed>0 ORDER BY start_time";

        $res = mysqli_query($conn, $query);

        if(!$res) 
            throw new Exception("[PHP error] unable to retrieve the user list");

        while( $r = mysqli_fetch_assoc($res) ){
            $users[] = $r;
        }

        mysqli_free_result($res);
        return json_encode($users); //here is returned a string that containes the JSON data 

    } catch( Exception $e ) {
        
        mysqli_free_result($res);
        return "NULL";
    }
}

/**
 * Get user info from the database 
 */
function db_get_current_book($email){
    
    global $conn; //forzo utilizzo variabile fuori dallo scope

    try {   

        $query = "SELECT min_requested, min_allowed FROM users WHERE email = '".$email."' ";

        $res = mysqli_query($conn, $query);

        if(!$res) 
            throw new Exception("[PHP error] unable to retrieve info about user");

        $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
    
        $min_r = $row['min_requested'];
        $min_a = $row['min_allowed'];

        mysqli_free_result($res);
        
        return $min_r."&".$min_a;

    } catch( Exception $e ) {
        
        mysqli_free_result($res);
        return "NULL";
    }
}


/*************************************************************************************************/

/**
 * Deletes the current booking, if present, of the user with $email
 */
function db_delete_booking($email){

    global $conn;
    $res = ""; 
    $min_allowed = 0;
    $min_requested = 0;

    //sanitize user credentials
    $san_email = mysqli_real_escape_string($conn, $email);

    //test if the user has aready booked a consultation, if yes deletes it
    if(test_user_already_booked($email) == NOT_ALREADY_BOOKED){
       
        return NOT_ALREADY_BOOKED;        
    
    } else {
        //actually deletes the booking
        
        try{

            //retrieves minutes allowed and requested for the user that want to delete his booking
            $res = mysqli_query($conn, "SELECT min_allowed, min_requested FROM users WHERE email = '".$san_email."' ");
            if(!$res) 
                throw new Exception("[PHP error] unable to retrieve the min_allowed of the user");

            $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
            $min_allowed = $row['min_allowed'];
            $min_requested = $row['min_requested'];

            //delete reservation info from the database for the user
            $res = mysqli_query($conn, "UPDATE users SET min_requested=NULL, min_allowed=NULL, start_time=NULL WHERE email='".$san_email."' ");
            if(!$res) 
                throw new Exception("[PHP error] unable to update the users table to delete a booking");

            //test which kind of update is required
            if( $min_allowed == $min_requested){
                /*
                * (1) no recalculation is needed
                */
                $res = mysqli_query($conn, "UPDATE consultation SET min_left = min_left + '".$min_allowed."' ");
                if(!$res) 
                    throw new Exception("[PHP error] unable to add minutes to min_left");

            } else {
                /*
                * (2) min_allowed<min_requested, recalculation of min_allowed, min_left and start_time is needed
                */
                //update min_left
                $res = mysqli_query($conn, "UPDATE consultation SET min_left = 180-(SELECT SUM(min_requested) FROM users) ");
                if(!$res) 
                    throw new Exception("[PHP error] unable to add minutes to min_left");
                
                //update min_allowed of all the other users
                $res = mysqli_query($conn, "UPDATE users SET min_allowed=min_requested WHERE min_allowed>0 ");
                if(!$res) 
                    throw new Exception("[PHP error] unable to update the users table to delete a booking");

                //update start_time
                db_recalculate_start_time($san_email);
            }

            return DELETE_BOOKING_OK;

        } catch(Exception $e){

            return NOT_ALREADY_BOOKED; //if the query return false or isn't able to be executed return this state
        }
    }
}

/**
 * Set a new booking fo the requesting user identified by $email
 */
function db_set_new_booking($email, $min_requested){
    
    global $conn; //forzo utilizzo variabile fuori dallo scope
    $ret = ""; //return value of the function
    $res = ""; //return value of the queries

    //sanitized user credentials
    $san_email = mysqli_real_escape_string($conn, $email);
    $san_min_requested = mysqli_real_escape_string($conn, $min_requested);

    /*
    * 0) test if the user has aready booked a consultation, in this case no other booking are permitted
    */
    if(test_user_already_booked($email) == ALREADY_BOOKED){
        return ALREADY_BOOKED;        
    }

    //START TRANSACTION
    try {   
        mysqli_autocommit($conn, false); //effettuerò commit manualmente alla fine della transazione

        //retrieve the number of minutes left
        $res = mysqli_query($conn, "SELECT min_left FROM consultation");
        if(!$res) 
            throw new Exception("[PHP error] unable to retrieve min_left from db");

        $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
        $min_left = $row['min_left']; 

        //if there is left time for another consultation, book one for the user
        if($min_left > 0){

            if($san_min_requested <= $min_left){
                /*
                * (1) there's enough time to book all the requested time for the consultation
                */

                db_book_user($san_email, $min_left, $san_min_requested);
                //everything OK, commit and return
                $ret = BOOK_OK;

            } else if($san_min_requested > $min_left){
                /*
                * (2) there's NOT enough time, recalculate all time to accomodate the consultation properly
                */

                db_book_user_with_recalculation($san_email, $min_left, $san_min_requested);
                //everything OK, commit and return
                $ret = BOOK_OK;
            }

        } else {
            
            //there aren't any minutes left for another consultation: cannot book 
            $ret = BOOK_KO;
        }

    } catch( Exception $e ) {
        //rollback in caso di fail di uno degli statement SQL
        mysqli_rollback($conn);
        $ret = BOOK_KO;
        return $ret;
    }
    
    //effettuo commit manuale
    mysqli_commit($conn);

    //END OF TRANSACTION

    return $ret;
}


/**
 * verify if the user already has a consultation booked.
 */
 function test_user_already_booked($email){

    global $conn; //forzo utilizzo variabile fuori dallo scope

    try{
    
        //retrieve the number of minutes left
        $res = mysqli_query($conn, "SELECT min_allowed FROM users WHERE email='".$email."' ");
        
        if(!$res) 
            throw new Exception("[PHP error] unable to retrieve min_allowed from db");

        $row = mysqli_fetch_array($res, MYSQLI_ASSOC);

        if( $row['min_allowed'] != NULL ){
            return ALREADY_BOOKED;
        } else return NOT_ALREADY_BOOKED;

    } catch(Exception $e) {
        return ALREADY_BOOKED; //don't allow new booking in any case
    }
}

/**
 * This function is called by db_set_new_booking when there are enough min_left to accomodate the requesting user
 * 
 * Exceptions are catched by the calling function
 */
function db_book_user($san_email, $min_left, $min_requested) {

    global $conn;

    $min_allowed = $min_requested; //in this case, the amaount of minutes allowed is equal to the requested ones
    $start_time = calculate_start_time();

    //update record of the user 
    $res = mysqli_query($conn, "UPDATE users SET min_requested=".$min_requested.", min_allowed=".$min_allowed.", start_time=".$start_time." WHERE email='".$san_email."' ");
    if(!$res) 
    throw new Exception("[PHP error] unable to update the informations of the user");

    //substract the minutes reserved for the user from the min_left
    $res = mysqli_query($conn, "UPDATE consultation SET min_left = min_left - ".$min_allowed);
    if(!$res) 
        throw new Exception("[PHP error] unable to update the informations of the user");
            
}

/**
 * This function is called by db_set_new_booking when there aren't enough min_left to accomodate the requesting user, a recalculation of the min_allowed is required
 * 
 * Exceptions are catched by the calling function
 */
function db_book_user_with_recalculation($san_email, $min_left, $min_requested) {

    global $conn;
    $total_requested_min = 0; //sum of all the min_requested in the db
    $scaling_factor = 0; //value used to scale the min_allowed of other users

    //calculate the total requested time:
    $res = mysqli_query($conn, "SELECT SUM(min_requested) AS tot FROM users WHERE min_allowed > 0");
    if(!$res) 
        throw new Exception("[PHP error] unable to execute query");

    $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
    $total_requested_min = $row['tot'] + $min_requested; //this is equal to the sum of ALL the users requested time
    $scaling_factor = 180/$total_requested_min;

    //update the user table to reflect the recalculation of the min_allowed
    $res = mysqli_query($conn, "UPDATE users SET min_allowed = min_requested*".$scaling_factor." WHERE min_allowed > 0 AND email != '".$san_email."' ");
    if(!$res) 
        throw new Exception("[PHP error] unable to execute query");

    //recalculate all the start_time of the users
    db_recalculate_start_time($san_email);

    //update the consultation table to reflect the fact that there's no other time left
    $res = mysqli_query($conn, "UPDATE consultation SET min_left = 0 ");
    if(!$res) 
        throw new Exception("[PHP error] unable to execute query");

    /*
    * Insert the new booking for the requesting user
    */
    $start_time = calculate_start_time();

    //calculate the total allowed time, after the update:
    $res = mysqli_query($conn, "SELECT SUM(min_allowed) AS tot FROM users WHERE min_allowed > 0");
    if(!$res) 
        throw new Exception("[PHP error] unable to execute query");
    
    $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
    $total_allowed_min = $row['tot'];

    //update the requesting user tuple
    $res = mysqli_query($conn, "UPDATE users SET min_allowed = 180-".$total_allowed_min.", min_requested='.$min_requested.', start_time=".$start_time." WHERE email ='".$san_email."' ");
    if(!$res) 
        throw new Exception("[PHP error] unable to execute query");
    
}


/*************************************************************************************************/

/**
 * Calculate the sum of all the min_allowed and generates the start_time for the next booking, ready to be injected into the DB
 *
 * Exceptions are catched by the calling function
 */
function calculate_start_time(){

    global $conn;

    $base_time = '14:00:00'; //represents the base upon which the time offsets needed to elaborate the correct start_time for the user
    $base_timestamp = strtotime($base_time); //timestamp generated from the base_time, in seconds
    $total_allowed_min = ""; // sum of all the min_allowed to the users

    //calculate start_time
    $res = mysqli_query($conn, "SELECT SUM(min_allowed) AS start_time_offset FROM users");
    if(!$res) 
        throw new Exception("[PHP error] unable to retrieve the maximum end_time from db");

    $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
    $total_allowed_min = $row['start_time_offset']; 

    $start_timestamp = $base_timestamp + (60*$total_allowed_min); //add amount of minutes to the timestamp
    //$start_time = date("H:i:s", $start_timestamp); //convert timestamp to date format hh:mm:ss compatible with mysql TIME

    mysqli_free_result($res);

    return $start_timestamp;
}

/**
 * Recalculate all the start time for every user (except the last and the first)
 *
 * This functions is needed because the following query (simplest solution) is not supported by MySQL:
 *
 * UPDATE users AS U1 SET start_time = ".strtotime('14:00:00')." + 60*(SELECT * FROM (SELECT SUM(min_allowed) FROM users AS U2 WHERE U2.start_time < U1.start_time ) )  WHERE min_allowed > 0 AND start_time !=".strtotime('14:00:00')."  AND email != '".$san_email."' "
 *
 *
 * Exceptions are catched by the calling function
 */
function db_recalculate_start_time($san_email){

    global $conn;

    $base_time = '14:00:00'; //represents the base upon which the time offsets needed to elaborate the correct start_time for the user
    $base_timestamp = strtotime($base_time); //timestamp generated from the base_time, in seconds
    
    /*
    *   retrieve the set of users that need the update
    */
    $query = "SELECT email, start_time FROM users WHERE start_time !=1499601600 AND email!='".$san_email."' ORDER BY start_time ";

    $res = mysqli_query($conn, $query);
    if(!$res) 
        throw new Exception("[PHP error] unable to retrieve the user list");
    
    /*
    * Update the start time for each user
    */

    //if empty set, so there's only one booking present: the start_time for this user must be the base_time, 14:00:00
    if( mysqli_num_rows($res) == 0 ){

        //update his start_time to the base one (14:00:00)
        $r = mysqli_query($conn, "UPDATE users SET start_time=".$base_timestamp." WHERE email ='".$user_email."' ");
        if(!$r) 
            throw new Exception("[PHP error] unable to execute query");

    } else {

        //for each user retrieved from the database, update their start_time
        while( $user = mysqli_fetch_assoc($res) ){

        $old_start_time = $user["start_time"];
        $user_email = $user["email"];
        $new_start_time = 0;

        //calculate the new start_time
        $r = mysqli_query($conn, "SELECT COALESCE(SUM(min_allowed), 0) AS start_time_offset FROM users WHERE email !='".$san_email."' AND email !='".$user_email."' AND start_time<".$old_start_time );
        if(!$r) 
            throw new Exception("[PHP error] unable to retrieve the maximum end_time from db");

        $row = mysqli_fetch_array($r, MYSQLI_ASSOC);
        $start_time_offset = $row['start_time_offset']; 

        $new_start_time = $base_timestamp + ($start_time_offset*60); //here we have the new timestamp for the user

        //insert it
        $r = mysqli_query($conn, "UPDATE users SET start_time=".$new_start_time." WHERE email ='".$user_email."' ");
        if(!$r) 
            throw new Exception("[PHP error] unable to execute query");

        }
    }

    mysqli_free_result($res);
}


//end
?>