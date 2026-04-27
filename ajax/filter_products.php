<?php
include "../db.php";

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

// Get the category name from categories table
$category_result = $conn->query("SELECT name FROM categories WHERE id = $category_id");
$category_name = null;

if ($category_result && $category_result->num_rows > 0) {
    $category_row = $category_result->fetch_assoc();
    $category_name = $category_row['name'];
}

// Build the query based on category
$sql = "SELECT * FROM products";

if ($category_id == 0 || !$category_name || $category_name === "All") {
    // Show all products (category_id = 0 or category name is "All")
    $query = $conn->query("SELECT * FROM products ORDER BY (quantity <= 0) ASC, name ASC");
} else {
    // Filter by specific category name
    $escaped_category = $conn->real_escape_string($category_name);
    $query = $conn->query("SELECT * FROM products WHERE category = '$escaped_category' ORDER BY (quantity <= 0) ASC, name ASC");
}

if ($query && $query->num_rows > 0) {
	while ($row = $query->fetch_assoc()) {
		$qty = (int)$row['quantity'];
		$is_oos = $qty <= 0;
		echo "
		<div class='bg-white p-4 rounded-lg shadow hover:shadow-lg transition cursor-pointer flex flex-col ".($is_oos ? "opacity-50 cursor-not-allowed pointer-events-none" : "")."' ".
			(!$is_oos ? "onclick='addToCart(
				\"{$row['id']}\",
				\"".htmlspecialchars($row['name'])."\",
				\"{$row['price']}\",
				\"".htmlspecialchars($row['image_url'])."\"
			)'" : "").">
			<img src='".htmlspecialchars($row['image_url'])."'
				alt='".htmlspecialchars($row['name'])."'
				class='w-full h-32 object-cover rounded-lg mb-4'>
			<h4 class='text-lg font-semibold mb-2'>".htmlspecialchars($row['name'])."</h4>
			<div class='mt-auto flex items-center justify-between'>
				<span class='text-blue-600 font-bold'>₱".number_format($row['price'], 2)."</span>
				<span class='mt-1 text-xs px-2 py-1 rounded ".($is_oos ? "bg-red-100 text-red-700" : (($qty <= 5) ? "bg-yellow-100 text-yellow-700" : "bg-green-100 text-green-700"))."'>".
					($is_oos ? "Out of stock" : "Stock: ".$qty).
				"</span>
			</div>
		</div>
		";
	}
} else {
    echo "
    <div class='col-span-full flex flex-col items-center justify-center'>
        <i class='fa-solid fa-cart-shopping text-4xl mb-2 mt-64 text-[#3498DB]'></i>
    <p class='text-center text-gray-600 col-span-full'>No products found.</p>
    </div>
    ";
}
?>