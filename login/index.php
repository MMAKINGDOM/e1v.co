<?php
session_start();

// ...
$USER_FILE = "/var/www/data/test.json"; // OUT OF WEBROOT:]

// js destroyes teh session. logout will have a value of 1. the value is not necessary. the only necessary thing is that the logout parameter should be there.
if(isset($_GET['logout'])) {
    session_destroy();
    header("Location: login");
    exit;
}

// CSRF after login. fix it later...
$loginError = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $users = json_decode(file_get_contents($USER_FILE), true);
    $user = $_POST['username'];
    $pass = $_POST['password'];

    if (isset($users[$user]) && $pass === $users[$user]) {
        $_SESSION['logged_in'] = $user;
        header("Location: GREENFLAGS/");
        exit;
    } else {
        $loginError = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<style>
body { background: #1b1b1b; color: #fff; font-family: 'Roboto', sans-serif; display:flex; justify-content:center; align-items:center; height:100vh; margin:0;}
.login-container { background: rgb(37,33,33); padding:30px; border-radius:10px; box-shadow:0 0 20px rgba(0,0,0,0.5);}
input { width:100%; padding:10px; margin:10px 0; border-radius:5px; border:none;}
button { width:100%; padding:10px; background: darkgoldenrod; color:#fff; border:none; border-radius:5px; cursor:pointer;}
button:hover { background: rgb(134,99,8);}
.error { color: red; margin-bottom:10px; }
</style>
</head>
<body>
<div class="login-container">
    <h2>Login</h2>
    <?php if($loginError) echo "<div class='error'>$loginError</div>"; ?>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
