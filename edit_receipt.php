<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id']);
    $amount = floatval($_POST['amount']);
    $payment_type = $_POST['payment_type'] ?? 'debt';

    // Update query
    $query = "UPDATE orders SET payment_amount = ?, payment_type = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("dsi", $amount, $payment_type, $order_id);

    if ($stmt->execute()) {
        // Preserve filter parameters
        $range = $_GET['range'] ?? ($_POST['range'] ?? 'today');
        $start = $_GET['start'] ?? ($_POST['start'] ?? null);
        $end = $_GET['end'] ?? ($_POST['end'] ?? null);

        $redirectUrl = "transaction.php?range=" . urlencode($range);
        if ($range === "custom" && $start && $end) {
            $redirectUrl .= "&start=" . urlencode($start) . "&end=" . urlencode($end);
        }

        header("Location: $redirectUrl&updated=1");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
