<?php
session_start();
include "db.php"; // database connection

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}


// Range handling (PHP-driven)
$range = isset($_GET['range']) ? $_GET['range'] : 'month';
$validRanges = ['today', '7d', 'month', 'year', 'custom'];
if (!in_array($range, $validRanges, true))
    $range = 'month';

$today = date('Y-m-d');
if ($range === 'today') {
    $start = $today;
    $end = $today;
} elseif ($range === '7d') {
    $start = date('Y-m-d', strtotime('-6 days'));
    $end = $today;
} elseif ($range === 'year') {
    $start = date('Y-01-01'); // first day of the year
    $end = $today;
} elseif ($range === 'custom') {
    // later you can allow user to pick start & end dates via a datepicker
    $start = isset($_GET['start']) ? $_GET['start'] : $today;
    $end = isset($_GET['end']) ? $_GET['end'] : $today;
} else {
    $start = date('Y-m-01'); // first day of month
    $end = $today;
}

$tx_start = isset($_GET['tx_start']) ? $_GET['tx_start'] : null;
$tx_end = isset($_GET['tx_end']) ? $_GET['tx_end'] : null;

$where = "";
if ($tx_start && $tx_end) {
    $start = $conn->real_escape_string($tx_start);
    $end = $conn->real_escape_string($tx_end);
    $where = "WHERE DATE(created_at) BETWEEN '$start' AND '$end'";
} elseif ($tx_start) {
    $start = $conn->real_escape_string($tx_start);
    $where = "WHERE DATE(created_at) >= '$start'";
} elseif ($tx_end) {
    $end = $conn->real_escape_string($tx_end);
    $where = "WHERE DATE(created_at) <= '$end'";
}

// Default: last 7 days if no filter
if (!$tx_start && !$tx_end) {
    $where = "WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
}

$recent = $conn->query("
    SELECT id, total_amount, created_at
    FROM orders
    $where
    ORDER BY created_at DESC
    LIMIT 50
");

// Revenue (already correct)
$revenue = (float) ($conn->query("
    SELECT IFNULL(SUM(total_amount),0) AS t 
    FROM orders 
    WHERE DATE(created_at) BETWEEN '$start' AND '$end'
")->fetch_assoc()['t'] ?? 0);

// Profit (Revenue - COGS)
$profitQuery = $conn->query("
    SELECT SUM((oi.price - p.cost_price) * oi.quantity) AS profit
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN '$start' AND '$end'
");

$profit = (float) ($profitQuery->fetch_assoc()['profit'] ?? 0);


// CSV export (PHP)
if (isset($_GET['export']) && $_GET['export'] == '1') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment; filename=\"sales_export_{$range}.csv\"");
    echo "Date,Total\n";
    $exp = $conn->query("
        SELECT DATE(created_at) AS d, SUM(total_amount) AS s
        FROM orders
        WHERE DATE(created_at) BETWEEN '$start' AND '$end'
        GROUP BY DATE(created_at)
        ORDER BY d ASC
    ");
    while ($r = $exp->fetch_assoc()) {
        echo $r['d'] . "," . number_format((float) $r['s'], 2, '.', '') . "\n";
    }
    exit;
}

// Cards (PHP)
$total_products = (int) ($conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'] ?? 0);
$transactions = (int) ($conn->query("SELECT COUNT(*) AS c FROM orders WHERE DATE(created_at) BETWEEN '$start' AND '$end'")->fetch_assoc()['c'] ?? 0);
$revenue = (float) ($conn->query("SELECT IFNULL(SUM(total_amount),0) AS t FROM orders WHERE DATE(created_at) BETWEEN '$start' AND '$end'")->fetch_assoc()['t'] ?? 0);

// Low stock query
$threshold = 5;
$low_stock = $conn->query("SELECT name, quantity FROM products WHERE quantity < $threshold");
$low = $conn->query("SELECT COUNT(*) AS c FROM products WHERE quantity <= 5")
    ->fetch_assoc()['c'] ?? 0;

$low = $low_stock->num_rows;

// Dashboard data
$total_products = $conn->query("SELECT COUNT(*) AS count FROM products")->fetch_assoc()['count'];
$today = date("Y-m-d");
$total_sales_today = $conn->query("SELECT IFNULL(SUM(total),0) AS total FROM sales WHERE DATE(date)='$today'")->fetch_assoc()['total'];
$low_stock = $conn->query("SELECT name, quantity FROM products WHERE quantity < 5");
$most_sold = $conn->query("
    SELECT p.name, SUM(si.quantity) as qty
    FROM sale_items si
    JOIN products p ON si.product_id = p.id
    GROUP BY si.product_id
    ORDER BY qty DESC
    LIMIT 1
")->fetch_assoc();

// Series: sales per day (PHP)
$map = [];
$cursor = new DateTime($start);
$endDT = new DateTime($end);
while ($cursor <= $endDT) {
    $map[$cursor->format('Y-m-d')] = 0.0;
    $cursor->modify('+1 day');
}
$resSales = $conn->query("
    SELECT DATE(created_at) AS d, SUM(total_amount) AS s
    FROM orders
    WHERE DATE(created_at) BETWEEN '$start' AND '$end'
    GROUP BY DATE(created_at)
    ORDER BY d ASC
");
while ($row = $resSales->fetch_assoc()) {
    $map[$row['d']] = (float) $row['s'];
}
$labels = array_map(fn($d) => date('M j', strtotime($d)), array_keys($map));
$values = array_values($map);

// Series: top 5 products (PHP)
$resTop = $conn->query("
    SELECT p.name, SUM(oi.quantity) AS qty
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    JOIN products p ON p.id = oi.product_id
    WHERE DATE(o.created_at) BETWEEN '$start' AND '$end'
    GROUP BY oi.product_id
    ORDER BY qty DESC
    LIMIT 5
");
$top_labels = [];
$top_values = [];
while ($t = $resTop->fetch_assoc()) {
    $top_labels[] = $t['name'];
    $top_values[] = (int) $t['qty'];
}

// Recent transactions (PHP)
$recent = $conn->query("SELECT id, total_amount, created_at FROM orders ORDER BY created_at DESC LIMIT 5");

// Helper for range label
function range_label($r)
{
    if ($r === 'today')
        return 'Today';
    if ($r === '7d')
        return 'Last 7 Days';
    return 'This Month';
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="script.js"></script>
</head>

<body id="page-transition"
    class="flex bg-[#34495E] font-sans text-gray-900 translate-y-5 opacity-0 transition-all duration-500 text-sm text-gray-500">
    <?php include 'includes/sidebar.php'; ?>
    <!-- Container -->
    <div class="bg-gray-100 m-6 w-full min-h-screen p-6 space-y-6 rounded-3xl">
        <?php include 'includes/topbar.php'; ?>
        <h2 class="font-bold text-2xl">Dashboard</h2>
        <!-- Sales Overview Header -->
        <section class="flex items-center justify-between space-y-3">
            <div>
                <h2 class="text-lg text-gray-700 font-semibold">Sales Overview</h2>
                <p class="text-gray-500 text-sm">Your current sales summary and activity</p>
            </div>
            <!-- Right: Controls (pure PHP via form) -->
            <form method="GET" class="flex flex-wrap justify-end items-center gap-2">
                <select name="range" id="rangeSelect"
                    class="border border-gray-300 rounded-full px-4 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    <option value="today" <?php echo $range === 'today' ? 'selected' : ''; ?>>Today</option>
                    <option value="7d" <?php echo $range === '7d' ? 'selected' : ''; ?>>Last 7 Days</option>
                    <option value="month" <?php echo $range === 'month' ? 'selected' : ''; ?>>This Month</option>
                    <option value="year" <?php echo $range === 'year' ? 'selected' : ''; ?>>This Year</option>
                    <option value="custom" <?php echo $range === 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                </select>

                <!-- Custom Date Range (hidden by default) -->
                <div id="customDateWrapper"
                    class="bg-gray-50 border border-gray-300 rounded-xl p-3 flex items-center gap-3 shadow hidden">
                    <div class="flex flex-col">
                        <label for="startDate" class="text-xs font-medium text-gray-600">Start</label>
                        <input type="date" name="start" id="startDate"
                            value="<?php echo isset($_GET['start']) ? $_GET['start'] : ''; ?>"
                            class="border border-gray-300 rounded-lg px-2 py-1 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    </div>
                    <div class="flex flex-col">
                        <label for="endDate" class="text-xs font-medium text-gray-600">End</label>
                        <input type="date" name="end" id="endDate"
                            value="<?php echo isset($_GET['end']) ? $_GET['end'] : ''; ?>"
                            class="border border-gray-300 rounded-lg px-2 py-1 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    </div>
                </div>
                <button type="submit"
                    class="bg-purple-600 hover:bg-purple-700 text-white rounded-full px-4 py-2 text-sm flex items-center gap-1 shadow">
                    <i class="bx bx-filter-alt text-lg"></i>
                    <span>Filter</span>

                    <!--</button>
                <a href="?export=1&range=<?php echo htmlspecialchars($range); ?>"
                    class="bg-purple-600 hover:bg-purple-700 border border-gray-300 rounded-full px-4 py-2 text-sm flex items-center space-x-1 hover:bg-gray-100">
                    <i class='bx bx-download text-xl text-gray-700'></i>
                    <span>Export</span>
                </a> -->
            </form>
        </section>

        <!-- Sales Summary Cards -->
        <section class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Total Sales -->
            <div class="bg-purple-600 rounded-xl p-6 text-white flex justify-between items-center">
                <div>
                    <p class="text-sm opacity-70">Total Sales</p>
                    <h3 class="text-3xl font-bold mt-1">₱<?php echo number_format($revenue, 2); ?></h3>
                    <p class="text-xs opacity-75 mt-1">Range: <span><?php echo range_label($range); ?></p>
                </div>
                <div class="bg-white w-12 h-12 mb-8 flex items-center justify-center rounded-full shadow">
                    <i class="bx bx-money text-2xl text-blue-600"></i>
                </div>
            </div>

            <!-- Total Products -->
            <div class="bg-white rounded-xl shadow p-6 flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-600">Total Products</p>
                    <h3 class="text-3xl font-bold mt-1"><?php echo $total_products; ?></h3>
                    <p class="text-xs text-gray-500 mt-1">Inventory overview</span></p>
                </div>
                <div class="bg-yellow-400 w-12 h-12 mb-8 flex items-center justify-center rounded-full text-white">
                    <i class='bx bx-package text-2xl'></i>
                </div>
            </div>

            <!-- Total Transactions -->
            <div class="bg-white rounded-xl shadow p-6 flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-600">Total Transaction</p>
                    <h3 class="text-3xl font-bold mt-1"><?php echo $transactions; ?></h3>
                    <p class="text-xs text-gray-500 mt-1">Completed Transaction</p>
                </div>
                <div class="bg-blue-400 w-12 h-12 mb-8 flex items-center justify-center rounded-full text-white">
                    <i class='bx bx-list-ul-square text-2xl text-white'></i>
                </div>
            </div>

            <!-- Profit Overview -->
            <div class="bg-white rounded-xl shadow p-6 flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-600">Profit Overview</p>
                    <h3 class="text-3xl font-bold mt-1">₱<?php echo number_format($profit, 2); ?></h3>
                    <p class="text-xs text-gray-500 mt-1">Net Profit for <?php echo ucfirst($range); ?></p>
                </div>
                <div class="bg-green-500 w-12 h-12 mb-8 flex items-center justify-center rounded-full text-white">
                    <i class='bx bx-line-chart text-2xl'></i>
                </div>
            </div>
        </section>
        <div class="bg-gray-100 min-h-screen">

            <!-- Charts Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="flex flex-col items-center bg-white shadow rounded-xl p-4 h-64">
                    <p class="text-lg"><i class='bx bx-chart-spline text-purple-500'></i> Sales Chart</p>
                    <canvas id="salesChart" class="w-full h-full mb-4"></canvas>
                </div>
                <div class="flex flex-col items-center bg-white shadow rounded-xl p-4 h-64">
                    <p class="text-lg"><i class='bx  bx-bar-chart-big text-purple-500 text-1xl'></i> Top-Selling
                        Products</p>
                    <canvas id="topChart" class="w-full h-full mb-4"></canvas>
                </div>
            </div>

            <!-- Low Stock Products -->
            <div class="bg-white shadow rounded-xl p-4 mb-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold mb-2">⚠️ Low Stock Products</h2>
                    <div class="flex items-center">
                        <a href="products.php"
                            class="flex items-center gap-2 rounded-full border px-4 py-2 hover:bg-gray-200">
                            <span>View All</span>
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full border hover:bg-gray-500">
                                <i class='bx bx-arrow-right text-lg'></i>
                            </span>
                        </a>
                    </div>
                </div>
                <table class="w-full text-left border-collapse mt-1">
                    <thead>
                        <tr class="bg-gray-200 ">
                            <th class="p-2">Product</th>
                            <th class="p-2">Stock</th>
                            <th class="p-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($low > 0): ?>
                            <?php while ($row = $low_stock->fetch_assoc()): ?>
                                <tr class="border-b">
                                    <td class="p-2"><?= htmlspecialchars($row['name']) ?></td>
                                    <td class="p-2"><?= $row['quantity'] ?></td>
                                    <td class="p-2 <?= $row['quantity'] == 0 ? 'text-red-500' : 'text-yellow-500' ?>">
                                        <?= $row['quantity'] == 0 ? 'Out of Stock' : 'Low Stocks' ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr class="border-b">
                                <td colspan="3" class="p-2 text-center text-gray-500 text-xl">
                                    <i class="fa-solid fa-circle-check text-green-500 mr-2 text-xl">
                                    </i>All products are sufficiently stocked.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Transactions (PHP) -->
            <div class="bg-white shadow rounded-xl p-4">
                <div class="flex items-center justify-between ">
                    <h2 class="text-lg font-bold mb-2">🧾 Recent Transactions</h2>
                    <div class="flex items-center gap-4 ">
                        <form method="GET" id="txFilterForm" class="flex items-center gap-2">
                            <input type="date" name="tx_start"
                                value="<?php echo isset($_GET['tx_start']) ? $_GET['tx_start'] : ''; ?>"
                                class="border rounded-full px-3 py-1 text-sm">

                            <span>to</span>

                            <input type="date" name="tx_end"
                                value="<?php echo isset($_GET['tx_end']) ? $_GET['tx_end'] : ''; ?>"
                                class="border rounded-full px-3 py-1 text-sm">
                        </form>
                        <a href="transaction.php"
                            class="flex items-center gap-2 rounded-full border px-4 py-2 hover:bg-gray-200">
                            <span>View All</span>
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full border hover:bg-gray-500">
                                <i class='bx bx-arrow-right text-lg'></i>
                            </span>
                        </a>
                    </div>
                </div>
                <div class="max-h-96 overflow-y-auto mt-3">
                    <table class="w-full text-sm border-collapse">
                        <thead class="bg-gray-100 sticky top-0">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold">#ID</th>
                                <th class="px-3 py-2 text-left font-semibold">Amount</th>
                                <th class="px-3 py-2 text-left font-semibold">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Get filter values
                            $tx_start = isset($_GET['tx_start']) ? $_GET['tx_start'] : null;
                            $tx_end = isset($_GET['tx_end']) ? $_GET['tx_end'] : null;

                            $where = "";
                            if ($tx_start && $tx_end) {
                                $where = "WHERE DATE(created_at) BETWEEN '$tx_start' AND '$tx_end'";
                            } elseif ($tx_start) {
                                $where = "WHERE DATE(created_at) >= '$tx_start'";
                            } elseif ($tx_end) {
                                $where = "WHERE DATE(created_at) <= '$tx_end'";
                            }

                            // Fetch all transactions (remove LIMIT)
                            $allTx = $conn->query("
                                SELECT id, total_amount, created_at
                                FROM orders
                                $where
                                ORDER BY created_at DESC
                            ");
                            ?>
                            <?php if ($allTx && $allTx->num_rows > 0): ?>
                                <?php while ($tx = $allTx->fetch_assoc()): ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-3 py-2">#<?= (int) $tx['id'] ?></td>
                                        <td class="px-3 py-2">₱<?= number_format((float) $tx['total_amount'], 2) ?></td>
                                        <td class="px-3 py-2"><?= date('Y-m-d H:i', strtotime($tx['created_at'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="px-3 py-4 text-center text-gray-500">
                                        <i class='bx bx-coins text-gray-500 mr-2 text-xl'></i> No recent transactions.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
    <script>
        const salesLabels = <?php echo json_encode($labels); ?>;
        const salesValues = <?php echo json_encode($values); ?>;
        const topLabels = <?php echo json_encode($top_labels); ?>;
        const topValues = <?php echo json_encode($top_values); ?>;
    </script>
</body>

</html>