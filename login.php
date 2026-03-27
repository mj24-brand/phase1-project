<?php
session_start();
include("config/db.php");

if(isset($_POST['login'])){
    $username=$_POST['username'];
    $password=md5($_POST['password']);

    $query="SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result=mysqli_query($conn,$query);

    if(mysqli_num_rows($result)>0){
        $user_data=mysqli_fetch_assoc($result);
        $role=strtolower(trim($user_data['role']));

        $_SESSION['user_id']=$user_data['id'];
        $_SESSION['user_username']=$user_data['username'];
        $_SESSION['user_fullname']=$user_data['full_name'];
        $_SESSION['role']=$role;
        $_SESSION['user_role']=$role;

        if($role==='admin'){
            $_SESSION['admin']=$user_data['username'];
        }else if($role==='manager'){
            $_SESSION['manager']=$user_data['username'];
        }else if($role==='waiter'){
            $_SESSION['waiter']=$user_data['username'];
        }else if($role==='cashier'){
            $_SESSION['cashier']=$user_data['username'];
        }else{
            $_SESSION['kitchen']=$user_data['username'];
        }

        if($role==='admin'){
            header("Location:Dashboard/Admin/index.php");
        }else if($role==='manager'){
            header("Location:Dashboard/Manager/index.php");
        }else if($role==='waiter'){
            header("Location:Dashboard/Waiter/index.php");
        }else if($role==='cashier'){
            header("Location:Dashboard/Casheir/index.php");
        }else{
            header("Location:Dashboard/Kitchen/index.php");
        }

        exit();
    }else{
        $error="Invalid Username or Password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
</head>
<body class="bg-dark">
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-4">
            <div class="card p-4 shadow">
                <h3 class="text-center">Login</h3>
                <form method="POST">
                <div class="mb-3">
                    <label>Username</label>
                    <input name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button name="login" class="btn btn-primary w-100">Login</button>
                </form>
                <?php if(isset($error)){ ?>
                <div class="alert alert-danger mt-3"><?php echo $error; ?></div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>