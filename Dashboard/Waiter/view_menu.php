<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'waiter') {
    header("Location: ../../login.php");
    exit();
}
include("../../config/db.php");

// Fetch all categories
$categories = array();
$cat_result = mysqli_query($conn, "SELECT * FROM menu_categories ORDER BY name");
if ($cat_result) {
    while ($cat = mysqli_fetch_assoc($cat_result)) {
        $cat_id = $cat['id'];
        $items  = array();
        $item_result = mysqli_query($conn, "SELECT * FROM menu_items WHERE category_id = $cat_id AND available = 1 ORDER BY name");
        if ($item_result) {
            while ($item = mysqli_fetch_assoc($item_result)) {
                $items[] = $item;
            }
        }
        if (!empty($items)) {
            $cat['items'] = $items;
            $categories[] = $cat;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Menu</title>
    <link rel="stylesheet" href="../../assets/styles/waiter.css">
</head>
<body>
<div class="container">

    <div class="top-bar">
        <div class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></div>
        <div class="logout"><a href="../../logout.php">Logout</a></div>
    </div>

    <div class="nav">
        <a href="index.php?page=take_order">Take New Order</a>
        <a href="index.php?page=active_orders">Active Orders</a>
        <a href="view_menu.php" class="active">View Menu</a>
    </div>

    <div class="content">

        <h2>Restaurant Menu</h2>

        <?php if (empty($categories)) { ?>
            <p>No menu items available.</p>
        <?php } else { ?>
            <?php foreach ($categories as $cat) { ?>
                <div class="category">
                    <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                    <?php if (!empty($cat['description'])) { ?>
                        <p><?php echo htmlspecialchars($cat['description']); ?></p>
                    <?php } ?>
                    <table border="1" cellpadding="8">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Price</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($cat['items'] as $item) { ?>
                            <tr>
                                <td>
                                    <?php if (!empty($item['image'])) { ?>
                                        <img src="../../assets/uploads/<?php echo htmlspecialchars(basename($item['image'])); ?>"
                                             alt="<?php echo htmlspecialchars($item['name']); ?>"
                                             style="width:60px; height:60px; object-fit:cover;"
                                             onerror="this.style.display='none'"><br>
                                    <?php } ?>
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($item['description'])); ?></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <br>
            <?php } ?>
        <?php } ?>

        <a href="index.php">Back to Dashboard</a>

    </div><!-- /.content -->
</div><!-- /.container -->
</body>
</html>