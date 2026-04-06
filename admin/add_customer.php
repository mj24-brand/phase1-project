<?php 
include("auth.php");
include("../includes/header.php"); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Add Customer</h2>
    <a href="customers.php" class="btn btn-secondary">
        <i class="fa fa-arrow-left"></i> Back
    </a>
</div>

<div class="card shadow border-0">
    <div class="card-body">

        <form method="POST">

            <div class="row">

                <!-- Name -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Customer Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter full name" required>
                </div>

                <!-- Phone -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="Enter phone number">
                </div>

                <!-- Email -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter email">
                </div>

                <!-- Loyalty Points -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Loyalty Points</label>
                    <input type="number" name="points" class="form-control" value="0">
                </div>

                <!-- Address -->
                <div class="col-md-12 mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="3" placeholder="Enter address"></textarea>
                </div>

            </div>

            <!-- Buttons -->
            <div class="d-flex justify-content-end">
                <button type="reset" class="btn btn-light me-2">Reset</button>
                <button type="submit" name="save" class="btn btn-primary">
                    <i class="fa fa-save"></i> Save Customer
                </button>
            </div>

        </form>

        <?php
        include("../config/db.php");

        if(isset($_POST['save'])){
            $name = $_POST['name'];
            $phone = $_POST['phone'];
            $email = $_POST['email'];
            $address = $_POST['address'];
            $points = $_POST['points'];

            $conn->query("INSERT INTO customers(name,phone,email,address,loyalty_points)
                          VALUES('$name','$phone','$email','$address','$points')");

            echo "<div class='alert alert-success mt-3'>
                    Customer added successfully!
                  </div>";
        }
        ?>

    </div>
</div>

<?php include("../includes/footer.php"); ?>