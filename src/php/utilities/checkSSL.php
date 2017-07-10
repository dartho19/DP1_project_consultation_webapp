<?php
    //Effettua redirect su HTTPS 
    if( empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on'  ){
        
       // header('HTTP/1.1 301 Moved Permanently');
       // header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

       
        //Se viene richiesto uno script NON tramite https termina script immediatamente
        exit();
    } 
?>