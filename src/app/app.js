/*******************************
 * Utility Function Definition
 */

//non permettere invio di dati dai form premendo "enter"
function disableEnterKey() {

    $(window).keydown(function (event) {
        if (event.keyCode == 13) {
            event.preventDefault();
            return false;
        }
    });
}


/*******************************
 * Template Injectors
 */


//AUCTION-TEMPLATE
function injectConsultationTemplate() {

    $("#main-content").load("src/app/template/consultation.html"); //carica il template
}

//LOGIN-TEMPALTE
function injectLoginTemplate() {
    $("#left-menu").load("src/app/template/login.html"); //carica il template
}

//ADMIN-TEMPLATE
function injectAdminTemplate() {

    console.log("[debug] user logged in. preparing to show admin panel.");
    $("#left-menu").load("src/app/template/admin.html"); //carica il template
}


/***********************************************
 *          Entry Point of the App
 * 
 ***********************************************/
$(document).ready(function () {

    //Check if cookies are enabled
    if (!navigator.cookieEnabled) {
        $("#container").load("src/app/template/nocookie.html"); //carica il template
    }

    //inject tempaltes and load data from backend
    injectConsultationTemplate(); //lo inserisce nella view
    injectLoginTemplate(); //carica il template del login nel left-menu
    disableEnterKey(); //return non invia il form

    loadConsultationModel(); //load model from backend

    //carica dal backend il valore impostato per bid ogni 3 secondi
    setInterval(function () {
        loadConsultationModel();

    }, 3000);

})