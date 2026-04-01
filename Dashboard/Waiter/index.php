<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'waiter') {
    header("Location: ../../login.php");
    exit();
}
include("../../config/db.php");

$page = isset($_GET['page']) ? $_GET['page'] : 'take_order';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waiter Dashboard</title>
    <link rel="stylesheet" href="../../assets/styles/waiter.css">
</head>
<body>
<div class="container">

    <div class="top-bar">
        <div class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></div>
        <div class="logout"><a href="../../logout.php">Logout</a></div>
    </div>

    <div class="nav">
        <a href="index.php?page=take_order"    <?php if ($page == 'take_order')    echo 'class="active"'; ?>>Take New Order</a>
        <a href="index.php?page=active_orders" <?php if ($page == 'active_orders') echo 'class="active"'; ?>>Active Orders</a>
        <a href="view_menu.php">View Menu</a>
    </div>

    <div class="content">

        <?php if ($page == 'active_orders') { ?>

            <?php
            $waiter_id = $_SESSION['user_id'];
            $orders = array();
            $res = mysqli_query($conn, "SELECT o.*, t.table_number FROM orders o LEFT JOIN tables t ON o.table_id = t.id WHERE o.waiter_id = $waiter_id AND o.status NOT IN ('paid','cancelled') ORDER BY o.order_time DESC");
            if ($res) {
                while ($row = mysqli_fetch_assoc($res)) {
                    $orders[] = $row;
                }
            }
            ?>

            <h2>Active Orders</h2>

            <?php if (empty($orders)) { ?>
                <p>No active orders.</p>
            <?php } else { ?>
                <table border="1" cellpadding="8">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Type</th>
                            <th>Table / Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $order) { ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td><?php echo $order['order_type']; ?></td>
                            <td>
                                <?php if ($order['order_type'] == 'dine_in') { ?>
                                    Table <?php echo $order['table_number']; ?>
                                <?php } else { ?>
                                    <?php echo htmlspecialchars($order['customer_name']); ?>
                                <?php } ?>
                            </td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo $order['status']; ?></td>
                            <td>
                                <a href="index.php?page=order_details&order_id=<?php echo $order['id']; ?>">View / Modify</a>

                                <?php if ($order['order_type'] == 'dine_in' && $order['status'] != 'served') { ?>
                                    &nbsp;
                                    <form method="POST" action="index.php?page=order_details&order_id=<?php echo $order['id']; ?>" style="display:inline;">
                                        <input type="hidden" name="action"   value="serve">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <button type="submit" onclick="return confirm('Mark this order as served?');">Mark Served</button>
                                    </form>
                                <?php } ?>

                                &nbsp;
                                <form method="POST" action="index.php?page=order_details&order_id=<?php echo $order['id']; ?>" style="display:inline;">
                                    <input type="hidden" name="action"   value="cancel">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" onclick="return confirm('Cancel this order?');">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            <?php } ?>

        <?php } elseif ($page == 'order_details') { ?>

            <?php
            $order_id = 0;
            if (isset($_GET['order_id']))  $order_id = (int)$_GET['order_id'];
            if (isset($_POST['order_id'])) $order_id = (int)$_POST['order_id'];

            if (!$order_id) {
                echo '<p>Invalid order. <a href="index.php?page=active_orders">Go back</a></p>';
            } else {

                $waiter_id = $_SESSION['user_id'];
                $res = mysqli_query($conn, "SELECT o.*, t.table_number FROM orders o LEFT JOIN tables t ON o.table_id = t.id WHERE o.id = $order_id AND o.waiter_id = $waiter_id");
                $order = mysqli_fetch_assoc($res);

                if (!$order) {
                    echo '<p>Order not found or access denied. <a href="index.php?page=active_orders">Go back</a></p>';
                } else {

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
                                echo "<script>alert('Order cancelled.'); window.location.href='index.php?page=active_orders';</script>";
                                exit;
                            } else {
                                $alertMessage = "Error cancelling order.";
                            }

                        } elseif ($action == 'serve') {
                            if (mysqli_query($conn, "UPDATE orders SET status = 'served' WHERE id = $order_id")) {
                                echo "<script>alert('Order marked as served.'); window.location.href='index.php?page=active_orders';</script>";
                                exit;
                            } else {
                                $alertMessage = "Error marking order as served.";
                            }
                        }
                    }

                    // Fetch items
                    $items = array();
                    $res = mysqli_query($conn, "SELECT oi.*, mi.name FROM order_items oi JOIN menu_items mi ON oi.menu_item_id = mi.id WHERE oi.order_id = $order_id");
                    if ($res) {
                        while ($row = mysqli_fetch_assoc($res)) {
                            $items[] = $row;
                        }
                    }

                    // Recalculate total
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

                    <?php if ($alertMessage != '') { ?>
                        <script>alert('<?php echo addslashes($alertMessage); ?>');</script>
                    <?php } ?>

                    <h2>Order #<?php echo $order_id; ?></h2>
                    <p>
                        <strong>Type:</strong> <?php echo $order['order_type']; ?>
                        <?php if ($order['order_type'] == 'dine_in') { ?>
                            &nbsp;|&nbsp; <strong>Table:</strong> <?php echo $order['table_number']; ?>
                        <?php } else { ?>
                            &nbsp;|&nbsp; <strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?>
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

                    <h3>Items</h3>
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
                                        <!-- Update -->
                                        <form method="POST" action="index.php?page=order_details&order_id=<?php echo $order_id; ?>" style="display:inline;">
                                            <input type="hidden" name="action"        value="update_item">
                                            <input type="hidden" name="item_order_id" value="<?php echo $item['id']; ?>">
                                            <input type="number" name="quantity"       value="<?php echo $item['quantity']; ?>" min="1" style="width:50px;">
                                            <input type="text"   name="notes"          value="<?php echo htmlspecialchars($item['notes']); ?>" size="12">
                                            <button type="submit">Update</button>
                                        </form>
                                        &nbsp;
                                        <!-- Remove -->
                                        <form method="POST" action="index.php?page=order_details&order_id=<?php echo $order_id; ?>" style="display:inline;">
                                            <input type="hidden" name="action"        value="remove_item">
                                            <input type="hidden" name="item_order_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" onclick="return confirm('Remove this item?');">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    <?php } ?>

                    <h3>Add Item</h3>
                    <form method="POST" action="index.php?page=order_details&order_id=<?php echo $order_id; ?>">
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
                    <form method="POST" action="index.php?page=order_details&order_id=<?php echo $order_id; ?>" style="display:inline;">
                        <input type="hidden" name="action"   value="cancel">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <button type="submit" onclick="return confirm('Cancel entire order?');">Cancel Order</button>
                    </form>
                    &nbsp;
                    <a href="index.php?page=active_orders">Back to Active Orders</a>

                    <?php
                } // end order found
            } // end order_id valid
            ?>

        <?php } else { ?>

            <!-- ===================== TAKE NEW ORDER ===================== -->
            <?php
            $waiter_id    = $_SESSION['user_id'];
            $alertMessage = '';

            // Fetch available tables
            $tables = array();
            $res = mysqli_query($conn, "SELECT * FROM tables WHERE status = 'available' ORDER BY table_number");
            if ($res) {
                while ($row = mysqli_fetch_assoc($res)) {
                    $tables[] = $row;
                }
            }

            // Fetch available menu items
            $menu_items = array();
            $res = mysqli_query($conn, "SELECT mi.*, mc.name as category FROM menu_items mi JOIN menu_categories mc ON mi.category_id = mc.id WHERE mi.available = 1 ORDER BY mc.name, mi.name");
            if ($res) {
                while ($row = mysqli_fetch_assoc($res)) {
                    $menu_items[] = $row;
                }
            }

            // Handle form submission
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_order'])) {

                $order_type       = mysqli_real_escape_string($conn, $_POST['order_type']);
                $notes            = mysqli_real_escape_string($conn, trim(isset($_POST['notes']) ? $_POST['notes'] : ''));
                $table_id         = ($order_type == 'dine_in') ? (int)$_POST['table_id'] : 0;
                $customer_name    = ($order_type != 'dine_in') ? mysqli_real_escape_string($conn, trim($_POST['customer_name']))    : '';
                $customer_phone   = ($order_type != 'dine_in') ? mysqli_real_escape_string($conn, trim($_POST['customer_phone']))   : '';
                $delivery_address = ($order_type == 'delivery') ? mysqli_real_escape_string($conn, trim($_POST['delivery_address'])) : '';

                $table_id_sql         = $table_id        ? $table_id               : 'NULL';
                $customer_name_sql    = $customer_name   ? "'$customer_name'"      : 'NULL';
                $customer_phone_sql   = $customer_phone  ? "'$customer_phone'"     : 'NULL';
                $delivery_address_sql = $delivery_address ? "'$delivery_address'"  : 'NULL';

                $insert_order = "INSERT INTO orders (order_type, table_id, customer_name, customer_phone, delivery_address, waiter_id, notes)
                                 VALUES ('$order_type', $table_id_sql, $customer_name_sql, $customer_phone_sql, $delivery_address_sql, $waiter_id, '$notes')";

                if (mysqli_query($conn, $insert_order)) {
                    $order_id = mysqli_insert_id($conn);

                    if ($order_type == 'dine_in' && $table_id) {
                        mysqli_query($conn, "UPDATE tables SET status = 'occupied' WHERE id = $table_id");
                    }

                    $item_ids   = isset($_POST['item_id'])    ? $_POST['item_id']    : array();
                    $quantities = isset($_POST['quantity'])   ? $_POST['quantity']   : array();
                    $item_notes = isset($_POST['item_notes']) ? $_POST['item_notes'] : array();

                    foreach ($item_ids as $index => $menu_item_id) {
                        $menu_item_id = (int)$menu_item_id;
                        $qty          = (int)$quantities[$index];
                        if ($qty <= 0 || !$menu_item_id) continue;

                        $price_res = mysqli_query($conn, "SELECT price FROM menu_items WHERE id = $menu_item_id");
                        $price_row = mysqli_fetch_assoc($price_res);
                        $price     = $price_row['price'];

                        $note_item = mysqli_real_escape_string($conn, trim(isset($item_notes[$index]) ? $item_notes[$index] : ''));
                        mysqli_query($conn, "INSERT INTO order_items (order_id, menu_item_id, quantity, price, notes) VALUES ($order_id, $menu_item_id, $qty, $price, '$note_item')");
                    }

                    echo "<script>window.location.href='index.php?page=order_details&order_id=$order_id';</script>";
                    exit;

                } else {
                    $alertMessage = "Error creating order. Please try again.";
                }
            }
            ?>

            <?php if ($alertMessage != '') { ?>
                <script>alert('<?php echo addslashes($alertMessage); ?>');</script>
            <?php } ?>

            <h2>Take New Order</h2>

            <form method="POST" action="index.php?page=take_order" id="orderForm">

                <h3>Order Type</h3>
                <label><input type="radio" name="order_type" value="dine_in"  required> Dine In</label>
                <label><input type="radio" name="order_type" value="takeaway">           Takeaway</label>
                <label><input type="radio" name="order_type" value="delivery">           Delivery</label>
                <label><input type="radio" name="order_type" value="online">             Online Order</label>

                <div id="dine_in_fields" style="display:none; margin-top:10px;">
                    <label>Select Table:</label>
                    <select name="table_id">
                        <option value="">-- Select Table --</option>
                        <?php foreach ($tables as $t) { ?>
                            <option value="<?php echo $t['id']; ?>">
                                Table <?php echo $t['table_number']; ?> (Cap. <?php echo $t['capacity']; ?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div id="non_dine_fields" style="display:none; margin-top:10px;">
                    <input type="text" name="customer_name"  placeholder="Customer Name"><br><br>
                    <input type="text" name="customer_phone" placeholder="Phone Number">
                    <div id="delivery_field" style="display:none; margin-top:8px;">
                        <textarea name="delivery_address" placeholder="Delivery Address" rows="2" cols="40"></textarea>
                    </div>
                </div>

                <h3>Menu Items</h3>
                <table id="order_items_table" border="1" cellpadding="6">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Notes</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="item-row">
                            <td>
                                <select name="item_id[]" required>
                                    <option value="">-- Select Item --</option>
                                    <?php foreach ($menu_items as $item) { ?>
                                        <option value="<?php echo $item['id']; ?>">
                                            <?php echo htmlspecialchars($item['category'] . ' - ' . $item['name'] . ' ($' . number_format($item['price'], 2) . ')'); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td><input type="number" name="quantity[]"   min="1" value="1" required style="width:60px;"></td>
                            <td><input type="text"   name="item_notes[]" placeholder="Special instructions"></td>
                            <td><button type="button" onclick="removeRow(this)">Remove</button></td>
                        </tr>
                    </tbody>
                </table>

                <br>
                <button type="button" onclick="addRow()">+ Add Another Item</button>
                <br><br>

                <label>General Notes:</label><br>
                <textarea name="notes" rows="3" cols="50"></textarea>
                <br><br>

                <button type="submit" name="create_order">Place Order</button>
            </form>

            <script>
            // Stored item options HTML so cloned rows have all options
            var itemOptionsHTML = document.querySelector('#order_items_table select').innerHTML;

            function addRow() {
                var tbody  = document.getElementById('order_items_table').getElementsByTagName('tbody')[0];
                var newRow = document.createElement('tr');
                newRow.className = 'item-row';
                newRow.innerHTML =
                    '<td><select name="item_id[]" required>' + itemOptionsHTML + '</select></td>' +
                    '<td><input type="number" name="quantity[]" min="1" value="1" required style="width:60px;"></td>' +
                    '<td><input type="text" name="item_notes[]" placeholder="Special instructions"></td>' +
                    '<td><button type="button" onclick="removeRow(this)">Remove</button></td>';
                tbody.appendChild(newRow);
            }

            function removeRow(btn) {
                var rows = document.getElementsByClassName('item-row');
                if (rows.length > 1) {
                    btn.parentNode.parentNode.parentNode.removeChild(btn.parentNode.parentNode);
                } else {
                    alert('At least one item is required.');
                }
            }

            // Show/hide fields based on order type
            var radios = document.getElementsByName('order_type');
            for (var i = 0; i < radios.length; i++) {
                radios[i].addEventListener('change', function() {
                    var dineIn   = document.getElementById('dine_in_fields');
                    var nonDine  = document.getElementById('non_dine_fields');
                    var delivery = document.getElementById('delivery_field');
                    if (this.value === 'dine_in') {
                        dineIn.style.display  = 'block';
                        nonDine.style.display = 'none';
                    } else {
                        dineIn.style.display  = 'none';
                        nonDine.style.display = 'block';
                        delivery.style.display = (this.value === 'delivery') ? 'block' : 'none';
                    }
                });
            }
            </script>

        <?php } ?>

    </div><!-- /.content -->
</div><!-- /.container -->
</body>
</html>