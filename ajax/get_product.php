<?php
session_start();
require_once "../db.php";

header("Content-Type: application/json");

if (!isset($_GET['barcode']) || $_GET['barcode'] === "") {
	echo json_encode(["success" => false, "message" => "Missing barcode"]);
	exit;
}

$barcode = $_GET['barcode'];

$stmt = $conn->prepare("SELECT id, name, price, image_url FROM products WHERE barcode = ? LIMIT 1");
$stmt->bind_param("s", $barcode);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
	echo json_encode([
		"success" => true,
		"product" => [
			"id" => (int)$row['id'],
			"name" => $row['name'],
			"price" => (float)$row['price'],
			"image_url" => $row['image_url']
		]
	]);
} else {
	echo json_encode(["success" => false, "message" => "Product not found"]);
}