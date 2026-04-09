            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright. Your Website 2021</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

            </div>
            <!-- End of Content Wrapper -->

            </div>
            <!-- End of Page Wrapper -->

            <!-- Scroll to Top Button-->
            <a class="scroll-to-top rounded" href="#page-top">
                <i class="fas fa-angle-up"></i>
            </a>
            
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
            <script src="./assets/vendor/jquery/jquery.min.js"></script>
            <script src="./assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

            <!-- Core plugin JavaScript-->
            <script src="./assets/vendor/jquery-easing/jquery.easing.min.js"></script>

            <!-- Custom scripts for all pages-->
            <script src="./assets/js/sb-admin-2.min.js"></script>

            <!-- Page level plugins -->
            <script src="./assets/vendor/chart.js/Chart.min.js"></script>
            <script src="./assets/vendor/datatables/jquery.dataTables.min.js"></script>
            <script src="./assets/vendor/datatables/dataTables.bootstrap4.min.js"></script>

            <!-- Page level custom scripts -->
            <script src="./assets/js/demo/chart-area-demo.js"></script>
            <script src="./assets/js/demo/chart-pie-demo.js"></script>
            <script src="./assets/js/demo/datatables-demo.js"></script>

            <!-- sweetAlert2 Link -->
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


            </body>

            </html>