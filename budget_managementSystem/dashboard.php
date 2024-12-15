<?php
session_start();

if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    // Redirect to the OTP page if not verified
    header("Location: otp_form.php");
    exit();
}

require 'db_connect.php';

// Query for other departments/offices
$query = "select department_name, budget, balance from tbl_departments WHERE identifier != 'MO' and budget != 0";
$result = mysqli_query($conn, $query);

// Query for Mayor Office (MO)
$querymo = "select department_name, budget, balance from tbl_departments WHERE identifier = 'MO'";
$resultmo = mysqli_query($conn, $querymo);

// Fetch Mayor Office data
$row2 = mysqli_fetch_assoc($resultmo);

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/img/laur.png">
	<title>BMS</title>
</head>
<body>

	<!-- SIDEBAR -->
	<?php require("./sidemenu.php"); ?>
	<!-- SIDEBAR -->

	<!-- CONTENT -->
	<section id="content">
		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Dashboard</h1>
				</div>
			</div>

			<ul class="box-info">
				<li>
					<i class='bx bxs-buildings'></i>
					<span class="text">
						<h3><?php echo $row2['department_name']; ?></h3>
						<p>Departments/Office</p>
					</span>
				</li>

				<!-- Balance Section for MO -->
				<li>
					<i class='bx bxs-wallet'></i> <!-- Wallet icon for Balance -->
					<span class="text">
						<h3>₱<?php echo number_format($row2['budget'], 2); ?></h3>
						<p>Total Budget</p>
					</span>
				</li>

				<!-- Total Use Section for MO -->
				<li>
					<i class='bx bxs-bar-chart-alt-2'></i> <!-- Bar chart icon for Total Use -->
					<span class="text">
						<h3>₱<?php echo number_format($row2['balance'], 2); ?></h3>
						<p>Total Unused Funds</p>
					</span>
				</li>
			</ul>

			<!-- Other Departments/Office Section -->
			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Other Departments/Office</h3>
						<i class='bx bx-search'></i>
						<i class='bx bx-filter'></i>
					</div>
					<table>
						<thead>
							<tr>
								<th>Departments/Office</th>
								<th>Total Budget</th>
								<th>Total Unused Funds</th>
							</tr>
						</thead>
						<tbody>
						<?php while ($row = mysqli_fetch_assoc($result)) { ?>
							<tr>
								<td><?php echo $row['department_name']; ?></td>
								<td>₱<?php echo number_format($row['budget'], 2); ?></td>
								<td>₱<?php echo number_format($row['balance'], 2); ?></td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->

	<script src="assets/js/script.js"></script>
</body>
</html>
