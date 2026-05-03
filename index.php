<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = md5($_POST['password']); // simple hashing for demonstration

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
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avocart Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body id="page-transition" class="avocart-login min-h-screen translate-y-5 opacity-0 transition-all duration-500">
    <main class="avocart-login__shell">
        <section class="avocart-login__brand reveal">
            <div class="avocart-logo-mark">
                <img src="assets/images/avocart.png" alt="Avocart logo">
            </div>
            <p class="avocart-kicker">Avocart</p>
            <div class="avocart-moving-text" aria-label="Avocart brand message">
                <span>Fresh sales, smooth checkout.</span>
                <span>Smart shelves for sari-sari store.</span>
                <span>Track stock. Serve fast. Grow fresh.</span>
                <span>Avocart keeps your store moving.</span>
            </div>
        </section>

        <section class="avocart-login__panel reveal">
            <form method="POST" class="avocart-login__form">
                <img src="assets/images/avocart.png" alt="Avocart logo" class="avocart-form-logo">
                <h1>Welcome back</h1>
                <p>Log in to Avocart</p>

                <?php if (isset($error)): ?>
                    <div class="avocart-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <label for="username">Username</label>
                <div class="avocart-input-wrap">
                    <i class="fa fa-user"></i>
                    <input type="text" id="username" name="username" required autocomplete="username">
                </div>

                <label for="password">Password</label>
                <div class="avocart-input-wrap">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>

                <a href="" class="avocart-forgot">Forgot Password</a>
                <button type="submit">Login</button>
            </form>
        </section>
    </main>
    <script src="assets/js/script.js"></script>
</body>
</html>
