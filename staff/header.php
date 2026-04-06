<!DOCTYPE html>
<html>
<head>
<title>Staff Panel</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
body { background:#f5f7fa; }

.sidebar {
    height:100vh;
    width:220px;
    position:fixed;
    background:#343a40;
    padding-top:20px;
}

.sidebar a {
    color:white;
    display:block;
    padding:10px;
    text-decoration:none;
}

.sidebar a:hover {
    background:#495057;
}

.content {
    margin-left:230px;
    padding:20px;
}
</style>

</head>
<body>

<?php include("sidebar.php"); ?>

<div class="content">