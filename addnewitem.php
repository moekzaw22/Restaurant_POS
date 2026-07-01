<?php
session_start();
require 'database.php';

header('Content-Type: application/json');

$order_id = $_POST['order_id'] ?? null;
$table_id = $_SESSION['table_id'] ?? null;
$product_code = $_POST['product_code'] ?? '';
$quantity = $_POST['quantity'] ?? 1;
$note = $_POST['note'] ?? '';

// Validate
if(!$table_id || !$product_code){
    echo json_encode(['error'=>'Table or product missing']);
    exit;
}

// Check product exists
$stmt = $pdo->prepare("SELECT Product_ID, Item_Price FROM product WHERE Product_ID = ?");
$stmt->execute([$product_code]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$product){
    echo json_encode(['error'=>'Invalid product code']);
    exit;
}

// Create new order if first item
if(!$order_id){
    $stmt = $pdo->prepare("INSERT INTO order_r (Table_ID, Status) VALUES (?, 'Open')");
    $stmt->execute([$table_id]);
    $order_id = $pdo->lastInsertId();

    // Update table status
    $stmt = $pdo->prepare("UPDATE tables_assignment SET Status='Occupied', Assign_order=? WHERE Table_ID=?");
    $stmt->execute([$order_id, $table_id]);

    $_SESSION['order_id'] = $order_id;
}

// Insert item into order_detail
$stmt = $pdo->prepare("INSERT INTO order_detail (Order_ID, Product_ID, Quantity, Note, Product_Price) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$order_id, $product['Product_ID'], $quantity, $note, $product['Item_Price']]);

echo json_encode(['order_id'=>$order_id]);
