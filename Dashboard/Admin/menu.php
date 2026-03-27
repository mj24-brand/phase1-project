<?php
if (!isset($_SESSION['admin']) && !(isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
    header("Location: ../../login.php");
    exit();
}
include("../../config/db.php");

$alertMessage = "";

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ---------- Add category ----------
    if (isset($_POST['add_category'])) {
        $name = mysqli_real_escape_string($conn, trim($_POST['name']));
        $desc = mysqli_real_escape_string($conn, trim($_POST['description']));
        if (empty($name)) {
            $alertMessage = "Category name is required.";
        } else {
            $check = mysqli_query($conn, "SELECT id FROM menu_categories WHERE name = '$name'");
            if (mysqli_num_rows($check) > 0) {
                $alertMessage = "Category already exists.";
            } else {
                $query = "INSERT INTO menu_categories (name, description) VALUES ('$name', '$desc')";
                if (mysqli_query($conn, $query)) {
                    if (function_exists('logActivity')) {
                        logActivity($_SESSION['user_id'], "Added menu category", "Category: $name");
                    }
                    $alertMessage = "Category added successfully.";
                } else {
                    $alertMessage = "Database error: " . mysqli_error($conn);
                }
            }
        }
    }

    // ---------- Delete category ----------
    elseif (isset($_POST['delete_category'])) {
        // FIX: Use correct field name 'cat_id'
        $cat_id = (int) $_POST['cat_id'];
        // Check if category has any items
        $check = mysqli_query($conn, "SELECT id FROM menu_items WHERE category_id = $cat_id");
        if (mysqli_num_rows($check) > 0) {
            $alertMessage = "Cannot delete category: it contains menu items. Remove or reassign items first.";
        } else {
            $query = "DELETE FROM menu_categories WHERE id = $cat_id";
            if (mysqli_query($conn, $query)) {
                if (function_exists('logActivity')) {
                    logActivity($_SESSION['user_id'], "Deleted menu category", "Category ID: $cat_id");
                }
                $alertMessage = "Category deleted successfully.";
            } else {
                $alertMessage = "Database error: " . mysqli_error($conn);
            }
        }
    }

    // ---------- Add menu item ----------
    elseif (isset($_POST['add_item'])) {
        $category_id = (int) $_POST['category_id'];
        $name = mysqli_real_escape_string($conn, trim($_POST['name']));
        $desc = mysqli_real_escape_string($conn, trim($_POST['description']));
        $price = (float) $_POST['price'];
        $available = isset($_POST['available']) ? 1 : 0;

        // Image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../../assets/uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            $image = time() . '_' . basename($_FILES['image']['name']);
            $target_file = $target_dir . $image;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $alertMessage = "Failed to upload image.";
            }
        }

        if (empty($name) || $price <= 0 || $category_id == 0) {
            $alertMessage = "Please fill all required fields (name, price, category).";
        } else {
            $query = "INSERT INTO menu_items (category_id, name, description, price, image, available) 
                      VALUES ($category_id, '$name', '$desc', $price, '$image', $available)";
            if (mysqli_query($conn, $query)) {
                if (function_exists('logActivity')) {
                    logActivity($_SESSION['user_id'], "Added menu item", "Item: $name");
                }
                $alertMessage = "Menu item added successfully.";
            } else {
                $alertMessage = "Database error: " . mysqli_error($conn);
            }
        }
    }

    // ---------- Edit menu item ----------
    elseif (isset($_POST['edit_item'])) {
        $item_id = (int) $_POST['id'];           // hidden field name "id"
        $category_id = (int) $_POST['category_id'];
        $name = mysqli_real_escape_string($conn, trim($_POST['name']));
        $desc = mysqli_real_escape_string($conn, trim($_POST['description']));
        $price = (float) $_POST['price'];
        $available = isset($_POST['available']) ? 1 : 0;

        $image_query = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../../assets/uploads/";
            $image = time() . '_' . basename($_FILES['image']['name']);
            $target_file = $target_dir . $image;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_query = ", image = '$image'";
            } else {
                // Only set message, but still allow update without image
                $alertMessage = "Failed to upload new image, but other changes saved.";
            }
        }

        if (empty($name) || $price <= 0 || $category_id == 0) {
            $alertMessage = "Please fill all required fields (name, price, category).";
        } else {
            $query = "UPDATE menu_items SET category_id = $category_id, name = '$name', description = '$desc', 
                      price = $price, available = $available" . $image_query . " WHERE id = $item_id";
            if (mysqli_query($conn, $query)) {
                if (function_exists('logActivity')) {
                    logActivity($_SESSION['user_id'], "Edited menu item", "Item ID: $item_id");
                }
                $alertMessage = "Menu item updated successfully.";
            } else {
                $alertMessage = "Database error: " . mysqli_error($conn);
            }
        }
    }

    // ---------- Delete menu item ----------
    elseif (isset($_POST['delete_item'])) {
        // FIX: Use correct field name 'id' (the hidden input in the form)
        $item_id = (int) $_POST['id'];
        $query = "DELETE FROM menu_items WHERE id = $item_id";
        if (mysqli_query($conn, $query)) {
            if (function_exists('logActivity')) {
                logActivity($_SESSION['user_id'], "Deleted menu item", "Item ID: $item_id");
            }
            $alertMessage = "Menu item deleted successfully.";
        } else {
            $alertMessage = "Database error: " . mysqli_error($conn);
        }
    }
}

// Fetch all categories (required for display)
$categories = [];
$result_cat = mysqli_query($conn, "SELECT * FROM menu_categories ORDER BY name");
if ($result_cat) {
    while ($row = mysqli_fetch_assoc($result_cat)) {
        $categories[] = $row;
    }
}

// Fetch all menu items with category names
$items = [];
$query_items = "SELECT mi.*, mc.name as category_name 
                FROM menu_items mi 
                JOIN menu_categories mc ON mi.category_id = mc.id 
                ORDER BY mc.name, mi.name";
$result_items = mysqli_query($conn, $query_items);
if ($result_items) {
    while ($row = mysqli_fetch_assoc($result_items)) {
        $items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management</title>
</head>
<body>
    <?php if (!empty($alertMessage)): ?>
        <script>
            alert('<?php echo addslashes($alertMessage); ?>');
        </script>
    <?php endif; ?>

    <h2>Menu Categories</h2>

    <!-- Add Category Form -->
    <h3>Add New Category</h3>
    <form method="POST">
        <input type="text" name="name" placeholder="Category Name" required>
        <input type="text" name="description" placeholder="Description (optional)">
        <button type="submit" name="add_category">Add Category</button>
    </form>

    <!-- List Categories -->
    <h3>Existing Categories</h3>
    <table border="1">
        <thead>
            <tr><th>ID</th><th>Name</th><th>Description</th><th>Actions</th> </thead>
        <tbody>
        <?php foreach ($categories as $cat): ?>
           <tr>
               <td><?php echo $cat['id']; ?></td>
               <td><?php echo htmlspecialchars($cat['name']); ?></td>
               <td><?php echo htmlspecialchars($cat['description']); ?></td>
               <td>
                <form method="POST" style="display:inline;">
                    <!-- Hidden field name = 'cat_id' -->
                    <input type="hidden" name="cat_id" value="<?php echo $cat['id']; ?>">
                    <button type="submit" name="delete_category" onclick="return confirm('Delete category? This will also delete all items in it!');">Delete</button>
                </form>
               </td>
           </tr>
        <?php endforeach; ?>
        </tbody>
     </table>

    <hr>

    <h2>Menu Items</h2>

    <!-- Add Item Form -->
    <h3>Add New Menu Item</h3>
    <form method="POST" enctype="multipart/form-data">
        <select name="category_id" required>
            <option value="">Select Category</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="name" placeholder="Item Name" required>
        <textarea name="description" placeholder="Description"></textarea>
        <input type="number" step="0.01" name="price" placeholder="Price" required>
        <input type="file" name="image" accept="image/*">
        <label><input type="checkbox" name="available" checked> Available</label>
        <button type="submit" name="add_item">Add Item</button>
    </form>

    <!-- List Items with inline edit -->
    <h3>Existing Menu Items</h3>
    <table border="1">
        <thead>
            <tr><th>ID</th><th>Category</th><th>Name</th><th>Description</th><th>Price</th><th>Image</th><th>Available</th><th>Actions</th> </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
        <form method="POST" enctype="multipart/form-data">
            <!-- Hidden field name = 'id' for both edit and delete -->
            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
            <tr>
                <td><?php echo $item['id']; ?></td>
                <td>
                    <select name="category_id" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php if($cat['id'] == $item['category_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="text" name="name" value="<?php echo htmlspecialchars($item['name']); ?>" required></td>
                <td><textarea name="description"><?php echo htmlspecialchars($item['description']); ?></textarea></td>
                <td><input type="number" step="0.01" name="price" value="<?php echo $item['price']; ?>" required></td>
                <td>
                    <?php if ($item['image']): ?>
                        <img src="../../assets/uploads/<?php echo $item['image']; ?>" width="50"><br>
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*">
                </td>
                <td><input type="checkbox" name="available" <?php if($item['available']) echo 'checked'; ?>></td>
                <td>
                    <button type="submit" name="edit_item">Update</button>
                    <!-- delete button uses same hidden 'id' field -->
                    <button type="submit" name="delete_item" onclick="return confirm('Delete this item?');">Delete</button>
                </td>
             </tr>
        </form>
        <?php endforeach; ?>
        </tbody>
     </table>
</body>
</html>