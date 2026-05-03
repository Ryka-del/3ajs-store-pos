<?php
session_start();
include "db.php"; // Database connection

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Low stock query
$low_stock = $conn->query("SELECT name, quantity FROM products WHERE quantity < 5");
$low = $conn->query("SELECT COUNT(*) AS c FROM products WHERE quantity <= 5")
    ->fetch_assoc()['c'] ?? 0;

// Product query
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';

$sql = "SELECT * FROM products WHERE 1";

if (!empty($search)) {
    $sql .= " AND name LIKE '%$search%'";
}

if (!empty($category)) {
    $sql .= " AND category = '$category'";
}

$sql .= " ORDER BY (quantity <= 0)ASC, name ASC";

$query = $conn->query($sql);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $barcode = $_POST['barcode'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $cost_price = $_POST['cost_price'];
    $quantity = $_POST['quantity'];
    $product_id = isset($_POST['product_id']) && $_POST['product_id'] !== '' ? (int)$_POST['product_id'] : null;

    // Handle image upload
    $image_url = null;
    $newImageUploaded = !empty($_FILES['image']['name']);

    if ($newImageUploaded) {
        $target_dir = "uploads/";

        if (!empty($_FILES['image']['name'])) {
            $target_dir = "uploads/";

            // Create directory if it doesn't exist
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            // Validate upload error first
            if (!isset($_FILES['image']['error']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                die("❌ Error uploading file.");
            }

            $original_name = $_FILES['image']['name'];
            // Sanitize filename
            $safe_name = preg_replace('/[^A-Za-z0-9._-]/', '_', $original_name);

            $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            // Detect MIME using finfo for reliability
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $file_mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
            finfo_close($finfo);

            // Normalize uncommon JPEG MIME
            if ($file_mime === 'image/jpg') {
                $file_mime = 'image/jpeg';
            }
            $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            // Validate extension and MIME independently
            if (!in_array($file_ext, $allowed_exts, true) || !in_array($file_mime, $allowed_mimes, true)) {
                die("❌ Invalid file type. Only JPG, PNG, GIF, or WEBP are allowed.");
            }

            // Limit file size (max 2MB)
            $file_size = $_FILES['image']['size'];
            if ($file_size > 2 * 1024 * 1024) {
                die("❌ File is too large. Maximum size is 2MB.");
            }

            $file_name = time() . "_" . $safe_name;
            $target_file = $target_dir . $file_name;

            // Move uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_url = $target_file;
            } else {
                die("❌ Error moving uploaded file.");
            }
        }
    }

    if ($product_id) {
        // EDIT: fetch existing image if not replaced
        if (!$newImageUploaded) {
            $imgStmt = $conn->prepare("SELECT image_url FROM products WHERE id = ?");
            $imgStmt->bind_param("i", $product_id);
            $imgStmt->execute();
            $imgRes = $imgStmt->get_result()->fetch_assoc();
            $image_url = $imgRes ? $imgRes['image_url'] : null;
        }

        $stmt = $conn->prepare("UPDATE products
            SET barcode = ?, name = ?, category = ?, price = ?, cost_price = ?, quantity = ?, image_url = ?, updated_at = NOW()
            WHERE id = ?");
        $stmt->bind_param("sssddisi", $barcode, $name, $category, $price, $cost_price, $quantity, $image_url, $product_id);
        $stmt->execute();

        header("Location: products.php?updated=1");
        exit;
    } else {
        // ADD
        $stmt = $conn->prepare("INSERT INTO products (barcode, name, category, price, cost_price, quantity, image_url, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("sssddis", $barcode, $name, $category, $price, $cost_price, $quantity, $image_url);
        $stmt->execute();

        header("Location: products.php?success=1");
        exit;
    }
}

if (isset($_GET['barcode'])) {
    $barcode = $_GET['barcode'];

    $stmt = $conn->prepare("SELECT name, category FROM products WHERE barcode = ?");
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            "success" => true,
            "name" => $row['name'],
            "category" => $row['category']
        ]);
    } else {
        echo json_encode(["success" => false]);
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- QuaggaJS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
    <script src="https://unpkg.com/@zxing/library@latest"></script>
    <script src="assets/js/script.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body id="page-transition"
    class="ui-app-shell flex bg-[#34495E] font-sans text-gray-900 translate-y-5 opacity-0 transition-all duration-500">
    <?php include 'includes/sidebar.php'; ?>
    <div class="ui-main-panel bg-gray-100 m-6 w-full min-h-screen p-6 space-y-6 rounded-3xl">
        <?php include 'includes/topbar.php'; ?>
        <h2 class="section-heading reveal text-4xl font-bold text-gray-800 mb-4">Products</h2>

        <!-- <div class="">
            <?php if ($low > 0): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Low Stock Alert!</strong>
                    <span class="block sm:inline">The following products are low in stock:</span>
                    <ul class="list-disc list-inside mt-2">
                        <?php while ($row = $low_stock->fetch_assoc()): ?>
                            <li><?php echo htmlspecialchars($row['name']) . " (Qty: " . (int) $row['quantity'] . ")"; ?></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div> -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-3">
            <!-- Search -->
            <form method="GET" class="flex w-full sm:w-1/2">
                <div class="relative w-full">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-blue-500"></i>
                    <input type="text" name="search" placeholder="Search product..."
                        class="ui-input w-full p-2 pl-10 rounded-l-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 text-black rounded-lg"
                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                        <i class="fa-solid fa-barcode absolute right-3 top-1/2 -translate-y-1/2 text-blue-500"></i>
                    </button>
                </div>
            </form>

            <div class="flex flex-col sm:flex-row max-lg:flex-row items-center gap-3 w-full sm:w-auto">
                <!-- Category Filter -->
                <form method="GET">
                    <select name="category" onchange="this.form.submit()"
                        class="ui-select p-2 rounded-lg border border-gray-300 text-black">
                        <option value="">All Categories</option>
                        <!-- Loop categories dynamically -->
                        <?php
                        $cats = $conn->query("SELECT * FROM categories WHERE id != 11");
                        while ($cat = $cats->fetch_assoc()):
                            ?>
                            <option value="<?php echo $cat['name']; ?>" <?php if (isset($_GET['category']) && $_GET['category'] === $cat['name'])
                                   echo 'selected'; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </form>

                <!-- Add Product Button -->
                <button class="ui-primary-btn bg-blue-600 hover:bg-blue-700 text-white xl:px-4 xl:py-2 rounded-lg"
                    onclick="openProductModal('add')">
                    + Add Product
                </button>
            </div>
            <!-- Modal -->
            <div id="addProductModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                <div class="ui-modal-panel bg-white rounded-xl p-6 w-full max-w-lg relative max-h-[90vh] overflow-y-auto">
                    <h2 id="productModalTitle" class="text-xl font-bold mb-4">Add Product</h2>

                    <!-- Close Button -->
                    <button onclick="closeProductModal()"
                        class="absolute top-3 right-3 text-gray-500 hover:text-black">✖</button>

                    <form id="productForm" method="POST" action="products.php" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" name="product_id" id="productId" value="">
                        <!-- Modern Image Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Image</label>
                            <div id="imagePreviewContainer"
                                class="w-full h-40 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center cursor-pointer relative overflow-hidden bg-gray-50">

                                <!-- Default text -->
                                <span id="uploadText" class="text-gray-400">Click or Drag & Drop to Upload</span>

                                <!-- Preview image -->
                                <img id="imagePreview" class="hidden w-full h-full object-cover absolute inset-0" />

                                <!-- File input -->
                                <input type="file" name="image" id="imageUpload" accept="image/*"
                                    class="absolute inset-0 opacity-0 cursor-pointer" />
                            </div>
                        </div>
                        <!-- Barcode -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Barcode</label>
                            <div class="flex space-x-2">
                                <input type="text" id="barcodeInput" name="barcode"
                                    class="ui-input w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                                    placeholder="Scan or enter barcode">
                                <button type="button" onclick="startScanner()"
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                                    Scan
                                </button>
                            </div>
                        </div>

                        <!-- Product Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Product Name</label>
                            <input type="text" id="productName" name="name" class="ui-input w-full p-2 border rounded-lg"
                                required>
                        </div>

                        <!-- Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Category</label>
                            <select id="categorySelect" name="category" class="ui-select w-full p-2 border rounded-lg" required>
                                <option value="" disabled selected>Select Category</option>
                                <?php
                                $cats = $conn->query("SELECT * FROM categories");
                                while ($cat = $cats->fetch_assoc()):
                                    ?>
                                    <option value="<?php echo htmlspecialchars($cat['name']); ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="grid grid-cols-2">
                            <!-- Cost Price -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cost Price</label>
                                <input type="number" step="0.01" name="cost_price" id="costPrice" class="ui-input w-full p-2 border rounded-lg"
                                    required>
                            </div>
                            <!-- Price -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Price</label>
                                <input type="number" step="0.01" name="price" id="sellPrice" class="ui-input w-full p-2 border rounded-lg"
                                    required>
                            </div>
                        </div>

                        <!-- Quantity -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input type="number" name="quantity" id="qty" class="ui-input w-full p-2 border rounded-lg" required>
                        </div>

                        <!-- Submit -->
                        <button id="productSubmitBtn" type="submit"
                            class="ui-primary-btn bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg w-full">
                            Save Product
                        </button>
                    </form>

                    <!-- Scanner Preview -->
                    <div id="scannerContainer" class="mt-4 hidden">
                        <video id="scannerPreview" autoplay playsinline
                            class="w-full rounded-lg overflow-hidden bg-black"></video>
                        <div class="flex justify-center">
                            <button onclick="stopScanner()"
                                class="mt-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg items-center justify-center flex">
                                Stop Scan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Products Grid -->
        <div
            class="ui-soft-card ui-glow-ring grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 border p-4 rounded-lg bg-white h-[42rem] overflow-y-auto">
            <?php if ($query->num_rows > 0): ?>
                <?php while ($row = $query->fetch_assoc()): ?>
                    <div class="flex flex-col">
                        <div class="product-card tilt-card reveal bg-white rounded-xl shadow-md p-4 flex flex-col items-center h-72 cursor-pointer hover:shadow-lg transition hover:-translate-y-2"
                            onclick="toggleButtons(this)">
                            <!-- Product Image -->
                            <img src="<?php echo !empty($row['image_url']) ? $row['image_url'] : 'assets/images/no-image.png'; ?>"
                                alt="<?php echo htmlspecialchars($row['name']); ?>"
                                class="product-thumb w-32 h-32 object-cover rounded-lg mb-3">

                            <!-- Product Info -->
                            <h2 class="text-lg font-semibold text-gray-800">
                                <?php echo htmlspecialchars($row['name']); ?>
                            </h2>
                            <div class="mt-auto flex">
                                <p class="text-gray-500 text-sm"><?php echo htmlspecialchars($row['category']); ?></p>
                            </div>
                            <div class="mt-auto flex items-center justify-between gap-3">
                                <p class="text-blue-600 font-bold mt-2">₱<?php echo number_format($row['price'], 2); ?></p>
                                <span
                                    class="mt-2 text-xs px-2 py-1 rounded <?php echo ((int) $row['quantity'] <= 0) ? 'bg-red-100 text-red-700' : (((int) $row['quantity'] <= 5) ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700'); ?>">
                                    <?php echo (int) $row['quantity'] <= 0 ? 'Out of stock' : 'Stock: ' . (int) $row['quantity']; ?>
                                </span>
                            </div>
                        </div>
                        <!-- Buttons BELOW card -->
                        <div class="hidden flex justify-center mt-3 gap-3 action-buttons">
                            <button
                                class="ui-primary-btn w-full px-6 py-2 bg-blue-500 text-white rounded-lg shadow hover:bg-blue-600 transition"
                                onclick="openProductModal('edit', this)"
                                data-id="<?php echo (int)$row['id']; ?>"
                                data-barcode="<?php echo htmlspecialchars($row['barcode']); ?>"
                                data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                data-category="<?php echo htmlspecialchars($row['category']); ?>"
                                data-price="<?php echo htmlspecialchars($row['price']); ?>"
                                data-cost="<?php echo htmlspecialchars($row['cost_price']); ?>"
                                data-qty="<?php echo (int)$row['quantity']; ?>"
                                data-image="<?php echo htmlspecialchars($row['image_url']); ?>"
                            >
                                Edit product
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full flex flex-col items-center justify-center py-10">
                    <i class="fa-solid fa-cart-shopping text-4xl mb-2 text-[#3498DB] text-center"></i>
                    <p class="text-center text-gray-600 col-span-full">No products found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
