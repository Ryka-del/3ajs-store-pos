<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();

}
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>


<div class="sticky top-0 h-screen w-52 bg-[#34495E] text-white  flex flex-col items-center py-6 hidden md:block z-50">

  <!-- User Avatar -->
  <div class="flex flex-col items-center">
    <img src="https://api.dicebear.com/7.x/initials/svg?seed=<?php echo urlencode($_SESSION['fullname']); ?>"
      alt="User Avatar" class="w-20 h-20 rounded-full border-2 border-gray-600 mb-2">
    <h2 class="max-lg:text-lg font-semibold min-md:text-xs"><?php echo htmlspecialchars($_SESSION['fullname']); ?></h2>
  </div>

  <!-- Sidebar Wrapper -->
  <div class="bg-[#34495E] text-white xl:min-h-screen flex flex-col items-center lg:w-auto w-full relative">

    <!-- Hamburger for Mobile -->
    <button id="" class="lg:hidden absolute top-4 left-4 text-white text-2xl">
      <i class="bx bx-menu"></i>
    </button>

    <!-- Navigation -->
    <nav id="" class="flex flex-col space-y-2 mt-2 w-full lg:block hidden">
      <a href="home.php" class="flex flex-col items-center p-3 rounded-lg hover:bg-[#2980B9]">
        <i class='bx bx-home-alt text-5xl mb-1'></i>
        <span>Home</span>
      </a>
      <a href="dashboard2.php" class="flex flex-col items-center p-3 rounded-lg hover:bg-[#2980B9]">
        <i class='bx bx-grid-column-left text-5xl mb-1'></i>
        <span>Dashboard</span>
      </a>
      <a href="products.php" class="flex flex-col items-center p-3 rounded-lg hover:bg-[#2980B9]">
        <i class="bx bx-package text-5xl mb-1 "></i>
        <span>Products</span>
      </a>
      <a href="transaction.php" class="flex flex-col items-center p-3 rounded-lg hover:bg-[#2980B9]">
        <i class="bx bx-list-ul-square mb-1 text-5xl"></i>
        <span>Transactions</span>
      </a>
    </nav>
    <a href="logout.php"
      class="flex items-center justify-center bg-red-600 hover:bg-red-700 text-white px-4 py-2 mt-4 rounded-lg space-x-2">
      <i class='bx bx-log-out text-2xl'></i>
      <span>Logout</span>
    </a>
  </div>

  <!-- Logout Button -->

</div>
<script src="assets/js/script.js"></script>