<?php
/********************************************************************************************************
 *           Handle the reqest for a new booking, or the request for a deletion
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

//include 'session_manager.php'; //Fai partire la sessione / aggiornala
include 'utilities/db_utilities.php';

// including the session functions
include "utilities/session_utilities.php";

/*********************
 *      Constants
 ********************/
define('UNACCEPTED_INPUT', -10);


/*******************
 * Global variables
 *******************/
$bookResult = 0;
$inputData = "VALID"; //per verificare se input valido o meno

$min_requested;
$email;

/*********************
* Start session
*/
startOrUpdateSession();
/*********************/

/*************************************************
*   Inizio esecuzione script
*/

//verify if user is logged in
if( !isset($_SESSION['email']) ){
    echo "BOOK_KO"; 
    exit; 
}

/**
 * User requested to book a consultation
 */

if( isset($_POST['min_requested']) ){

    $min_requested = $_POST['min_requested'];

    if( $min_requested < 0 || $min_requested > 180) 
        $inputData = UNACCEPTED_INPUT;
}

//check sull'action richiesta
if( isset($_POST['action'])){

    //check if action is admitted
    $action = $_POST['action'];

    if($action != "userBooking"){ //testa se l'action è una di quelle previste
        $inputData == UNACCEPTED_INPUT;
    } 
}

//testo se min_requested ed action risultano entrambi validi
if( $inputData == UNACCEPTED_INPUT ){

    echo "UNACCEPTED_INPUT"; //ritorno al client questo valore
    exit; //blocco esecuzione script
}


//effettuo l'azione richiesta
if( db_connect() == DB_ERROR ){
    
    echo "DB_ERROR";

}else{

    if( $action == "userBooking" ){
        
        //try to reserve a consultation for the user
        $email = $_SESSION['email'];
        $min_requested = $_POST['min_requested'];
        
        $bookResult = db_set_new_booking($email, $min_requested);

        if( $bookResult == BOOK_OK){

            //user has been able to get a booking
            echo "BOOK_OK";

        } else if($bookResult == ALREADY_BOOKED) {

            echo "ALREADY_BOOKED";

        } else if($bookResult == BOOK_KO){

            echo "BOOK_KO";
        }
    
    } else if ( $action == "deleteBooking" ){

        $email = $_SESSION['email'];
        $bookResult = db_delete_booking($email);

        if( $bookResult == DELETE_BOOKING_OK){
            //user has been able to get a booking
            echo "DELETE_BOOKING_OK";

        } else if($bookResult == DELETE_BOOKING_KO) {

            echo "DELETE_BOOKING_KO";

        } else if($bookResult == NOT_ALREADY_BOOKED) {

            echo "NOT_ALREADY_BOOKED";
        }
    }
}

//DISCONNESSIONE DAL DB
db_close();

?>