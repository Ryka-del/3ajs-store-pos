<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = md5($_POST['password']);//simple hashing for demonstration

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['fullname'] = $user['fullname'];
        header("Location: home.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
    $_SESSION['fullname'] = $user['fullname'];
    var_dump($_SESSION);
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login page</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="flex flex-col min-h-screen">
    <div class="flex flex-col md:flex-row flex-1 mx-auto w-full max-w-full">
        <!-- Left Section: Image -->
        <div class="flex flex-col md:w-1/2 w-full p-4 justify-center bg-blue-400 lg:bg-white">
            <img src="assets/images/login.png" alt="Store Image" class="w-40 h-40 ml-28 md:w-96 md:h-96 lg:mt-10 lg:ml-36 hidden md:block">
            <h2 class="ml-2 lg:ml-40 mt-4 text-white md:text-black text-start">Welcome to the</h2>
            <h2 class="ml-2 lg:ml-40 text-white md:text-black text-start">3AJS Store Manager</h2>
        </div>
        <!-- Right Section: Login Form -->
        <div class="md:w-1/2 w-full bg-gradient-to-r from-blue-500 to-purple-600 flex flex-col justify-center">
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 lg:mt-20 mb-36 rounded justify-center items-center">
                
                <form method="POST" class="bg-white max-w-sm mx-auto mt-10 p-6 border rounded-2xl shadow">
                    <h2 class="text-3xl mb-4 font-bold">Hello!</h2>
                    <h2 class="text-2xl mb-4">Login</h2>
                    <?php if (isset($error)): ?>
                        <div class="bg-red-100 text-red-700 p-2 mb-4 rounded"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <div class="mb-4 relative w-full">
                        <label class="block mb-1" for="username">Username</label>
                        <i class="fa fa-user absolute inset-y-0 left-0 flex items-center mt-6 pt-1 pl-3 text-gray-400"></i>
                        <input type="text" id="username" name="username" required class="w-full pl-10 pr-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div class="mb-4 relative w-full">
                        <label class="block mb-1" for="password">Password</label>
                        <i class="fa-solid fa-lock absolute inset-y-0 left-0 flex items-center mt-6 pt-1 pl-3 pb-4 text-gray-400"></i>
                        <input type="password" id="password" name="password" required class="w-full pl-10 pr-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <a href="" class="text-xs mt-2">Forgot Password</a>
                    </div>
                    <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded">Login</button>
            </div>
        </div>
    </div>
</body>
</html>