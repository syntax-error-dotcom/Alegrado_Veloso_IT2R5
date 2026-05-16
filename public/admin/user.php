<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

include('../../app/middleware/admin.php');
include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/sidebar.php');
include(__DIR__ . '/includes/topbar.php');
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Users Management</h1>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Create New User Form Column -->
        <div class="container-fluid">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Create New User Account</h6>
                </div>
                <div class="card-body">
                    <form class="user needs-validation" autocomplete="off" method="post" action="../../app/controllers/adminBackend/userController.php" enctype="multipart/form-data" novalidate>
                        
                        <div class="form-group row">
                            <div class="col-sm-6 mb-2 mb-sm-0">
                                <label for="firstName" class="form-label mb-1">First Name <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="firstName" name="firstName" placeholder="ex. John Mark" required>
                                <div class="invalid-feedback">
                                    Please enter first name.
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="middleName" class="form-label mb-1">Middle Name</label>
                                <input type="text" class="form-control" id="middleName" name="middleName" placeholder="optional. ex. Lopez">
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-12 mb-3">
                                <label for="lastName" class="form-label mb-1">Last Name <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="lastName" name="lastName" placeholder="ex. Dela Cruz" required>
                                <div class="invalid-feedback">
                                    Please enter last name.
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-12 mb-3">
                                <label for="emailAddress" class="form-label mb-1">Email Address <span style="color: red;">*</span></label>
                                <input type="email" class="form-control" id="emailAddress" name="emailAddress" placeholder="ex. john.doe@example.com" required>
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-12 mb-3">
                                <label for="username" class="form-label mb-1">Username <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="ex. johndoe" required>
                                <div class="invalid-feedback">
                                    Please enter a username.
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <label for="password" class="form-label mb-1">Password <span style="color: red;">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="" required>
                                <div class="invalid-feedback">
                                    Please enter a password.
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="repeatPassword" class="form-label mb-1">Repeat Password <span style="color: red;">*</span></label>
                                <input type="password" class="form-control" id="repeatPassword" name="repeatPassword" placeholder="" required>
                                <div class="invalid-feedback">
                                    Passwords don't match.
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group row">
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <label for="street" class="form-label mb-1">Street Address <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="street" name="street" placeholder="ex. Gayloa St." required>
                                <div class="invalid-feedback">
                                    Please enter street address.
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="barangay" class="form-label mb-1">Barangay <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="barangay" name="barangay" placeholder="ex. Barangay 3" required>
                                <div class="invalid-feedback">
                                    Please enter barangay.
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-6 mb-3 mb-sm-0">
                                <label for="city" class="form-label mb-1">City <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="city" name="city" placeholder="ex. Cagayan de Oro" required>
                                <div class="invalid-feedback">
                                    Please enter city.
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="zip" class="form-label mb-1">Zip Code <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="zip" name="zip" placeholder="ex. 9000" required>
                                <div class="invalid-feedback">
                                    Please enter zip code.
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="adminCreateUser" class="btn btn-primary btn-user btn-block">
                            Create User Account
                        </button>
                    </form>
                </div>
            </div>
        </div>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
if (isset($_SESSION['message']) && $_SESSION['code'] != '') {
?>
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        Toast.fire({
            icon: "<?php echo $_SESSION['code']; ?>",
            title: "<?php echo $_SESSION['message']; ?>"
        });
    </script>
<?php
    unset($_SESSION['message']);
    unset($_SESSION['code']);
}
?>

<?php
include(__DIR__ . '/includes/footer.php');
?>