<?php
include "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id']);
    $additional_payment = floatval($_POST['additional_payment']);

    if ($additional_payment > 0) {
        // Update payment amount by adding new payment
        $stmt = $conn->prepare("UPDATE orders SET payment_amount = payment_amount + ? WHERE id = ?");
        $stmt->bind_param("di", $additional_payment, $order_id);
        if ($stmt->execute()) {
            header("Location: transaction.php?success=1");
            exit;
        } else {
            header("Location: transaction.php?error=1");
            exit;
        }
    } else {
        header("Location: transaction.php?error=invalid");
        exit;
    }
}
?>
