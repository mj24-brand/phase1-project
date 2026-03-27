<?php
if (!isset($_SESSION['admin']) && !(isset($_SESSION['role']) && $_SESSION['role'] === 'admin')) {
    header("Location: ../../login.php");
    exit();
}
include("../../config/db.php");

$alertMessage = "";

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add table
    if (isset($_POST['add_table'])) {
        $number = mysqli_real_escape_string($conn, $_POST['table_number']);
        $capacity = (int) $_POST['capacity'];
        $section = mysqli_real_escape_string($conn, $_POST['section']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);

        $check = mysqli_query($conn, "SELECT id FROM tables WHERE table_number = '$number'");
        if (mysqli_num_rows($check) > 0) {
            $alertMessage = "Table number already exists.";
        } else {
            $query = "INSERT INTO tables (table_number, capacity, section, status) VALUES ('$number', $capacity, '$section', '$status')";
            if (mysqli_query($conn, $query)) {
                $log = "INSERT INTO activity_logs (user_id, action, details, ip_address) 
                        VALUES ({$_SESSION['user_id']}, 'Added table', 'Table $number, status $status', '{$_SERVER['REMOTE_ADDR']}')";
                mysqli_query($conn, $log);
                $alertMessage = "Table added successfully!";
            } else {
                $alertMessage = "Database error: " . mysqli_error($conn);
            }
        }
    }
    // Edit table
    elseif (isset($_POST['edit_table'])) {
        $id = (int) $_POST['id'];
        $number = mysqli_real_escape_string($conn, $_POST['table_number']);
        $capacity = (int) $_POST['capacity'];
        $section = mysqli_real_escape_string($conn, $_POST['section']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);

        $check = mysqli_query($conn, "SELECT id FROM tables WHERE table_number = '$number' AND id != $id");
        if (mysqli_num_rows($check) > 0) {
            $alertMessage = "Table number already exists.";
        } else {
            $query = "UPDATE tables SET table_number='$number', capacity=$capacity, section='$section', status='$status' WHERE id=$id";
            if (mysqli_query($conn, $query)) {
                $log = "INSERT INTO activity_logs (user_id, action, details, ip_address) 
                        VALUES ({$_SESSION['user_id']}, 'Edited table', 'Table ID $id', '{$_SERVER['REMOTE_ADDR']}')";
                mysqli_query($conn, $log);
                $alertMessage = "Table updated successfully!";
            } else {
                $alertMessage = "Database error: " . mysqli_error($conn);
            }
        }
    }
    // Delete table
    elseif (isset($_POST['delete_table'])) {
        $id = (int) $_POST['id'];
        // Optional: check if table is in active orders before deleting
        $check = mysqli_query($conn, "SELECT id FROM orders WHERE table_id = $id AND status NOT IN ('paid','cancelled')");
        if (mysqli_num_rows($check) > 0) {
            $alertMessage = "Cannot delete table because it has active orders.";
        } else {
            $query = "DELETE FROM tables WHERE id = $id";
            if (mysqli_query($conn, $query)) {
                $log = "INSERT INTO activity_logs (user_id, action, details, ip_address) 
                        VALUES ({$_SESSION['user_id']}, 'Deleted table', 'Table ID $id', '{$_SERVER['REMOTE_ADDR']}')";
                mysqli_query($conn, $log);
                $alertMessage = "Table deleted successfully!";
            } else {
                $alertMessage = "Database error: " . mysqli_error($conn);
            }
        }
    }
}

// Fetch all tables (always after POST processing)
$tables = [];
$result = mysqli_query($conn, "SELECT * FROM tables ORDER BY table_number");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $tables[] = $row;
    }
} else {
    $alertMessage = "Error fetching tables: " . mysqli_error($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tables</title>
</head>
<body>
    <?php if (!empty($alertMessage)): ?>
        <script>
            alert('<?php echo addslashes($alertMessage); ?>');
        </script>
    <?php endif; ?>

    <h3>Add New Table</h3>
    <form method="POST">
        <input type="number" name="table_number" placeholder="Table Number" required>
        <input type="number" name="capacity" placeholder="Capacity" required>
        <select name="section">
            <option value="AC">AC</option>
            <option value="Non AC">Non AC</option>
            <option value="Outdoor">Outdoor</option>
        </select>
        <select name="status">
            <option value="available">Available</option>
            <option value="occupied">Occupied</option>
            <option value="reserved">Reserved</option>
            <option value="cleaning">Cleaning</option>
        </select>
        <button type="submit" name="add_table">Add Table</button>
    </form>

    <h3>Existing Tables</h3>
    <table border="1">
        <thead>
            <tr><th>ID</th><th>Number</th><th>Capacity</th><th>Section</th><th>Status</th><th>Actions</th>               </thead>
        <tbody>
        <?php foreach ($tables as $t): ?>
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
            <tr>
                <td><?php echo $t['id']; ?></td>
                <td><input type="number" name="table_number" value="<?php echo $t['table_number']; ?>" required> </td>
                <td><input type="number" name="capacity" value="<?php echo $t['capacity']; ?>" required> </td>
                <td>
                    <select name="section">
                        <option value="AC" <?php if($t['section']=='AC') echo 'selected'; ?>>AC</option>
                        <option value="Non AC" <?php if($t['section']=='Non AC') echo 'selected'; ?>>Non AC</option>
                        <option value="Outdoor" <?php if($t['section']=='Outdoor') echo 'selected'; ?>>Outdoor</option>
                    </select>
                </td>
                <td>
                    <select name="status">
                        <option value="available" <?php if($t['status']=='available') echo 'selected'; ?>>Available</option>
                        <option value="occupied" <?php if($t['status']=='occupied') echo 'selected'; ?>>Occupied</option>
                        <option value="reserved" <?php if($t['status']=='reserved') echo 'selected'; ?>>Reserved</option>
                        <option value="cleaning" <?php if($t['status']=='cleaning') echo 'selected'; ?>>Cleaning</option>
                    </select>
                </td>
                <td>
                    <button type="submit" name="edit_table">Update</button>
                    <button type="submit" name="delete_table" onclick="return confirm('Delete this table?');">Delete</button>
                </td>
             </tr>
        </form>
        <?php endforeach; ?>
        </tbody>
     </table>
</body>
</html>