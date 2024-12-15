<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['otp_expires_at'])) {
    header("Location: login.php");
    exit();
}
$fullname = $_SESSION['fullname'];
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$activity = "Login Account";
$expires_at = $_SESSION['otp_expires_at'];
date_default_timezone_set('Asia/Manila'); 
$current_time = new DateTime();
$expiration_time = new DateTime($expires_at);
$time_diff = $expiration_time->getTimestamp() - $current_time->getTimestamp();

if ($time_diff <= 0) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'OTP Expired!',
                text: 'Your OTP has expired. Please request a new one.',
                icon: 'error',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'login.php';
            });
        });
    </script>";
    exit();
}

$minutes = floor($time_diff / 60);
$seconds = $time_diff % 60;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $otp = trim($_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'] . $_POST['otp5'] . $_POST['otp6']);

    $query = "SELECT * FROM authentication WHERE otp = ? AND expires_at >= NOW()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $query = "INSERT INTO tbl_activity (user_id, user, role, activity, date_activity) VALUES (?, ?, ?, ?,NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss",$user_id,$fullname, $role, $activity);
        $stmt->execute();

        $_SESSION['otp_verified'] = true; // Set session variable for successful OTP verification
        header("Location: dashboard.php"); // Redirect to the dashboard
        exit();
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Invalid OTP!',
                    text: 'The OTP you entered is incorrect or has expired.',
                    icon: 'error',
                    confirmButtonText: 'Try Again'
                });
            });
        </script>";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['email_sent']) && $_SESSION['email_sent'] === true): ?>
            Swal.fire({
                title: 'OTP Sent!',
                text: 'An OTP has been sent to your email address.',
                icon: 'info',
                confirmButtonText: 'OK'
            }).then(() => {
                Swal.showLoading();
                // Clear the session variable after displaying the alert
                <?php unset($_SESSION['email_sent']); ?>
            });
            <?php endif; ?>
        
            var minutes = <?php echo $minutes; ?>;
            var seconds = <?php echo $seconds; ?>;

            var minutesDisplay = document.querySelector('.minutes');
            var secondsDisplay = document.querySelector('.seconds');

            let alertShown = false;

            function updateCountdown() {
                if (seconds <= 0 && minutes <= 0 && !alertShown) {
                    alertShown = true;

                    minutesDisplay.innerHTML = "00";
                    secondsDisplay.innerHTML = "00";
                    document.querySelector('button[type="submit"]').disabled = true;

                    Swal.fire({
                        title: 'OTP Expired!',
                        text: 'Your OTP has expired. Please request a new one.',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'login.php';
                        } 
                    });

                    return;
                }

                if (seconds <= 0) {
                    minutes--;
                    seconds = 59;
                } else {
                    seconds--;
                }

                minutesDisplay.innerHTML = (minutes < 10 ? '0' : '') + minutes;
                secondsDisplay.innerHTML = (seconds < 10 ? '0' : '') + seconds;
            }

            updateCountdown();
            setInterval(updateCountdown, 1000);

            // Function to move focus to the next OTP input field
            function moveFocus(event) {
                if (event.target.value.length >= event.target.maxLength) {
                    let nextInput = event.target.nextElementSibling;
                    if (nextInput) {
                        nextInput.focus();
                    }
                }
            }

            // Function to allow only numbers
            function onlyNumbers(event) {
                const value = event.target.value;
                const filteredValue = value.replace(/\D/g, ''); // Remove non-numeric characters
                if (filteredValue !== value) {
                    event.target.value = filteredValue;
                }
            }

            // Add event listeners to all OTP input fields
            let otpInputs = document.querySelectorAll('input[name^="otp"]');
            otpInputs.forEach(input => {
                input.addEventListener('input', function(event) {
                    onlyNumbers(event);
                    moveFocus(event);
                });
            });
        });
    </script>
</head>
<body>
    <div class="login">
        <img src="assets/img/login-bg.jpg" alt="login image" class="login__img">
        <form method="POST" class="login__form">
            <h1 class="login__title">ENTER OTP</h1>

            <div style="text-align: center;">
                <!-- Labels -->
                <label for="quantity">Please enter the OTP below to proceed with login. If you</label><br>
                <label for="quantity">cannot see the email from "noreply@gmail.ph" in your</label><br>
                <label for="quantity">inbox, please check your Spam folder.</label>

                <br><br>

                <!-- OTP Input Fields -->
                <div>
                    <input type="text" id="otp1" name="otp1" maxlength="1" style="width: 14%; height: 55px; text-align: center; background: transparent; color: white; font-weight: bold; border: 1px solid;">
                    <input type="text" id="otp2" name="otp2" maxlength="1" style="width: 14%; height: 55px; text-align: center; background: transparent; color: white; font-weight: bold; border: 1px solid;">
                    <input type="text" id="otp3" name="otp3" maxlength="1" style="width: 14%; height: 55px; text-align: center; background: transparent; color: white; font-weight: bold; border: 1px solid;">
                    <input type="text" id="otp4" name="otp4" maxlength="1" style="width: 14%; height: 55px; text-align: center; background: transparent; color: white; font-weight: bold; border: 1px solid;">
                    <input type="text" id="otp5" name="otp5" maxlength="1" style="width: 14%; height: 55px; text-align: center; background: transparent; color: white; font-weight: bold; border: 1px solid;">
                    <input type="text" id="otp6" name="otp6" maxlength="1" style="width: 14%; height: 55px; text-align: center; background: transparent; color: white; font-weight: bold; border: 1px solid;">
                </div>

                <br>

                <!-- Timer -->
                <div>
                    <span class="minutes" style="color: blue; font-weight: bold; font-size: 18pt;"><?php echo str_pad($minutes, 2, '0', STR_PAD_LEFT); ?></span>
                    <span style="color: white; font-weight: bold; font-size: 18pt;">:</span>
                    <span class="seconds" style="color: blue; font-weight: bold; font-size: 18pt;"><?php echo str_pad($seconds, 2, '0', STR_PAD_LEFT); ?></span>
                </div>

            </div>

            <br>
            <button type="submit" class="login__button">Validate</button>
        </form>          
    </div>
</body>
</html>
