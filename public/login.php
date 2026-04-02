<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Login</title>

  <!-- Custom fonts for this template-->
  <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link
    href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
    rel="stylesheet">

  <!-- Custom styles for this template-->
  <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
  <link href="assets/css/sb-admin-2.css" rel="stylesheet">

</head>

<body class="bg-gradient-primary" id="login-background">

  <div class="container">

    <!-- Outer Row -->
    <div class="row justify-content-center">

      <div class="col-xl-10 col-lg-12 col-md-9">

        <div class="card o-hidden border-0 shadow-lg my-5">
          <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row">
              <div class="col-lg-6 d-none d-lg-block bg-login-image">
                <img src="assets/img/library.jpg" alt="Login Image" class="img-fluid" style="height: 100%; width: 100%; object-fit: cover;">
              </div>
              <div class="col-lg-6">
                <div class="p-5">

                  <!-- Welcome Back Label -->
                  <div class="text-center">
                    <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                  </div>

                  <!-- action="includes/login.inc.php" -->
                  <form class="user needs-validation" method="post" action="../app/controllers/loginController.php" enctype="multipart/form-data" autocomplete="off" novalidate>
                    <!-- Email input -->
                    <div data-mdb-input-init class="form-outline mb-4">
                      <input type="text" id="username" placeholder="" name="username" class="form-control" required />
                      <label class="form-label" for="username">Username</label>
                    </div>

                    <!-- Password input -->
                    <div data-mdb-input-init class="form-outline mb-4">
                      <input type="password" id="password" placeholder="" name="password" class="form-control" required />
                      <label class="form-label" for="password">Password</label>
                    </div>


                    <!-- Submit button -->
                    <button type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-block mb-4" name="login">Login</button>

                    <!-- Register buttons -->
                    <div class="text-center">
                      <p>Not a member? <a href="register">Register</a></p>



                    </div>
                  </form>



                  <hr>
                  <div class="text-center">
                    <a class="small" href="forgot-password">Forgot Password?</a>
                  </div>
                  <div class="text-center">
                    <a class="small" href="register">Create an Account!</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>

  </div>

  <!-- sweetAlert2 Link -->
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






  <!-- Bootstrap core JavaScript-->
  <script src="assets/vendor/jquery/jquery.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Core plugin JavaScript-->
  <script src="assets/vendor/jquery-easing/jquery.easing.min.js"></script>

  <!-- Custom scripts for all pages-->
  <script src="assets/js/sb-admin-2.min.js"></script>

  
  

</body>

</html>