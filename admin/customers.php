<?php include("../includes/header.php"); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Customers</h2>
    <a href="add_customer.php" class="btn btn-primary">
        <i class="fa fa-plus"></i> Add Customer
    </a>
</div>

<!-- Search Bar -->
<form method="GET" class="mb-3">
    <div class="input-group">
        <input type="text" name="search" class="form-control" placeholder="Search customers...">
        <button class="btn btn-dark">Search</button>
    </div>
</form>

<!-- Card Container -->
<div class="card shadow border-0">
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-hover align-middle">

                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Loyalty</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php
                include("../config/db.php");

                $where = "";
                if(isset($_GET['search'])){
                    $s = $_GET['search'];
                    $where = "WHERE name LIKE '%$s%'";
                }

                $res = $conn->query("SELECT * FROM customers $where");

                while($row = $res->fetch_assoc()){
                ?>
                    <tr>
                        <td><?= $row['id'] ?></td>

                        <!-- Customer Info -->
                        <td>
                            <strong><?= $row['name'] ?></strong><br>
                            <small class="text-muted"><?= $row['email'] ?></small>
                        </td>

                        <!-- Contact -->
                        <td><?= $row['phone'] ?></td>

                        <!-- Loyalty Badge -->
                        <td>
                            <?php
                            $points = $row['loyalty_points'];

                            if($points > 500){
                                echo '<span class="badge bg-warning text-dark">Gold</span>';
                            } elseif($points > 200){
                                echo '<span class="badge bg-secondary">Silver</span>';
                            } else {
                                echo '<span class="badge bg-success">Normal</span>';
                            }
                            ?>
                            <br>
                            <small><?= $points ?> pts</small>
                        </td>

                        <!-- Actions -->
                        <td>
                            <a href="view_customer.php?id=<?= $row['id'] ?>" 
                               class="btn btn-sm btn-info">
                               <i class="fa fa-eye"></i>
                            </a>

                            <a href="edit_customer.php?id=<?= $row['id'] ?>" 
                               class="btn btn-sm btn-warning">
                               <i class="fa fa-edit"></i>
                            </a>

                            <a href="delete_customer.php?id=<?= $row['id'] ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this customer?')">
                               <i class="fa fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>

            </table>
        </div>

    </div>
</div>

<?php include("../includes/footer.php"); ?>