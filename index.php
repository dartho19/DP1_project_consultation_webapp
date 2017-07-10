<?php
    //Effettua redirect su HTTPS 
   /* 
   if( empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on'  ){
        
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        exit();
    }
    */ 
?>

<!doctype html>
<html>
<!-- HEAD -->

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <title>pd1-web-project</title>
    <meta name="description" content="Consultation Web App" />
    <meta name="author" content="giovanni.garifo@polito.it" />
    <meta http-equiv="pragma" content="no-cache" />

    <!-- Imports all styles -->
    <link href="resources/bootstrap.min.css" rel="stylesheet">
    <link href="src/css/custom.css" rel="stylesheet">

    <!--Imports of JQuery -->
    <script src="resources/jquery-3.2.1.js"></script>

    <!-- Import of js scripts-->
    <!-- 1. carica modello -->
    <script src="src/app/model/consultation.js"></script>
    <script src="src/app/model/user.js"></script>
    <!-- 2. carica ctrl -->
    <script src="src/app/controller/consultation.js"></script>
    <!-- 3. carica app -->
    <script src="src/app/app.js"></script>
</head>

<!-- Notifica se javascript non è abilitato -->
<noscript>
    <div>
        <h3 style="padding-top: 20%;" align="center">You don't have JavaScript enabled. Please enable it in your browser settings to use the App.</h3>
        <img style="margin: auto; width: 50%; display: block;" src="img/dinosaur.jpg" alt="there was a dinosaur">
    </div>
    <style type="text/css">
        .my-header, .row {
            display: none !important;
        }

        .container {
            background-color: #f5f5f5 !important;
        }
    </style>
</noscript>

<!-- BODY -->

<body id="container" class="container">

    <!-- header -->
    <div class="row page-header my-header">
        <h1 class="my-title">Consultation Web App</h1>
        <small class="my-sub-title">Here you can book a consultation for the Distributed Programming I course!</small>
    </div>

    <!-- center -->
    <div class="row">

        <!--left menu-->
        <div id="left-menu" class="col-xs-3 my-left-menu">
        </div>

        <!-- content -->
        <div id="main-content" class="col-xs-9 my-main-content">

        </div>

    </div>


    <!-- footer -->
    <div class="row my-footer">
        <h6 style="position: absolute;">This application was developed by
            <a href="https://www.linkedin.com/in/giovannigarifo/">Giovanni Garifo</a>. You can find source code on </h6>
        <a href="https://bitbucket.org/dartho19/pd1-web-project-consultations">
        <img src="img/bitbucket-logo.png" class="my-bitbucket-logo"/>
        </a>
    </div>

</body>

</html>