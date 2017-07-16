<?php
/********************************************************************************************************
 *           used to allow the frontend to request the updated data from the DB
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

//inizializza la sessione, ma NON setta time()
session_start();

/*******************
 * Global variables
 *******************/
$thr;
$email;


/*************************************************
*   Inizio esecuzione script
*/

/**
 * 1) client asked for the list of current booked consultation, data will be sent as a serialized JSON
 */
if( isset($_POST['action']) ){

    $action = $_POST['action'];

    if( $action == "getConsultationInfo" ){
        
        db_connect();
        echo db_get_consultation_info();
        db_close();
        exit;
    }
}

/**
 * 2) client asked for the information of the logged user (min_requested and min_allowed)
 */
if( isset($_POST['action']) && isset($_SESSION['email'])){

    $action = $_POST['action'];

    if( $action == "getCurrentBook" ){
        
        db_connect();
        echo db_get_current_book($_SESSION['email']);
        db_close();
        exit;
    }
}