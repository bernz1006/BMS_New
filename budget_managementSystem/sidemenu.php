<?php
$current_page = basename($_SERVER['PHP_SELF']);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['last_page'] = $current_page;

if (isset($_SESSION['fullname']) && isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $fullname = $_SESSION['fullname'];
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
} else {
    $fullname = "Guest";
    $user_id = "Unknown";
    $role = "Unknown";
}
?>

<link rel="stylesheet" href="assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<section id="sidebar">
     <div style="background-color:darkblue; height:40px;">
        <span class="text" style="font-size:18pt; color:lightgreen; padding-left:3px;">B</span><span class="text" style="color:white;">UDGET</span>
		<span class="text" style="font-size:18pt;color:lightgreen; padding-left:3px;">M</span><span class="text" style="color:white;">ANAGEMENT</span>
		<span class="text" style="font-size:18pt;color:lightgreen; padding-left:3px;">S</span><span class="text" style="color:white;">YSTEM</span>
    </div>
	<br>
	 <img src="assets/img/laur.png" alt="Logo" style="height: 100px; margin-left: 80px;">
	 <br> <br>
	 <div style="background-color:darkblue; height:75px;">
 
    <span class="text" style="font-size:10pt; padding-left:25px; color:white; font-weight:bold;">
        <i class='bx bxs-user'></i> 
        USER: <?= $fullname; ?>
    </span>
    <br>
    <span class="text" style="font-size:10pt; padding-left:25px; color:white; font-family:arial;">
        <i class='bx bxs-id-card'></i> 
        POSITION: <?= $role; ?>
    </span>
    <br>
    <span class="text" style="font-size:10pt; padding-left:25px; color:white; font-family:arial;">
        <i class='bx bxs-user-badge'></i> 
        USER ID: <?= $user_id; ?>
    </span>
</div>
<ul class="side-menu top">
        <li class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
            <a href="./dashboard.php">
                <i class='bx bxs-dashboard'></i>
                <span class="text">Dashboard</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'office.php') ? 'active' : ''; ?>">
            <a href="./office.php">
                <i class='bx bxs-buildings'></i>
                <span class="text">Departments/Offices</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'budget.php') ? 'active' : ''; ?>">
            <a href="./budget.php">
                <i class='bx bxs-wallet-alt'></i>
                <span class="text">Budget Management</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'supplimental_realignment.php') ? 'active' : ''; ?>">
            <a href="./supplimental_realignment.php">
                <i class='bx bxs-wallet-alt'></i>
                <span class="text">Supplimental/Realignment</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'archive.php') ? 'active' : ''; ?>">
            <a href="./archive.php">
                <i class='bx bxs-bar-chart-alt-2'></i>
                <span class="text">Archive</span>
            </a>
        </li>
   
        <li class="<?php echo ($current_page == 'account.php') ? 'active' : ''; ?>"
        style="<?php echo (strcasecmp($role, 'Administrator') === 0) ? 'display:block;' : 'display:none;'; ?>">
            <a href="./account.php">
                <i class='bx bxs-user-detail'></i>
                <span class="text">Account Management</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'activity.php') ? 'active' : ''; ?>" 
        style="<?php echo (strcasecmp($role, 'Encoder') === 0) ? 'display:none;' : 'display:block;'; ?>">
            <a href="./activity.php">
                <i class='bx bxs-file-find'></i>
                <span class="text">Activity Logs</span>
            </a>
        </li>
        <li>
            <a href="#" class="logout" id="logoutButton">
                <i class='bx bxs-log-out-circle'></i>
                <span class="text">Logout</span>
            </a>
        </li>

    </ul>
    <br>
<br>
<div id="date-time" style="color:white; padding-left:25px;"></div>
</section>
<script>
function updateTime() {
    const now = new Date();
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const day = days[now.getDay()];
    const date = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    const time = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

    document.getElementById('date-time').innerHTML = `<strong>${day}, ${date}</strong><br>${time}`;
}

setInterval(updateTime, 1000);
updateTime();
function toggleDropdown(id) {
    const dropdown = document.getElementById(id);
    if (dropdown.style.display === 'none' || dropdown.style.display === '') {
        dropdown.style.display = 'block';
    } else {
        dropdown.style.display = 'none';
    }
}
document.getElementById("logoutButton").addEventListener("click", function () {
    Swal.fire({
        title: 'Are you sure?',
        text: "You will be logged out of your session.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, log out',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Proceed with the logout
            fetch('logout.php', { method: 'POST' })
                .then(response => response.text())
                .then(data => {
                    Swal.fire({
                        title: 'Logged out successfully!',
                        text: 'You will be redirected to the login page.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = "login.php";
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred during logout.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        }
    });
});

</script>