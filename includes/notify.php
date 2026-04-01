<?php

if(!function_exists('sendNotification')){

function sendNotification($conn, $msg, $type='system', $receiver_type=null, $receiver_id=null){

    $msg = mysqli_real_escape_string($conn, $msg);

    $conn->query("
        INSERT INTO notifications(message, type, receiver_type, receiver_id)
        VALUES('$msg', '$type', '$receiver_type', '$receiver_id')
    ");
}

}
?>