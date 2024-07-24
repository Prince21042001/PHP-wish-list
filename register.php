<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'db_connect.php';

    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        header('Location: login.php');
        exit();
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Wish List Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('background.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
        }
        .register-container {
            position: relative;
            z-index: 1;
            max-width: 400px;
            width: 100%;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .register-container h1 {
            font-size: 2em;
            margin-bottom: 20px;
        }
        .btn-custom {
            width: 100%;
            margin-bottom: 10px;
            border-radius: 20px;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <div class="overlay"></div>
    <div class="register-container">
        <h1 class="text-success">Register</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <form action="register.php" method="POST">
            <div class="form-group">
                <input type="text" class="form-control" name="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <input type="email" class="form-control" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-success btn-custom">Register</button>
        </form>
        <p class="mt-3">Already have an account? <a href="login.php" class="text-success">Login here</a></p>
    </div>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
