<?php
// home.php
session_start();
include "db.php"; // database connection

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Fetch categories
$query = $conn->query("SELECT * FROM categories ORDER BY name ASC");

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";

// Base query
$sql = "SELECT * FROM products";

// Add WHERE if searching
if (!empty($search)) {
    $sql .= " WHERE name LIKE '%$search%'";
}

$sql .= " ORDER BY (quantity <=0) ASC, name ASC";


$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/@zxing/library@latest"></script>
</head>

<body id="page-transition"
    class="flex bg-[#34495E] xl:min-h-screen md:max-h-screen translate-y-5 opacity-0 transition-all duration-500">
    <?php include 'includes/sidebar.php'; ?>
    <!-- Page Loader -->
    <div id="page-loader" class="fixed inset-0 flex items-center justify-center bg-white z-50">
        <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Full Page Wrapper -->
    <div class="w-full p-0 m-6 overflow-hidden bg-gray-100 rounded-3xl">
        <!-- New div at the top of the full page wrapper -->
        <?php include 'includes/topbar.php'; ?>

        <div class="flex flex-col md:flex-row gap-6 h-full ">

            <!-- Left Section -->
            <div class="flex flex-col w-full md:w-2/3 h-screen p-4 space-y-4">

                <!-- Category + Search -->
                <div
                    class="flex flex-col sm:flex-row max-md:flex-col-reverse rounded-2xl justify-between sm:px-0 py-4 sm:py-2">
                    <h3 class="xl:text-3xl sm:text-3xl max-md:text-2xl font-bold max-md:mt-4 text-start sm:mb-0">Choose
                        Category</h3>
                    <form method="GET" class="relative w-full sm:w-72">
                        <i
                            class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-[#3498DB]"></i>
                        <input type="text" name="search" placeholder="Search..."
                            value="<?php echo htmlspecialchars($search); ?>"
                            class="w-full p-2 pl-10 pr-10 rounded-lg text-black border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <!--<button type="submit"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                            <i class="fa-solid fa-barcode absolute right-3 top-1/2 -translate-y-1/2 text-[#3498DB]"></i>
                        </button> -->
                    </form>
                </div>

                <!-- Categories Row -->
                <div class="relative w-full">
                    <button onclick="scrollCategories(-200)"
                        class="absolute left-0 top-1/2 -translate-y-1/2 bg-gray-700 text-white p-2 rounded-full shadow hover:bg-[#2980B9] z-10 hidden lg:flex">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>

                    <div id="categoriesRow" class="flex space-x-4 overflow-x-auto scroll-smooth py-2 scrollbar-hide">
                        <?php while ($row = $query->fetch_assoc()): ?>
                            <div class="flex-shrink-0 cursor-pointer text-center p-4 bg-gray-100 rounded-2xl hover:bg-[#2980B9] transition min-w-[100px]"
                                onclick="filterProducts('<?php echo $row['id']; ?>')">
                                <div
                                    class="w-12 h-12 flex items-center justify-center bg-white rounded-full shadow mx-auto">
                                    <i class='bx <?php echo $row['icon'] ?? "bx-grid"; ?> text-2xl text-[#3498DB]'></i>
                                </div>
                                <span class="mt-2 block font-medium text-gray-800 whitespace-nowrap">
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </span>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <button onclick="scrollCategories(200)"
                        class="absolute right-0 top-1/2 -translate-y-1/2 bg-gray-700 text-white p-2 rounded-full shadow hover:bg-gray-600 z-10 hidden lg:flex">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>

                <!-- Gray Box -->
                <div
                    class="bg-gray-200 flex rounded-2xl flex items-start justify-start xl:h-[34rem] md:max-h-screen xl:h-screen text-gray-700 overflow-y-auto">
                    <div id="product-grid"
                        class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-4 gap-4 w-full p-4 md:mb-10 ">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($product = $result->fetch_assoc()): ?>
                                <?php
                                $qty = (int) $product['quantity'];
                                $is_oos = $qty <= 0;
                                ?>
                                <div class="bg-white p-4 rounded-lg shadow hover:shadow-lg transition cursor-pointer flex flex-col <?php echo $is_oos ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''; ?>"
                                    <?php if (!$is_oos): ?> onclick="addToCart(
                                    '<?php echo $product['id']; ?>',
                                    '<?php echo htmlspecialchars($product['name']); ?>',
                                    '<?php echo $product['price']; ?>',
                                    '<?php echo htmlspecialchars($product['image_url']); ?>'
                                    )" <?php endif; ?>>
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>"
                                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                                        class="w-full h-32 object-cover rounded-lg mb-4">
                                    <h4 class="text-lg font-semibold mb-2 "><?php echo htmlspecialchars($product['name']); ?>
                                    </h4>
                                    <div class="mt-auto flex items-center justify-between pt-2">
                                        <span
                                            class="font-bold text-[#3498DB]">₱<?php echo number_format($product['price'], 2); ?></span>
                                        <span
                                            class="mt-1 text-xs px-2 py-1 rounded <?php echo ((int) $product['quantity'] <= 0) ? 'bg-red-100 text-red-700' : (((int) $product['quantity'] <= 5) ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700'); ?>">
                                            <?php echo (int) $product['quantity'] <= 0 ? 'Out of stock' : 'Stock: ' . (int) $product['quantity']; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div>
                                <i class="fa-solid fa-cart-shopping text-4xl mb-2 text-[#3498DB] text-center"></i>
                                <p class="text-center text-gray-600 col-span-full">No products found.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Section -->
            <div
                class="bg-gray-200 w-full md:w-1/3 md:max-h-screen mt-3 mr-3 mb-3 rounded-3xl p-4 flex flex-col xl:max-h-screen">
                <!-- flex-col + fixed height for scrollable content -->

                <!-- Header -->
                <div class="grid grid-cols-2 bg-gray-200">
                    <div>
                        <h3 class="xl:text-xl md:text-md font-bold">Transaction List</h3>
                        <p class="md:text-xs xl:text-md">Transaction Id: <span
                                id="last-transaction-id"><?= isset($_SESSION['last_order_id']) ? $_SESSION['last_order_id'] : '-' ?></span>
                        </p>
                    </div>
                    <div class="text-right">
                        <button type="button" onclick="openScanModal()">
                            <i class='bx bx-barcode text-xl text-blue-500 border-1 p-4 rounded-lg shadow-md'></i>
                        </button>
                        <button type="button" onclick="clearCart()">
                            <i class='bx bx-trash text-xl text-red-500 border-1 p-4 rounded-lg shadow-md'></i>
                        </button>
                    </div>
                </div>
                <hr class="w-full border-t border-gray-400 my-2">

                <!-- Scan Modal -->
                <div id="scanModal"
                    class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center hidden z-50">
                    <div class="bg-white rounded-3xl shadow-2xl w-[28rem] max-w-[90vw] p-6">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-xl font-bold">Scan Barcode</h2>
                            <button onclick="closeScanModal()" class="text-gray-500 hover:text-black">✖</button>
                        </div>
                        <div id="homeScannerContainer" class="w-full rounded-xl overflow-hidden bg-black">
                            <video id="homeScannerPreview" autoplay playsinline
                                class="w-full h-64 object-cover"></video>
                        </div>
                        <div class="mt-3 text-sm text-gray-500">
                            Point your camera at the product barcode. It will be added to the cart automatically.
                        </div>
                    </div>
                </div>

                <h3 class="font-bold mb-2">Item list</h3>

                <!-- Scrollable Item List -->
                <div id="cart-items"
                    class="item-list bg-white flex-1 flex flex-col items-center justify-start rounded-2xl mt-4 space-y-2 p-2 overflow-y-auto">
                    <div class="flex flex-col items-center justify-center text-gray-400 py-8">
                        <i class="fa-solid fa-cart-shopping text-4xl mb-2"></i>
                        <p class="text-sm">No items yet...</p>
                    </div>
                </div>

                <!-- Total Price + Done Button (Sticky Bottom) -->
                <div class="mt-4">
                    <div class="flex justify-between items-center mb-4 px-2">
                        <h3 class="text-lg font-bold">Total:</h3>
                        <p id="total-price" class="text-lg font-bold">₱0.00</p>
                    </div>

                    <!-- Checkout Button -->
                    <button type="button" onclick="openCheckoutModal()"
                        class="w-full bg-[#3498DB] hover:bg-[#2980B9] text-white font-bold py-2 px-4 rounded-2xl shadow-md transition duration-200">
                        Checkout
                    </button>
                </div>
                <!-- Modern Modal -->
                <div id="checkoutModal"
                    class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center hidden z-50">
                    <div
                        class="bg-white rounded-3xl shadow-2xl w-96 p-6 transform transition-all duration-300 scale-95">
                        <!-- Header -->
                        <h2 class="text-2xl font-bold text-gray-800 mb-2 text-center">Checkout</h2>
                        <p class="text-gray-500 text-center mb-6">Enter the payment amount for the items</p>

                        <!-- Total Price Display -->
                        <div class="flex justify-between items-center bg-gray-100 p-3 rounded-xl mb-4">
                            <span class="font-medium text-gray-700">Total:</span>
                            <span id="modal-total" class="font-bold text-gray-900 text-lg">₱0.00</span>
                        </div>

                        <!-- Payment Type -->
                        <label for="paymentType" class="block text-sm font-medium text-gray-700 mb-1">Payment
                            Type</label>
                        <select id="paymentType"
                            class="w-full border border-gray-300 rounded-xl p-3 mb-4 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent text-gray-800">
                            <option value="pay">Pay Now</option>
                            <option value="debt">Debt (Record without payment)</option>
                        </select>

                        <!-- Payment Input -->
                        <input id="paymentInput" type="number" placeholder="Enter amount"
                            class="w-full border border-gray-300 rounded-xl p-3 mb-6 focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent text-gray-800 text-lg">

                        <!-- Change Display -->
                        <div class="flex justify-between items-center bg-gray-50 p-3 rounded-xl mb-6">
                            <span class="font-medium text-gray-700">Change:</span>
                            <span id="modal-change" class="font-bold text-gray-900 text-lg">₱0.00</span>
                        </div>


                        <!-- Buttons -->
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeCheckoutModal()"
                                class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-5 py-2 rounded-xl transition duration-200">Cancel</button>
                            <button type="button" onclick="payNow()"
                                class="bg-gradient-to-r from-purple-400 to-purple-600 hover:from-purple-500 hover:to-purple-700 text-white font-bold px-5 py-2 rounded-xl shadow-lg transition duration-200">Pay</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🔊 Beep sounds  -->
        <audio id="successBeep" src="assets/sounds/success.mp3" preload="auto"></audio>
        <audio id="errorBeep" src="assets/sounds/error.mp3" preload="auto"></audio>
        <script src="assets/js/script.js"></script>
</body>

</html>