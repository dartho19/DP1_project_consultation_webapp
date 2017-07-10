/****************************************
 * Controller of the admin.html view    *
 ****************************************/


/************************
 * Functions
 */

//show alert with failed logout
function showLogoutFailed() {
    console.log("[debug] logout failed, showing alert to user");
    alert("There was an error during the Logout attempt. Try Again.");
}

//invia richiesta al backend per verificare se la sessione dell'utente è ancora attiva
var checkInactivity = function () {

    $.ajax({
        url: 'src/php/checkSession.php',
        type: 'POST',
        data: "action=checkInactivity", //serializzo dati a mano
        success: function (responseText) {

            console.log("[debug] session.php response: " + responseText + "\n");

            if (responseText == "SESSION_ENDED") {

                alert("Inactivity period elapsed.\n\nLogin again.")
                window.location.replace("index.php"); //effettua redirect a pagina di login

            }
        }
    });
}

//load from model the current booking for the logged user
var getCurrentBook = function () {

    $.ajax({
        url: 'src/php/getData.php',
        type: 'POST',
        data: "action=getCurrentBook", //handcrafted serialization of the data
        success: function (responseText) {

            console.log("[debug] booking of the logged user: " + responseText);

            var res = responseText.split("&");

            if (res[0] == "" || res[1] == "") {

                //it's not a number, show "-" instead
                $("#userRequestedMin").text("-");
                $("#userAllowedMin").text("-");

            } else {

                user.min_requested = res[0];
                user.min_allowed = res[1];

                $("#userRequestedMin").text(res[0] + " min");
                $("#userAllowedMin").text(res[1] + " min");
            }
        }
    });
}

/************************
 * Callbacks definition
 */

//callback for the adminForm
var userBookingCallback = function (responseText) {

    console.log("[debug] userBookingCallback - risultato ottenuto da book.php: " + responseText);

    if (responseText == "BOOK_OK") {

        getCurrentBook(); //update the admin template
        loadConsultationModel(); //update the consultation model and load again the consultation template

        $("#response").text("Booking ok!");
        $("#response").addClass("my-response-text-ok").removeClass("my-response-text-ko");

    } else if (responseText == "BOOK_KO") {

        getCurrentBook(); //update the admin template
        loadConsultationModel(); //update the consultation model and load again the consultation template

        $("#response").text("It's not possible to book.");
        $("#response").addClass("my-response-text-ko").removeClass("my-response-text-ok");

    } else if (responseText == "ALREADY_BOOKED") {

        $("#response").text("You have already booked a consultation.");
        $("#response").addClass("my-response-text-ko").removeClass("my-response-text-ok");

    } else if (responseText == "UNACCEPTED_INPUT") {

        $("#response").text("Unable to book.");
        $("#response").addClass("my-response-text-ko").removeClass("my-response-text-ok");

        alert("Unable to register your booking. Try again.");
    }

}


//callback for logout button click event
var logoutCallback = function () {

    $.ajax({
        url: 'src/php/auth.php',
        type: 'POST',
        data: "action=logout", //serializzo dati a mano
        success: function (responseText) {

            console.log(responseText);

            if (responseText == "SESSION_ENDED") {

                window.location.replace("index.php"); //effettua redirect a pagina di login

            } else showLogoutFailed();
        }
    });
}

//callback for onclick event on button "deleteBookingButton"
var deleteBookingCallback = function () {

    $.ajax({
        url: 'src/php/book.php',
        type: 'POST',
        data: "action=deleteBooking", //handcrafted data serialization
        success: function (responseText) {

            console.log("[debug] deleteBookingCallback - risultato ottenuto da bid.php: " + responseText);

            if (responseText == "DELETE_BOOKING_OK") {

                getCurrentBook(); //update the admin template
                loadConsultationModel(); //update the consultation model and load again the consultation template

                $("#response").text("Booking successfully deleted!");
                $("#response").addClass("my-response-text-ok").removeClass("my-response-text-ko");

            } else if (responseText == "DELETE_BOOKING_KO") {

                getCurrentBook(); //update the admin template
                loadConsultationModel(); //update the consultation model and load again the consultation template

                $("#response").text("Unable to delete your booking. try again.");
                $("#response").addClass("my-response-text-ko").removeClass("my-response-text-ok");

            } else if (responseText == "NOT_ALREADY_BOOKED") {

                $("#response").text("You don't have a reservation.");
                $("#response").addClass("my-response-text-ko").removeClass("my-response-text-ok");

            } else if (responseText == "UNACCEPTED_INPUT") {

                $("#response").text("Unable to delte the booking.");
                $("#response").addClass("my-response-text-ko").removeClass("my-response-text-ok");

                alert("Unable to delete your booking. Try again.");
            }
        }
    });
}


/************************************************
 *     Here starts the Controller execution
 * 
 ************************************************/
$(document).ready(function () {

    //get user data from db and show them
    getCurrentBook();

    /**********
     * Handler for "submit" event of the "adminForm"
     * 
     * send the form via AJAX 
     */
    $("#adminForm").submit(function (event) {

        event.preventDefault(); // stops the default action="/"

        var serializedForm = $('#adminForm').serialize() + "&action=userBooking"; //serialize data and append action info         

        console.log("[debug] sending the serialized data to book.php: " + serializedForm);

        $.ajax({
            url: 'src/php/book.php',
            type: 'POST',
            data: serializedForm,
            success: userBookingCallback
        });

    });


    /*
     * Effettua controllo sull'inattività ogni secondo
     */
    setInterval(function () {

        checkInactivity();

    }, 1000);


})