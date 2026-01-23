<?php
session_start();

$correct_password = "12345"; // tu contraseña

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = $_POST["password"] ?? "12345";

    if ($input === $correct_password) {
        $_SESSION["calc_access"] = true;
        header("Location: ../calculator/index.php");
        exit;
    } else {
        $error = "Password incorrecto.";
    }
}
?>


<!DOCTYPE html>
<html>
<body style="font-family: Arial; padding:40px; text-align:center;">

<h2>🔐 Enter Calculator Password</h2>

<form method="POST">
    <input type="password" name="password" 
           placeholder="Password" 
           style="padding:10px; font-size:16px;">
    <br><br>
    <button type="submit" 
            style="padding:10px 25px; font-size:16px;">
        Access
    </button>
</form>

<p style="color:red;"><?= $error ?? "" ?></p>

</body>
</html>
