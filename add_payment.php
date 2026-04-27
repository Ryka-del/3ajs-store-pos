<?php
include "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id']);
    $amount = floatval($_POST['amount']);

    // Insert into payment history
    $stmt = $conn->prepare("INSERT INTO order_payments (order_id, amount) VALUES (?, ?)");
    $stmt->bind_param("id", $order_id, $amount);
    $stmt->execute();
    $stmt->close();

    // Update balance
    $conn->query("UPDATE orders 
                  SET payment_amount = payment_amount + $amount,
                      balance = total_amount - payment_amount
                  WHERE id = $order_id");

    header("Location: transaction.php?msg=Payment added successfully");
    exit;
}
