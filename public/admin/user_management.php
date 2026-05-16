<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

include('../../app/middleware/admin.php');
include('../../app/config/config.php');
include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/sidebar.php');
include(__DIR__ . '/includes/topbar.php');
?>

<div class="container">

    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Registered Users</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $usersQuery = "SELECT user_id, firstName, lastName, username, emailAddress, role FROM users WHERE role = 'user' ORDER BY firstName ASC";
                            $result = $conn->query($usersQuery);

                            if ($result && $result->num_rows > 0) {
                                while ($user = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) . "</td>";
                                    echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                                    echo "<td>" . htmlspecialchars($user['emailAddress']) . "</td>";
                                    echo "<td><span class='badge badge-primary'>" . htmlspecialchars($user['role']) . "</span></td>";
                                    echo "<td>
    <button 
        type='button' 
        class='btn btn-danger btn-sm deleteUserBtn' 
        data-toggle='modal' 
        data-target='#confirmDeleteUser'
        data-userid='" . htmlspecialchars($user['user_id']) . "'
        data-username='" . htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) . "'
        onclick='setDeleteUser(this)'>
        <i class='fas fa-trash'></i> Delete
    </button>
</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center'>No users found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</div>


<div class="modal fade" id="confirmDeleteUser" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title w-100 text-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Confirm Delete
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>

            <form method="POST" action="../../app/controllers/adminBackend/userController.php" id="deleteUserForm">
                <div class="modal-body text-center">
                    <p class="mb-1">You are about to delete the user:</p>
                    <h5 class="font-weight-bold text-danger" id="deleteUserName"></h5>

                    <!-- make sure it says name="userId" not name="userUuid" -->
                    <input type="hidden" id="deleteUserId" name="userId" value="">
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="deleteUser" class="btn btn-danger px-4">Yes, Delete</button>
                </div>
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

<!-- Modal trigger JS — place this after the session block -->
<script>
    function setDeleteUser(button) {
        var userId = button.getAttribute('data-userid');
        var userName = button.getAttribute('data-username');

        // Validate userId
        if (!userId || userId.trim() === '') {
            alert('Error: User ID is missing.');
            return false;
        }

        document.getElementById('deleteUserId').value = userId;
        document.getElementById('deleteUserName').textContent = userName;
        return true;
    }
</script>








</div>



<?php
include(__DIR__ . '/includes/footer.php');
?>