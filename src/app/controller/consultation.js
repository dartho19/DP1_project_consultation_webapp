/****************************************
 * Controller of the consultation.html view    
 * 
 ****************************************/


/************************
 * Functions
 */

/*
*   Requires data to the backend, once avaible stores them into the model
*
*   send to backend the following action: getConsultationInfo
*/
var loadConsultationModel = function (){

    //carica il modello dal backend
    $.ajax({
        url: 'src/php/getData.php',
        type: 'POST',
        data: "action=getConsultationInfo", //serializzo dati a mano
        success: function (responseText) {

            console.log("[debug] model updated from backend.");

            var users = JSON.parse(responseText); //the response is a serialized JSON
            var timeLeft = 180;

            //clear table
            $('#userInfo tr').remove();

            //popolate table
            $.each(users, function(index, user){

                //calculate time in H:i:s format starting from the timestamp
                var date = new Date(user.start_time*1000); //arguments must be in milliseconds
                var h = date.getHours();
                var m = "0"+date.getMinutes();
                var s = "0"+date.getSeconds();
                var formattedStartTime = h + ':' + m.substr(-2) + ':' + s.substr(-2);

                //append the new row
                $('#userInfo').append("<tr><td>"+user.email+"</td><td>"+user.min_requested+"</td><td>"+user.min_allowed+"</td><td>"+formattedStartTime+"</td></tr>");
                timeLeft = timeLeft - user.min_allowed;
            })

            $("#timeLeft").html(timeLeft+" min");
        }
    });
}
