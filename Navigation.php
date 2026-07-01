<style>
body{
    margin:0;
    font-family: Arial;
}

.topbar{
    background:#333;
    color:white;
    padding:12px;
    font-size:20px;
}

.nav-menu{
    background:#444;
}

.nav-menu .nav-link{
    color:white;
    padding:14px 18px;
    display:inline-block;
    text-decoration:none;
}

.nav-menu .nav-link:hover{
    background:#666;
}

.nav-link.current{
    background:#666;
}

.content{
    padding:15px;
}
</style>
<?php 
function is_active($page){
    return basename($_SERVER['PHP_SELF']) == $page ? 'current' : '';
}
 ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title></title>
</head>
<body>



<div class="topbar">
Restaurant POS
</div>

<div class="nav-menu">
    <a class="nav-link <?= is_active('index.php'); ?>" href="index.php">Dashboard</a>
    <a class="nav-link <?= is_active('product_list.php'); ?>" href="product_list.php">Products</a>
    <a class="nav-link <?= is_active('tables.php'); ?>" href="tables.php">Tables</a>
    <a class="nav-link <?= is_active('Reports.php'); ?>" href="Reports.php">Reports</a>
</div>
</body>
</html>
<script>
// function toggleMenu(){
//     document.getElementById("navMenu").classList.toggle("active");
// }

</script>