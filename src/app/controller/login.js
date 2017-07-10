/****************************************
 * Controller of the login.html view    *
 ****************************************/


/************************
 * Functions
 */

//check if the password contains a digit, an alphatecical char and a case char
function checkPassword(password) {

    var valid = true;

    if (password.length < 2)
        valid = false;

    var r1 = new RegExp("[0-9]+", "i"); //matches a digit
    var r2 = new RegExp("[a-z]+", "i"); //matches an alphabetical char
    var r3 = new RegExp("[A-Z]+", "i"); //matches an alphabetical char (case)

    if (!r1.test(password) || !r2.test(password) || !r3.test(password))
        valid = false;

    return valid;
}


/**
 * Functions that shows alerts, popups and other messages
 */

function showInvalidPassword() {
    console.log("[debug] passoword not well formatted");
    alert("Your password must contain:\n\n- a digit\n- an alphabetical character\n- a case alphabetical character");
}

function showLoginFailed() {

    console.log("[debug] login failed, showing alert to user");
    alert("Wrong user credentials or email already used.");
}



/************************
 * Callbacks definition
 */

//Callback for the loginForm ajax http request
var loginCallback = function (responseText) {

    console.log("[debug] Login attempt response: " + responseText);

    if (responseText == "UNACCEPTED_CREDENTIALS" || responseText == "DB_ERROR") {

        showLoginFailed(); //show alert for wrong user credentials

    } else if (responseText == "LOGIN_OK" || responseText == "REGISTRATION_OK") {

        //show admin panel instead of login panel
        injectAdminTemplate(); //session started, show all the admin functionalities
       
    }
}


/************************************************
 *     Here starts the Controller execution
 * 
 ************************************************/
$(document).ready(function () {

    /**
     * Registering event handlers after 0.5s just to be sure the DOM is fully loaded 
     */
    setTimeout(function () {

            console.log("[debug] login.js - start to attach handlers to events.")

            /**********
             * Handler for "submit" event of the "loginForm"
             * 
             * send the form via AJAX 
             */
            $("#loginForm").submit(function (event) {

                event.preventDefault(); // stops the default action="/"

                //check if password is well formatted
                if (!checkPassword($("#password").val())) {

                    showInvalidPassword();

                } else {

                    var clickedButton = document.activeElement.id;
                    var serializedForm = $('#loginForm').serialize() + "&action=" + clickedButton; //append additional data to the serialized form

                    //saving email into front end model
                    user.email = $("#email").val();

                    console.log("[debug] sending the serialized data: " + serializedForm);

                    $.ajax({
                        url: 'src/php/auth.php',
                        type: 'POST',
                        data: serializedForm,
                        success: loginCallback
                    });
                }
            });


            //end of handler registration
            console.log("[debug] login.js ctrl - all handlers have been attached.")
        },
        500
    );


})