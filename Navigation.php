<?php
function is_active($page) {
    return strcasecmp(basename($_SERVER['PHP_SELF']), $page) === 0 ? 'current' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restaurant POS</title>
    <link rel="stylesheet" href="app.css">
</head>
<body>
<header class="app-header">
    <a class="brand" href="index.php"><span class="brand-mark">R</span><span>Restaurant<span class="brand-light">POS</span></span></a>
    <nav class="main-nav" aria-label="Main navigation">
        <a class="nav-link <?= is_active('index.php') ?>" href="index.php">Overview</a>
        <a class="nav-link <?= is_active('Product_list.php') ?>" href="Product_list.php">Products</a>
        <a class="nav-link <?= is_active('Tables.php') ?>" href="Tables.php">Tables</a>
        <a class="nav-link <?= in_array(basename($_SERVER['PHP_SELF']), ['Reports.php','Today_Reports.php','Report_By_Date.php'], true) ? 'current' : '' ?>" href="Reports.php">Reports</a>
    </nav>
    <span class="header-status"><i></i> Service ready</span>
</header>
<main class="page-content">
