<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'waiter') {
    header("Location: ../../login.php");
    exit();
}
include("../../config/db.php");

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

    $table_id_sql         = $table_id         ? $table_id                : 'NULL';
    $customer_name_sql    = $customer_name    ? "'$customer_name'"       : 'NULL';
    $customer_phone_sql   = $customer_phone   ? "'$customer_phone'"      : 'NULL';
    $delivery_address_sql = $delivery_address ? "'$delivery_address'"    : 'NULL';

    $insert_order = "INSERT INTO orders (order_type, table_id, customer_name, customer_phone, delivery_address, waiter_id, notes)
                     VALUES ('$order_type', $table_id_sql, $customer_name_sql, $customer_phone_sql, $delivery_address_sql, $waiter_id, '$notes')";

    if (mysqli_query($conn, $insert_order)) {
        $order_id = mysqli_insert_id($conn);

        // Mark table occupied
        if ($order_type == 'dine_in' && $table_id) {
            mysqli_query($conn, "UPDATE tables SET status = 'occupied' WHERE id = $table_id");
        }

        // Insert order items
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

        header("Location: order_details.php?order_id=$order_id");
        exit();

    } else {
        $alertMessage = "Error creating order. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take New Order - Waiter</title>
    <link rel="stylesheet" href="../../assets/styles/waiter.css">
</head>
<body>
<div class="container">

    <div class="top-bar">
        <div class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></div>
        <div class="logout"><a href="../../logout.php">Logout</a></div>
    </div>

    <div class="nav">
        <a href="take_order.php" class="active">Take New Order</a>
        <a href="active_order.php">Active Orders</a>
        <a href="view_menu.php">View Menu</a>
    </div>

    <div class="content">

        <?php if ($alertMessage != '') { ?>
            <p style="color:red;"><strong><?php echo htmlspecialchars($alertMessage); ?></strong></p>
        <?php } ?>

        <h2>Take New Order</h2>

        <form method="POST" action="take_order.php" id="orderForm">

            <h3>Order Type</h3>
            <label><input type="radio" name="order_type" value="dine_in"  required> Dine In</label> &nbsp;
            <label><input type="radio" name="order_type" value="takeaway">           Takeaway</label> &nbsp;
            <label><input type="radio" name="order_type" value="delivery">           Delivery</label> &nbsp;
            <label><input type="radio" name="order_type" value="online">             Online Order</label>

            <br><br>

            <!-- Dine-in: table selector -->
            <div id="dine_in_fields" style="display:none;">
                <label><strong>Select Table:</strong></label><br>
                <select name="table_id">
                    <option value="">-- Select Table --</option>
                    <?php foreach ($tables as $t) { ?>
                        <option value="<?php echo $t['id']; ?>">
                            Table <?php echo $t['table_number']; ?> (Capacity: <?php echo $t['capacity']; ?>)
                        </option>
                    <?php } ?>
                </select>
                <br><br>
            </div>

            <!-- Non dine-in: customer info -->
            <div id="non_dine_fields" style="display:none;">
                <label><strong>Customer Name:</strong></label><br>
                <input type="text" name="customer_name" placeholder="Customer Name"><br><br>
                <label><strong>Phone Number:</strong></label><br>
                <input type="text" name="customer_phone" placeholder="Phone Number"><br><br>
                <!-- Delivery address shown only for delivery -->
                <div id="delivery_field" style="display:none;">
                    <label><strong>Delivery Address:</strong></label><br>
                    <textarea name="delivery_address" placeholder="Delivery Address" rows="2" cols="40"></textarea>
                    <br><br>
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
            <label><strong>General Notes:</strong></label><br>
            <textarea name="notes" rows="3" cols="50" placeholder="Any notes for this order..."></textarea>
            <br><br>

            <button type="submit" name="create_order">Place Order</button>

        </form>

    </div><!-- /.content -->
</div><!-- /.container -->

<script>
// Save the item options HTML once so new rows always have the full dropdown
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

// Show/hide fields based on order type selection
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

</body>
</html>