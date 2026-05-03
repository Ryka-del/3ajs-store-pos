<?php
include 'db.php';
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store_db";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}



// Get filter from query string
$range = $_GET['range'] ?? 'today';
$start = $_GET['start'] ?? null;
$end = $_GET['end'] ?? null;

$where = "1"; // default (always true)
$paymentTypeColumn = mysqli_query($conn, "SHOW COLUMNS FROM orders LIKE 'payment_type'");
$hasPaymentType = $paymentTypeColumn && mysqli_num_rows($paymentTypeColumn) > 0;
$paymentTypeSelect = $hasPaymentType ? "o.payment_type" : "'pay' AS payment_type";

switch ($range) {
    case 'today':
        $where = "DATE(o.created_at) = CURDATE()";
        break;

    case '7d':
        $where = "o.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        break;

    case 'month':
        $where = "YEAR(o.created_at) = YEAR(CURDATE()) 
                  AND MONTH(o.created_at) = MONTH(CURDATE())";
        break;

    case 'year':
        $where = "YEAR(o.created_at) = YEAR(CURDATE())";
        break;

    case 'custom':
        if (!empty($start) && !empty($end)) {
            $startDate = mysqli_real_escape_string($conn, $start);
            $endDate = mysqli_real_escape_string($conn, $end);
            $where = "DATE(o.created_at) BETWEEN '$startDate' AND '$endDate'";
        }
        break;
}

// Build query
$query = "SELECT 
            o.id AS order_id, 
            o.user_id, 
            o.total_amount, 
            o.payment_amount,
            $paymentTypeSelect, 
            DATE_FORMAT(o.created_at, '%M %d, %Y %h:%i %p') AS formatted_date,
            oi.quantity, 
            oi.price AS item_price, 
            p.name AS product_name
          FROM orders o
          JOIN order_items oi ON o.id = oi.order_id
          JOIN products p ON oi.product_id = p.id
          WHERE $where
          ORDER BY o.created_at DESC, o.id, oi.id";

$result = mysqli_query($conn, $query);

$orders = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $order_id = $row['order_id'];
        if (!isset($orders[$order_id])) {
            $orders[$order_id] = [
                'user_id' => $row['user_id'],
                'total' => $row['total_amount'],
                'paid' => $row['payment_amount'],
                'payment_type' => $row['payment_type'], // ✅ NEW field
                'date' => $row['formatted_date'], // make sure you SELECT this alias in query
                'items' => []
            ];
        }
        $orders[$order_id]['items'][] = [
            'name' => $row['product_name'],
            'qty' => $row['quantity'],
            'price' => $row['item_price']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body id="page-transition"
    class="ui-app-shell flex bg-[#34495E] font-sans text-gray-900 translate-y-5 opacity-0 transition-all duration-500">

    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <div class="flex-1">
        
    </div>
    <div class="ui-main-panel bg-gray-100 m-6 w-full p-4 space-y-4 rounded-2xl">
        <?php include __DIR__ . '/includes/topbar.php'; ?>
        <h1 class="section-heading reveal max-lg:text-2xl xl:text-4xl font-bold text-gray-800 mb-4">Transaction History</h1>
        <div class="flex justify-between items-center">
            <div class="">
                <h2 class="text-2xl font-semibold mb-2">All Transactions</h2>
                <p class="text-gray-600 mb-4">Review all past transactions made in the store.</p>
            </div>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <!-- Left side: range filter -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full max-w-lg">
                    <select name="range" id="rangeSelect"
                        class="ui-select border border-gray-300 rounded-full px-4 py-2 text-sm bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        <option value="today" <?php echo $range === 'today' ? 'selected' : ''; ?>>Today</option>
                        <option value="7d" <?php echo $range === '7d' ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="month" <?php echo $range === 'month' ? 'selected' : ''; ?>>This Month</option>
                        <option value="year" <?php echo $range === 'year' ? 'selected' : ''; ?>>This Year</option>
                        <option value="custom" <?php echo $range === 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                    </select>

                    <!-- Custom Date Range (hidden by default) -->
                    <div id="customDateWrapper"
                        class="hidden bg-white border border-gray-200 rounded-xl p-3 flex items-center gap-3 shadow-sm">
                        <div class="flex flex-col">
                            <label for="startDate" class="text-xs font-medium text-gray-600">Start</label>
                            <input type="date" name="start" id="startDate"
                                value="<?php echo isset($_GET['start']) ? $_GET['start'] : ''; ?>"
                                class="ui-input border border-gray-300 rounded-lg px-2 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <div class="flex flex-col">
                            <label for="endDate" class="text-xs font-medium text-gray-600">End</label>
                            <input type="date" name="end" id="endDate"
                                value="<?php echo isset($_GET['end']) ? $_GET['end'] : ''; ?>"
                                class="ui-input border border-gray-300 rounded-lg px-2 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <!-- Right side: print button -->
                <button onclick="window.print()"
                    class="ui-accent-btn bg-purple-600 hover:bg-purple-700 text-white px-2 py-2 rounded-full shadow-md text-sm font-medium transition w-full">
                    🖨️ Print Transactions
                </button>
            </div>
        </div>
        <div class="ui-soft-card ui-glow-ring bg-gray-300 p-6 rounded-3xl h-[calc(100vh-200px)]  overflow-y-auto">
            <?php if (!empty($orders)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 ">
                    <?php foreach ($orders as $id => $order): ?>
                        <div
                            class="ui-soft-card tilt-card reveal relative bg-white rounded-2xl shadow-sm hover:shadow-xl transition-transform transform group overflow-hidden hover:-translate-y-2 border border-gray-100 p-4 pb-12">

                            <!-- Header -->
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-semibold text-gray-800">#<?= htmlspecialchars($id) ?></h3>
                                <span class="text-xs text-gray-500"><?= htmlspecialchars($order['date']) ?></span>
                            </div>

                            <!-- Customer -->
                            <div class=" flex items-center justify-between text-xs text-gray-500 mb-3">
                                <span class="font-medium text-gray-700">👤 Customer ID: <?= htmlspecialchars($order['user_id']) ?></span>
                                    <!-- Status Badge -->
                            <div class=" justify-end flex">
                                <?php if ($order['total'] == $order['paid']): ?>
                                    <span
                                        class="inline-block px-3 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">✅
                                        Fully Paid</span>
                                <?php elseif ($order['paid'] > 0 && $order['paid'] < $order['total']): ?>
                                    <span
                                        class="inline-block px-3 py-1 text-xs font-semibold bg-yellow-100 text-yellow-700 rounded-full">⚠️
                                        Partially Paid</span>
                                <?php elseif ($order['payment_type'] === 'debt'): ?>
                                    <span
                                        class="inline-block px-3 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded-full">💳
                                        Debt</span>
                                <?php else: ?>
                                    <span
                                        class="inline-block px-3 py-1 text-xs font-semibold bg-gray-100 text-gray-600 rounded-full">Pending</span>
                                <?php endif; ?>
                            </div>
                            </div>

                            <!-- Products (light card) -->
                            <div class="bg-gray-50 rounded-lg p-3 mb-3">
                                <p class="font-semibold text-gray-700 text-sm border-b border-gray-200 pb-2 mb-2">🛒 Products
                                </p>
                                <div class="space-y-2 text-xs text-gray-700">
                                    <?php foreach ($order['items'] as $item): ?>
                                        <div class="flex justify-between items-start">
                                            <div class="pr-2 truncate">
                                                <?= htmlspecialchars($item['name']) ?> <span class="text-gray-500">×
                                                    <?= (int) $item['qty'] ?></span>
                                            </div>
                                            <div class="font-medium whitespace-nowrap">
                                                ₱<?= number_format($item['price'] * $item['qty'], 2) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Totals -->
                            <div class="space-y-2 text-sm text-gray-700 mt-3 border-t pt-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total</span>
                                    <span class="font-bold text-blue-600">₱<?= number_format($order['total'], 2) ?></span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-gray-600">Paid</span>
                                    <span class="text-green-600 font-semibold">₱<?= number_format($order['paid'], 2) ?></span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-gray-600">Balance</span>
                                    <span
                                        class="font-medium <?= ($order['total'] - $order['paid'] > 0) ? 'text-red-600' : 'text-gray-500' ?>">
                                        ₱<?= number_format(max(0, $order['total'] - $order['paid']), 2) ?>
                                    </span>
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-gray-600">Change</span>
                                    <span
                                        class="font-medium <?= ($order['paid'] - $order['total'] > 0) ? 'text-green-600' : 'text-gray-500' ?>">
                                        ₱<?= number_format(max(0, $order['paid'] - $order['total']), 2) ?>
                                    </span>
                                </div>
                            </div>

                            

                            <!-- Edit button (bottom; reserved space via pb-12 above) -->
                            <div class="absolute bottom-4 inset-x-4 flex justify-center pointer-events-none">
                                <button onclick="openEditModal(<?= (int) $id ?>)"
                                    class="pointer-events-auto px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-medium rounded-lg shadow-md opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300">
                                    ✏️ Edit Receipt
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Empty state centered -->
                <div class="flex flex-col justify-center items-center h-64 w-full bg-white rounded-xl shadow-inner">
                    <i class="fa-solid fa-cart-shopping text-4xl mb-2 text-[#3498DB]"></i>
                    <p class="text-gray-600">No Transaction found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Modal (hidden by default) -->
        <div id="editModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
        <div class="ui-modal-panel bg-white w-full max-w-lg rounded-2xl shadow-2xl transform transition-all scale-95 opacity-0 duration-300"
            id="editModalContent">

            <!-- Header -->
            <div class="flex justify-between items-center border-b px-6 py-4">
                <h2 class="text-xl font-bold text-gray-800">🧾 Edit Receipt</h2>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition">
                    ✖
                </button>
            </div>

            <!-- Form -->
            <form action="edit_receipt.php" method="POST" class="p-6 space-y-5">
                <input type="hidden" name="order_id" id="editOrderId">

                <!-- Preserve filters -->
                <input type="hidden" name="range" value="<?= htmlspecialchars($range) ?>">
                <?php if ($range === 'custom'): ?>
                    <input type="hidden" name="start" value="<?= htmlspecialchars($start) ?>">
                    <input type="hidden" name="end" value="<?= htmlspecialchars($end) ?>">
                <?php endif; ?>

                <!-- Paid Amount -->
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Paid Amount (₱)</label>
                    <input type="number" step="0.01" name="amount" id="editAmount"
                        class="ui-input w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                </div>

                <!-- Payment Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Payment Type</label>
                    <select name="payment_type" id="editPaymentType"
                        class="ui-select w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                        required>
                        <option value="pay">✅ Fully Paid</option>
                        <option value="debt">⏳ Debt</option>
                    </select>
                </div>

                <!-- Footer -->
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2 rounded-lg bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium shadow hover:shadow-lg hover:scale-105 transition">
                        💾 Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        const ordersData = <?= json_encode($orders) ?>;
    </script>
</body>

</html>
