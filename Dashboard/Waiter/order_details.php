<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'waiter') {
    header("Location: ../../login.php");
    exit();
}
include("../../config/db.php");

$waiter_id = $_SESSION['user_id'];

// Get order_id from GET or POST
$order_id = 0;
if (isset($_GET['order_id']))  $order_id = (int)$_GET['order_id'];
if (isset($_POST['order_id'])) $order_id = (int)$_POST['order_id'];

if (!$order_id) {
    header("Location: active_order.php");
    exit();
}

// Fetch order and make sure it belongs to this waiter
$res   = mysqli_query($conn, "SELECT o.*, t.table_number FROM orders o LEFT JOIN tables t ON o.table_id = t.id WHERE o.id = $order_id AND o.waiter_id = $waiter_id");
$order = mysqli_fetch_assoc($res);

if (!$order) {
    header("Location: active_order.php");
    exit();
}

$alertMessage = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action == 'add_item') {
        $item_id  = (int)$_POST['item_id'];
        $quantity = (int)$_POST['quantity'];
        $notes    = mysqli_real_escape_string($conn, trim(isset($_POST['item_notes']) ? $_POST['item_notes'] : ''));

        $price_res = mysqli_query($conn, "SELECT price FROM menu_items WHERE id = $item_id");
        $price_row = mysqli_fetch_assoc($price_res);
        $price     = $price_row['price'];

        if (mysqli_query($conn, "INSERT INTO order_items (order_id, menu_item_id, quantity, price, notes) VALUES ($order_id, $item_id, $quantity, $price, '$notes')")) {
            $alertMessage = "Item added.";
        } else {
            $alertMessage = "Error adding item.";
        }

    } elseif ($action == 'update_item') {
        $item_order_id = (int)$_POST['item_order_id'];
        $quantity      = (int)$_POST['quantity'];
        $notes         = mysqli_real_escape_string($conn, trim($_POST['notes']));

        if (mysqli_query($conn, "UPDATE order_items SET quantity = $quantity, notes = '$notes' WHERE id = $item_order_id AND order_id = $order_id")) {
            $alertMessage = "Item updated.";
        } else {
            $alertMessage = "Error updating item.";
        }

    } elseif ($action == 'remove_item') {
        $item_order_id = (int)$_POST['item_order_id'];

        if (mysqli_query($conn, "DELETE FROM order_items WHERE id = $item_order_id AND order_id = $order_id")) {
            $alertMessage = "Item removed.";
        } else {
            $alertMessage = "Error removing item.";
        }

    } elseif ($action == 'cancel') {
        if (mysqli_query($conn, "UPDATE orders SET status = 'cancelled' WHERE id = $order_id")) {
            if ($order['order_type'] == 'dine_in' && $order['table_id']) {
                $table_id = (int)$order['table_id'];
                mysqli_query($conn, "UPDATE tables SET status = 'available' WHERE id = $table_id");
            }
            header("Location: active_order.php");
            exit();
        } else {
            $alertMessage = "Error cancelling order.";
        }

    } elseif ($action == 'serve') {
        if (mysqli_query($conn, "UPDATE orders SET status = 'served' WHERE id = $order_id")) {
            header("Location: active_order.php");
            exit();
        } else {
            $alertMessage = "Error marking as served.";
        }
    }
}

// Fetch order items
$items = array();
$res = mysqli_query($conn, "SELECT oi.*, mi.name FROM order_items oi JOIN menu_items mi ON oi.menu_item_id = mi.id WHERE oi.order_id = $order_id");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $items[] = $row;
    }
}

// Recalculate and save total
$total = 0;
foreach ($items as $it) {
    $total += $it['quantity'] * $it['price'];
}
mysqli_query($conn, "UPDATE orders SET total_amount = $total WHERE id = $order_id");

// Fetch menu items for Add Item dropdown
$menu_items = array();
$res = mysqli_query($conn, "SELECT mi.*, mc.name as category FROM menu_items mi JOIN menu_categories mc ON mi.category_id = mc.id WHERE mi.available = 1 ORDER BY mc.name, mi.name");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $menu_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_id; ?> - Waiter</title>
    <link rel="stylesheet" href="../../assets/styles/waiter.css">
</head>
<body>
<div class="container">

    <div class="top-bar">
        <div class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></div>
        <div class="logout"><a href="../../logout.php">Logout</a></div>
    </div>

    <div class="nav">
        <a href="take_order.php">Take New Order</a>
        <a href="active_order.php">Active Orders</a>
        <a href="view_menu.php">View Menu</a>
    </div>

    <div class="content">

        <?php if ($alertMessage != '') { ?>
            <p style="color:green;"><strong><?php echo htmlspecialchars($alertMessage); ?></strong></p>
        <?php } ?>

        <h2>Order #<?php echo $order_id; ?></h2>

        <p>
            <strong>Type:</strong> <?php echo $order['order_type']; ?>
            <?php if ($order['order_type'] == 'dine_in') { ?>
                &nbsp;|&nbsp; <strong>Table:</strong> <?php echo $order['table_number']; ?>
            <?php } else { ?>
                &nbsp;|&nbsp; <strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?>
                <?php if (!empty($order['customer_phone'])) { ?>
                    &nbsp;|&nbsp; <strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?>
                <?php } ?>
            <?php } ?>
        </p>

        <p>
            <strong>Status:</strong> <?php echo $order['status']; ?>
            &nbsp;|&nbsp;
            <strong>Total:</strong> $<?php echo number_format($total, 2); ?>
        </p>

        <?php if (!empty($order['notes'])) { ?>
            <p><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
        <?php } ?>

        <!-- Mark Served button -->
        <?php if ($order['order_type'] == 'dine_in' && $order['status'] != 'served' && $order['status'] != 'cancelled') { ?>
            <form method="POST" action="order_details.php?order_id=<?php echo $order_id; ?>" style="display:inline;">
                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                <input type="hidden" name="action"   value="serve">
                <button type="submit" onclick="return confirm('Mark this order as served?');">Mark as Served</button>
            </form>
            &nbsp;
        <?php } ?>

        <!-- Cancel Order button -->
        <?php if ($order['status'] != 'cancelled' && $order['status'] != 'paid') { ?>
            <form method="POST" action="order_details.php?order_id=<?php echo $order_id; ?>" style="display:inline;">
                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                <input type="hidden" name="action"   value="cancel">
                <button type="submit" onclick="return confirm('Cancel entire order?');" style="color:red;">Cancel Order</button>
            </form>
        <?php } ?>

        <br><br>

        <!-- Items table -->
        <h3>Items in this Order</h3>

        <?php if (empty($items)) { ?>
            <p>No items yet.</p>
        <?php } else { ?>
            <table border="1" cellpadding="8">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>$<?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($item['notes']); ?></td>
                        <td>
                            <!-- Update form -->
                            <form method="POST" action="order_details.php?order_id=<?php echo $order_id; ?>" style="display:inline;">
                                <input type="hidden" name="action"        value="update_item">
                                <input type="hidden" name="item_order_id" value="<?php echo $item['id']; ?>">
                                <input type="number" name="quantity"       value="<?php echo $item['quantity']; ?>" min="1" style="width:50px;">
                                <input type="text"   name="notes"          value="<?php echo htmlspecialchars($item['notes']); ?>" size="12">
                                <button type="submit">Update</button>
                            </form>
                            &nbsp;
                            <!-- Remove form -->
                            <form method="POST" action="order_details.php?order_id=<?php echo $order_id; ?>" style="display:inline;">
                                <input type="hidden" name="action"        value="remove_item">
                                <input type="hidden" name="item_order_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" onclick="return confirm('Remove this item?');">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"><strong>Total</strong></td>
                        <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        <?php } ?>

        <!-- Add Item -->
        <h3>Add Item</h3>
        <form method="POST" action="order_details.php?order_id=<?php echo $order_id; ?>">
            <input type="hidden" name="action" value="add_item">
            <select name="item_id" required>
                <option value="">-- Select Item --</option>
                <?php foreach ($menu_items as $item) { ?>
                    <option value="<?php echo $item['id']; ?>">
                        <?php echo htmlspecialchars($item['category'] . ' - ' . $item['name'] . ' ($' . number_format($item['price'], 2) . ')'); ?>
                    </option>
                <?php } ?>
            </select>
            <input type="number" name="quantity"   value="1" min="1" style="width:55px;" required>
            <input type="text"   name="item_notes" placeholder="Notes">
            <button type="submit">Add to Order</button>
        </form>

        <br>
        <a href="active_order.php">&larr; Back to Active Orders</a>

    </div>
</div>
</body>
</html>