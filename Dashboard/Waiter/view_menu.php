<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'waiter') {
    header("Location: ../../login.php");
    exit();
}
include("../../config/db.php");

// Fetch all categories with their items (only available items)
$categories = [];
$cat_query = "SELECT * FROM menu_categories ORDER BY name";
$cat_result = mysqli_query($conn, $cat_query);
if ($cat_result) {
    while ($cat = mysqli_fetch_assoc($cat_result)) {
        $cat_id = $cat['id'];
        $items_query = "SELECT * FROM menu_items WHERE category_id = $cat_id AND available = 1 ORDER BY name";
        $items_result = mysqli_query($conn, $items_query);
        $items = [];
        if ($items_result) {
            while ($item = mysqli_fetch_assoc($items_result)) {
                $items[] = $item;
            }
        }
        if (!empty($items)) {
            $categories[] = [
                'name' => $cat['name'],
                'description' => $cat['description'],
                'items' => $items
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Menu - Waiter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #2c3e50;
        }
        .category {
            margin-bottom: 30px;
        }
        .category h2 {
            background: #2c3e50;
            color: white;
            padding: 10px;
            border-radius: 5px;
        }
        .category-description {
            color: #7f8c8d;
            margin-bottom: 15px;
            font-style: italic;
        }
        .menu-items {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .menu-item {
            background: #ecf0f1;
            border-radius: 8px;
            width: calc(33.333% - 20px);
            padding: 15px;
            box-sizing: border-box;
            transition: transform 0.2s;
        }
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .menu-item img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .menu-item h3 {
            margin: 0 0 5px 0;
            color: #2c3e50;
        }
        .menu-item .price {
            font-weight: bold;
            color: #e67e22;
            font-size: 1.2em;
        }
        .menu-item .description {
            color: #7f8c8d;
            font-size: 0.9em;
            margin: 10px 0;
        }
        @media (max-width: 768px) {
            .menu-item {
                width: calc(50% - 20px);
            }
        }
        @media (max-width: 480px) {
            .menu-item {
                width: 100%;
            }
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background: #2c3e50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .back-link:hover {
            background: #1abc9c;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Restaurant Menu</h1>
    <?php if (empty($categories)): ?>
        <p>No menu items available.</p>
    <?php else: ?>
        <?php foreach ($categories as $cat): ?>
            <div class="category">
                <h2><?php echo htmlspecialchars($cat['name']); ?></h2>
                <?php if (!empty($cat['description'])): ?>
                    <div class="category-description"><?php echo htmlspecialchars($cat['description']); ?></div>
                <?php endif; ?>
                <div class="menu-items">
                    <?php foreach ($cat['items'] as $item): ?>
                        <div class="menu-item">
                            <?php if (!empty($item['image'])): ?>
                                <img src="../../assets/uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php endif; ?>
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <div class="price">$<?php echo number_format($item['price'], 2); ?></div>
                            <?php if (!empty($item['description'])): ?>
                                <div class="description"><?php echo nl2br(htmlspecialchars($item['description'])); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <a href="index.php" class="back-link">Back to Dashboard</a>
</div>
</body>
</html>