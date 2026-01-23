<?php
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($_POST["password"] === "12345") {
        $_SESSION["access_work_report"] = true;
        header("Location: ../employee_work_report/");
        exit();
    } else {
        $error = "❌ Incorrect password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access - Employee Work Report</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #001f54, #a30000);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .box {
            background: white;
            padding: 50px;
            border-radius: 18px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            width: 380px;
            text-align: center;
            animation: fadeIn 0.6s ease;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
            font-size: 1.8rem;
        }

        input {
            width: 100%;
            padding: 14px;
            margin-top: 15px;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }

        button {
            width: 100%;
            padding: 14px;
            margin-top: 20px;
            border: none;
            background: linear-gradient(135deg, #003049, #a30000);
            color: white;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }

        .error {
            margin-top: 15px;
            color: red;
            font-weight: bold;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* HOME BUTTON */
        .home-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: white;
            padding: 10px 18px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: bold;
            color: #333;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            transition: .3s;
            z-index: 99999;
        }
        .home-btn:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
        }

    </style>
</head>

<body>

<!-- 🏠 HOME BUTTON -->
<a href="../index.php" class="home-btn">🏠 Home</a>

    <div class="box">
        <h2>Enter Password</h2>

        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="password" name="password" placeholder="Password">
            <button type="submit">Access</button>
        </form>
    </div>
</body>

</html>
