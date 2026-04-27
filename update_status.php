<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id']);

    // Update payment_type to "pay"
    $query = "UPDATE orders SET payment_type = 'pay' WHERE id = $order_id";
    if (mysqli_query($conn, $query)) {
        header("Location: transaction.php?updated=1");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
