<?php
/********************************************************************************************************
 *              Handle ahtentication, logount and registration of a user
 * 
 * ******************************************************************************************************/


//error reporting for DEBUG -> to be removed in production
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

/************
 * Includes
 ***********/
//verify if the script has been requested over https, if not do a self-redirect thour https
include 'utilities/checkSSL.php';

//include the database utilities functions
include 'utilities/db_utilities.php';
// including the session functions
include "utilities/session_utilities.php";

/*********************
 *      Constants
 ********************/
define('UNACCEPTED_CREDENTIALS', -1);
define('ACCEPTED_CREDENTIALS', 1);


/*******************
 * Global variables
 *******************/
$loginResult = 0;
$registrationResult = 0;
$userCredentials = ACCEPTED_CREDENTIALS;

$email = "";
$password = "";
$action = "";

/*************************************************
*   Inizio esecuzione script
*/

/*********************
* Inizializza sessione
*/
startOrUpdateSession();
/*********************/

/**
 * 1) Verify if the requested action is to logout, in this case destroy the session and exit
 */
if( isset($_POST['action']) ){

    $action = $_POST['action'];

    if( $action == "logout" ){
        
        destroySession("exit"); //exit parameter used to discriminate the different cases
        exit;
    }
}


/**
 * 2) Login or Registration requested: test which of them and take action
 */
if( isset($_POST['email']) ){

    //check and sanitize email
    $email = $_POST['email'];

    $domain = explode("@", $email); //divide in two substring

    if(count($domain) == 2 ){ //[0] = giovanni.garifo, [1] = polito.it

        $domain = $domain[1];
    
        if(!filter_var($email, FILTER_VALIDATE_EMAIL) /*|| !checkdnsrr($domain, 'MX')*/ ){
             $userCredentials = UNACCEPTED_CREDENTIALS;
        }

    } else $userCredentials = UNACCEPTED_CREDENTIALS; //se email non è composta da str1@str2 di sicuro non è accettata
}

if( isset($_POST['password'])){
    $password = $_POST['password']; //to be sanitized before quering db
}

if( isset($_POST['action'])){

    //check if action is admitted
    $action = $_POST['action'];

    if($action != "signinButton" || $action != "registerButton"){
        $userCredentials == UNACCEPTED_CREDENTIALS;
    } 
}


//test correctness
if( $userCredentials == UNACCEPTED_CREDENTIALS ){

    destroySession("noexit"); //destroySession will not call exit();
    echo "UNACCEPTED_CREDENTIALS"; 
    exit; 
}

/**
 * Input correctly sanitized and weel format, procede with DB interrogation for login/registration
 * 
 * CONNESSIONE AL DB
 */
if( db_connect() == DB_ERROR ){
    
    destroySession("noexit"); 
    echo "DB_ERROR";
    exit; 

}else{

    if( $action == "signinButton"){
        
        //LOGIN
        $loginResult = db_login_user($email, $password);
    
        if($loginResult == LOGIN_OK){

            //comunico avvenuto login ed AVVIO SESSIONE
            $_SESSION["email"] = $email; //salvo i dati sull'email dell'utente connesso
            echo "LOGIN_OK";

        } else {
            
            destroySession("noexit"); //distruggi sessione
            echo "UNACCEPTED_CREDENTIALS";
        } 
    
    } else if($action == "registerButton"){

        //REGISTRATION
        $registrationResult = db_register_user($email, $password);
      
        if($registrationResult == REGISTRATION_OK){

            //comunico avvenuta registraione ed AVVIO SESSIONE
            $_SESSION["email"] = $email; //salvo i dati sull'email dell'utente connesso
            echo "REGISTRATION_OK";

        } else {
            
            destroySession("noexit"); //distruggi sessione
            echo "UNACCEPTED_CREDENTIALS";
        } 
    }

}

//DISCONNESSIONE DAL DB
db_close();

//end
?>