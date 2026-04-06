<?php
session_start();
include("config/db.php");

// ================= ADMIN LOGIN =================
if(isset($_POST['admin_login'])){
    if($_POST['email']=="admin@gmail.com" && $_POST['password']=="admin123"){
        $_SESSION['role'] = "admin";
        header("Location: admin/dashboard.php");
        exit;
    } else {
        $error = "Invalid Admin Login!";
    }
}

// ================= STAFF LOGIN =================
if(isset($_POST['staff_login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $res = $conn->query("SELECT * FROM staff WHERE email='$email' AND password='$password'");

    if($res->num_rows > 0){
        $staff = $res->fetch_assoc();

        $_SESSION['role'] = "staff";
        $_SESSION['staff_name'] = $staff['name'];
        $_SESSION['staff_id'] = $staff['id'];

        header("Location: staff/dashboard.php");
        exit;
    } else {
        $error = "Invalid Staff Login!";
    }
}

// ================= STAFF REGISTER =================
if(isset($_POST['staff_register'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $conn->query("
        INSERT INTO staff(name, email, password)
        VALUES('$name','$email','$password')
    ");

    $success = "Registration Successful! Please Login.";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(to right, #667eea, #764ba2);
    height: 100vh;
}

.card {
    border-radius: 15px;
}

.toggle-btn {
    cursor: pointer;
    color: blue;
}
</style>

</head>

<body class="d-flex justify-content-center align-items-center">

<div class="container">
<div class="row justify-content-center">

<?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
<?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

<!-- ADMIN CARD -->
<div class="col-md-4">
<div class="card p-4 shadow">

<h4 class="text-center">Admin Login</h4>

<form method="POST">

<input type="email" name="email" placeholder="Email" class="form-control mb-3" required>
<input type="password" name="password" placeholder="Password" class="form-control mb-3" required>

<button name="admin_login" class="btn btn-dark w-100">Login</button>

</form>

</div>
</div>

<!-- STAFF CARD -->
<div class="col-md-4">
<div class="card p-4 shadow">

<h4 class="text-center" id="staffTitle">Staff Login</h4>

<!-- LOGIN FORM -->
<form method="POST" id="loginForm">

<input type="email" name="email" placeholder="Email" class="form-control mb-3" required>
<input type="password" name="password" placeholder="Password" class="form-control mb-3" required>

<button name="staff_login" class="btn btn-primary w-100">Login</button>

<p class="text-center mt-2">
<span class="toggle-btn" onclick="showRegister()">New user? Register</span>
</p>

</form>

<!-- REGISTER FORM -->
<form method="POST" id="registerForm" style="display:none;">

<input type="text" name="name" placeholder="Name" class="form-control mb-3" required>
<input type="email" name="email" placeholder="Email" class="form-control mb-3" required>
<input type="password" name="password" placeholder="Password" class="form-control mb-3" required>

<button name="staff_register" class="btn btn-success w-100">Register</button>

<p class="text-center mt-2">
<span class="toggle-btn" onclick="showLogin()">Already have account? Login</span>
</p>

</form>

</div>
</div>

</div>
</div>

<script>
function showRegister(){
    document.getElementById("loginForm").style.display="none";
    document.getElementById("registerForm").style.display="block";
    document.getElementById("staffTitle").innerText="Staff Register";
}

function showLogin(){
    document.getElementById("loginForm").style.display="block";
    document.getElementById("registerForm").style.display="none";
    document.getElementById("staffTitle").innerText="Staff Login";
}
</script>

</body>
</html>