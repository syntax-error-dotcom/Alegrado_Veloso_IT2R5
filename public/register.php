<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>SB Admin 2 - Register</title>

    <!-- Custom fonts for this template-->
    <link href="assetsvendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="assets/css/sb-admin-2.css" rel="stylesheet">


</head>

<body class="bg-gradient-primary" id="login-background">

    <div class="container">

        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="row">
                    <div class="col-lg-5 d-none d-lg-block bg-register-image">
                        <img src="assets/img/library.jpg" alt="Login Image" class="img-fluid" style="height: 100%; width: 100%; object-fit: cover;">
                    </div>
                    <div class="col-lg-7">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4">Create an Account!</h1>
                                <h3 class="h6 text-gray-700 mb-4">Enter your personal details to join Us!.</h3>
                            </div>

                            <div class="d-flex align-items-center my-3">
                                    <hr class="flex-grow-3">
                                        <span class="mx-2 text-muted">Tell us who you are!</span>
                                    <hr class="flex-grow-1">
                                </div>

                            <form class="user needs-validation" autocomplete="off" method="post" action="../app/controllers/registerController.php" enctype="multipart/form-data" novalidate>
                                <div class="form-group row">
                                    <div class="col-sm-6 mb-2 mb-sm-0">
                                        <label for="firstName" class="form-label mb-1">First Name</label>
                                        <input type="text" class="form-control form-control" id="firstName" name="firstName"
                                            placeholder="ex. John Mark" required>
                                        <div class="invalid-feedback">
                                            Please enter your first name.
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="middleName" class="form-label mb-1">Middle Name</label>
                                        <input type="text" class="form-control form-control" id="middleName" name="middleName"
                                            placeholder="optional. ex. Lopez" >
                                        <div class="invalid-feedback">
                                            Please enter your middle name.
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group row">
                                    <div class="col-sm-12 mb-3 mb-sm-0">
                                        <label for="lastName" class="form-label mb-1">Last Name</label>
                                        <input type="text" class="form-control form-control" id="lastName " name="lastName"
                                            placeholder="ex. Dela Cruz" required>
                                        <div class="invalid-feedback">
                                            Please enter your last name.
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group row">
                                    <div class="col-sm-12 mb-3 mb-sm-0">
                                        <label for="emailAddress" class="form-label mb-1">Email Address</label>
                                        <input type="email" class="form-control form-control" id="emailAddress " name="emailAddress"
                                            placeholder="ex. john.doe@example.com" required>
                                        <div class="invalid-feedback">
                                            Please enter your email address.
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-12 mb-3 mb-sm-0">
                                        <label for="username" class="form-label mb-1">Username</label>
                                        <input type="text" class="form-control form-control" id="username" name="username"
                                            placeholder="ex. john.doe" required>
                                        <div class="invalid-feedback">
                                            Please enter a username.
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <label for="firstName" class="form-label mb-1">First Name</label>
                                        <input type="password" class="form-control form-control"
                                            id="password" name="password" placeholder="" required>
                                        <div class="invalid-feedback">
                                            Please enter a password.
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="exampleRepeatPassword" class="form-label mb-1">Repeat Password</label>
                                        <input type="password" class="form-control form-control"
                                            id="repeatPassword" name="repeatPassword" placeholder="" required>
                                        <div class="invalid-feedback">
                                            Password don't match.
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center my-3">
                                    <hr class="flex-grow-3">
                                        <span class="mx-2 text-muted">Tell us where you live!</span>
                                    <hr class="flex-grow-1">
                                </div>


                                <div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <label for="street" class="form-label mb-1">Street Address</label>
                                        <input type="text" class="form-control form-control"
                                            id="street" name="street" placeholder="ex. Gayloa St." required>
                                        <div class="invalid-feedback">
                                            Please enter your street address.
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="barangay" class="form-label mb-1">Barangay</label>
                                        <input type="text" class="form-control form-control"
                                            id="barangay" name="barangay" placeholder="ex. Barangay 3" required>
                                        <div class="invalid-feedback">
                                            Please enter your barangay.
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <label for="city" class="form-label mb-1">City</label>
                                        <input type="text" class="form-control form-control"
                                            id="city" name="city" placeholder=" ex. Cagayan de Oro" required>
                                        <div class="invalid-feedback">
                                            Please enter your city.
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="zip" class="form-label mb-1">Zip Code</label>
                                        <input type="text" class="form-control form-control"
                                            id="zip" name="zip" placeholder="ex. 9000" required>
                                        <div class="invalid-feedback">
                                            Please enter your zip code.
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" name="register" class="btn btn-primary btn-user btn-block">
                                    Create Account
                                </button>

                            </form>
                            <hr>
                            <div class="text-center">
                                <a class="small" href="forgot-password">Forgot Password?</a>
                            </div>
                            <div class="text-center">
                                <a class="small" href="login">Already have an account? Login!</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php
    session_start(); // make sure session is started at the very top of the file

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

    <!-- Bootstrap core JavaScript-->
    <script src="assets/vendor/jquery/jquery.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="assets/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="assets/js/register-form.js"></script>

    <script src="assets/js/sb-admin-2.min.js"></script>

    <!-- sweetAlert2 Link -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</body>

</html>