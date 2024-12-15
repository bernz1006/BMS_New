<?php
session_start();
require 'db_connect.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if (isset($_SESSION['fullname'])) {
    if (isset($_SESSION['last_page'])) {
        header('Location: ' . $_SESSION['last_page']);
    } else {
        header('Location: dashboard.php');
    }
    exit();
}


$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);

    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $userEmail = $user['email'];
            $fullName = $user['fullname']; 
            $userAgent = htmlspecialchars($_SERVER['HTTP_USER_AGENT']);
            $otp = random_int(100000, 999999);
            date_default_timezone_set('Asia/Manila');
            $dateTime = new DateTime();
            $dateTime->modify('+3 minutes'); 
            $expires_at = $dateTime->format('Y-m-d H:i:s');
            
            $query = "INSERT INTO authentication (user_id, otp, created_at, expires_at) VALUES (?, ?, NOW(), ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iss", $user['user_id'], $otp, $expires_at);
            $stmt->execute();

            $_SESSION['otp_expires_at'] = $expires_at;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['fullname'] =  $fullName;
            $_SESSION['role'] = $user['role'];

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;

                $mail->Username   = 'municipalityoflaur@gmail.com';              
                $mail->Password   = 'zefp iksj tcni shew';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('noreply@example.com', 'BMS');
                $mail->addAddress($userEmail, $fullName);

                $mail->SMTPDebug = 0;

                $mail->isHTML(true);
                $mail->Subject = 'BMS LOGIN';

                $emailBody = '
                    <div style="font-family: Arial, sans-serif; line-height: 1.5; color: #333;">
                        <h2 style="color: #4CAF50;">Login OTP</h2>
                        <p>Dear ' . htmlspecialchars($fullName) . ',</p>
                        <p>You are trying to LOGIN to Budget Management System.</p>
                        <p style="margin-top: 20px;"><strong>Please note:</strong> To confirm your request, please use the 6-digit code below:</p>
                        <div style="background-color: #f8f8f8; padding: 10px; border-radius: 5px; margin-top: 20px;">
                        <h1>'. $otp .'</h1>
                    </div>
                        <p style="color: #555; font-size: 14px;">The verification code will be valid for 3 minutes. Please do not share your code with anyone.</p>
                    </div>
                ';

                $mail->Body = $emailBody;
                $mail->send();

                // Set a success message in the session or return a specific response
                $_SESSION['email_sent'] = true;
                header("Location: otp_form.php");
                exit();
            } catch (Exception $e) {
                error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
                $error = "Unable to send OTP. Please try again later.";
            }
        } else {
            // $error = "Invalid password.";
            echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Invalid password!',
                    text: 'Please Enter Correct Password.',
                    icon: 'error',
                    confirmButtonText: 'Try Again'
                });
            });
        </script>";
        }
    } else {
        // $error = "No account found with that username.";
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Invalid Username!',
                    text: 'Please Enter Correct Username.',
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
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
    <div class="login">
        <img src="assets/img/login-bg.jpg" alt="login image" class="login__img">
        <form id="loginForm" method="POST" class="login__form">
            <h1 class="login__title">Login</h1>
            <div class="login__content">
                <div class="login__box">
                    <i class="ri-user-3-line login__icon"></i>
                    <div class="login__box-input">
                        <input type="text" required class="login__input" id="login-username" name="username" placeholder=" " autocomplete="off">
                        <label for="login-username" class="login__label">Username</label>
                    </div>
                </div>
                <div class="login__box">
                    <i class="ri-lock-2-line login__icon"></i>
                    <div class="login__box-input">
                        <input type="password" required class="login__input" id="login-pass" name="password" placeholder=" ">
                        <label for="login-pass" class="login__label">Password</label>
                        <i class="ri-eye-off-line login__eye" id="login-eye"></i>
                    </div>
                </div>
            </div>
            <button type="submit" class="login__button">Login</button>
        </form>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>

