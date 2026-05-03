<?php
// checkout.php
session_start();
include "db.php";

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
	echo json_encode(["success" => false, "message" => "Not logged in"]);
	exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['cart']) || empty($data['cart'])) {
	echo json_encode(["success" => false, "message" => "Cart is empty"]);
	exit;
}

$user_id = $_SESSION['user_id'];
$total = (float) $data['total'];
$payment = (float) $data['payment'];
$paymentType = ($data['paymentType'] ?? 'pay') === 'debt' ? 'debt' : 'pay';
$cart = $data['cart'];

$conn->begin_transaction();

try {
	// Keep checkout compatible with databases imported before payment_type was added.
	$paymentTypeColumn = $conn->query("SHOW COLUMNS FROM orders LIKE 'payment_type'");
	$hasPaymentType = $paymentTypeColumn && $paymentTypeColumn->num_rows > 0;

	if ($hasPaymentType) {
		$stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_amount, payment_type, created_at)
			VALUES (?, ?, ?, ?, NOW())");
		if (!$stmt) {
			throw new Exception("Could not prepare order insert: " . $conn->error);
		}
		$stmt->bind_param("idds", $user_id, $total, $payment, $paymentType);
	} else {
		$stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_amount, created_at)
			VALUES (?, ?, ?, NOW())");
		if (!$stmt) {
			throw new Exception("Could not prepare order insert: " . $conn->error);
		}
		$stmt->bind_param("idd", $user_id, $total, $payment);
	}

	$stmt->execute();
	$order_id = $stmt->insert_id;
	$stmt->close();

	$checkStmt = $conn->prepare("SELECT quantity, name FROM products WHERE id = ? FOR UPDATE");
	$insertItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
	$updateStock = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");

	if (!$checkStmt || !$insertItem || !$updateStock) {
		throw new Exception("Could not prepare checkout statements: " . $conn->error);
	}

	foreach ($cart as $product_id => $item) {
		$pid = (int) $product_id;
		$qty = (int) $item['qty'];
		$price = (float) $item['price'];

		$checkStmt->bind_param("i", $pid);
		$checkStmt->execute();
		$res = $checkStmt->get_result();

		if (!$res || $res->num_rows === 0) {
			throw new Exception("Product not found (ID $pid).");
		}

		$row = $res->fetch_assoc();
		$currentQty = (int) $row['quantity'];
		$pname = $row['name'];

		if ($qty <= 0) {
			throw new Exception("Invalid quantity for $pname.");
		}

		if ($currentQty < $qty) {
			throw new Exception("Insufficient stock for $pname. Available: $currentQty, requested: $qty.");
		}

		$insertItem->bind_param("iiid", $order_id, $pid, $qty, $price);
		$insertItem->execute();

		$updateStock->bind_param("ii", $qty, $pid);
		$updateStock->execute();
	}

	$checkStmt->close();
	$insertItem->close();
	$updateStock->close();

	$conn->commit();

	$_SESSION['last_order_id'] = $order_id;

	echo json_encode(["success" => true, "order_id" => $order_id]);
	exit;
} catch (Exception $e) {
	$conn->rollback();
	echo json_encode(["success" => false, "message" => $e->getMessage()]);
	exit;
}
?>
