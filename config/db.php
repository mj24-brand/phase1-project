<?php
$conn=mysqli_connect("localhost","root","","hotel_restaurant");
if(!$conn){
    die("Connection Failed:" .mysqli_connect_error());
}
?>