<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>

<!-- Topbar -->
<div class="ui-mobilebar w-full flex items-center justify-between py-4 lg:hidden ">

    <!-- Logo/Title -->
    <div class="flex items-center space-x-4">
        <h1 class="text-2xl font-bold">Store Management</h1>
    </div>

    <!-- User Info -->
    <div class="flex items-center space-x-4">


        <!-- Hamburger Menu Button -->
        <button id="menuToggle" class="text-2xl hover:text-gray-300 transition">
            <i class="bx bx-menu"></i>
        </button>
    </div>
</div>

<!-- Mobile Navigation Menu -->
<div id="navMenu" class="ui-mobilemenu bg-white w-full hidden shadow-lg">
    <div class="flex items-center space-x-3">
        <img src="https://api.dicebear.com/7.x/initials/svg?seed=<?php echo urlencode($_SESSION['fullname']); ?>"
            alt="User Avatar" class="w-10 h-10 rounded-full ml-6 border-2 border-gray-600">
        <span class="text-lg font-semibold"><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
    </div>
    <nav class="flex flex-col space-y-1 p-4">
        <a href="home.php" class="ui-nav-link flex items-center space-x-3 p-3 rounded-lg hover:bg-[#2980B9] transition">
            <i class='bx bx-home-alt text-2xl'></i>
            <span>Home</span>
        </a>
        <a href="dashboard2.php" class="ui-nav-link flex items-center space-x-3 p-3 rounded-lg hover:bg-[#2980B9] transition">
            <i class='bx bx-grid-column-left text-2xl'></i>
            <span>Dashboard</span>
        </a>
        <a href="products.php" class="ui-nav-link flex items-center space-x-3 p-3 rounded-lg hover:bg-[#2980B9] transition">
            <i class="bx bx-package text-2xl"></i>
            <span>Products</span>
        </a>
        <a href="transaction.php" class="ui-nav-link flex items-center space-x-3 p-3 rounded-lg hover:bg-[#2980B9] transition">
            <i class="bx bx-list-ul-square text-2xl"></i>
            <span>Transactions</span>
        </a>
        <a href="index.php" class="ui-nav-link flex items-center justify-center space-x-3 p-3 rounded-lg bg-red-600 hover:bg-red-700 transition">
            <i class='bx bx-log-out text-2xl'></i>
            <span>Logout</span>
        </a>
    </nav>
</div>

<script src="assets/js/script.js"></script>
