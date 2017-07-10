<?php
/********************************************************************************************************
 *                   Libreria che contiene funzioni per gestire la Sessione.
 * 
 * ******************************************************************************************************/

/****************
 *   Constants
 ****************/
DEFINE('MAX_INACTIVITY_TIME','120'); //tempo massimo di inattività consentito in secondi


/***********************
 *      Funzioni
 ***********************/

/*
* Distrugge forzatamente la sessione lato server e lato client (cookie)
*/
function destroySession($action){

        $_SESSION=array(); //pulisco array 
    
        //distruggi il cookie associato alla sessione
        if (ini_get("session.use_cookies")) { // the cookie is used to expose the identifier of the session to the remote browse
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600*24, $params["path"], /*trick: imposto tempo a valore negativo per uccidere il cookie*/
                    $params["domain"], $params["secure"], $params["httponly"]);
        }

        //distruggi la sessione
        session_destroy();      

        if($action == "exit"){ //exit action
            
            // redirect client to login/default page
            echo "SESSION_ENDED";
            exit; // IMPORTANT to avoid further output from the script  
        
        } else return; //default action
}


/*
* Testa se la sessione è scaduta
*/
function testSession(){

    session_start(); //creates a session or resumes the current one (using the id in the http request) 
    
    if (isset($_SESSION['time'])){
        
        
        $t = time(); //tempo attuale
        $t0 = $_SESSION['time']; //ultimo aggiornamento della sessione
        $delta = ($t-$t0); // tempo dall'ultimo aggiornamento

        //la sessione deve scadere. è passato troppo tempo dall'ultima azione dell'utente
        if($delta > MAX_INACTIVITY_TIME){
            destroySession("exit");
        } else {
            echo "SESSION_ALIVE";
        }

    }

}

/*
* Inizializza la sessione, se è già presente, aggiorna il timestamp dell'ultima modifica
*/
function startOrUpdateSession(){

    session_start(); //creates a session or resumes the current one (using the id in the http request) 

    $_SESSION['time']=time();
}


//end
?>