<?php
/********************************************************************************************************
 * Gestisce la richiesta di verifica sulla sessione: sessione è scaduta ? se si redirect ad homepage
 * 
 * ******************************************************************************************************/

//error reporting for DEBUG -> to be removed in production
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

/************
 * Includes
 ***********/

// including the session functions
include "utilities/session_utilities.php";


/*********************************************************************
*   Inizio esecuzione script: effettua check sul tempo di inattività
*/

if( isset($_POST['action']) ){

    $action = $_POST['action'];

    if( $action == "checkInactivity" ){

        testSession();        

    }
}


?>