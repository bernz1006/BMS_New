
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require 'db_connect.php';

$userAgent = $_SERVER['HTTP_USER_AGENT'];
$ipAddress = $_SERVER['REMOTE_ADDR'];
$otp = rand(100000, 999999);
$expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

$user_id = 1; 
$query = "INSERT INTO authentication (user_id, otp, created_at, expires_at) VALUES (?, ?, NOW(), ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iss", $user_id, $otp, $expires_at);

if ($stmt->execute()) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();                                          
        $mail->Host       = 'smtp.gmail.com';                     
        $mail->SMTPAuth   = true;                                 
        $mail->Username   = 'municipalityoflaur@gmail.com';              
        $mail->Password   = 'zefp iksj tcni shew';  // Use the generated App Password here
                        // $mail->Username   = 'bernzbauat8@gmail.com';
                // $mail->Password   = 'qmxg wcze ydpm fsje';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;       
        $mail->Port       = 587;                                  

        $mail->setFrom('noreply@gmail.com', 'BMS');
        $mail->addAddress('bauatbernz8@gmail.com', 'BMS');            

        // Enable SMTP Debugging
        $mail->SMTPDebug = 2; // Use 3 for more details

        $mail->isHTML(true);                                  
        $mail->Subject = 'Login OTP';

        $emailBody = '
            <div style="font-family: Arial, sans-serif; line-height: 1.5; color: #333;">
                <h2 style="color: #4CAF50;">Login OTP</h2>
                <p>Dear Mr/Mrs. Roj Ortiz,</p>
                <p>You are trying to LOGIN to Budget Management System.</p>
                <div style="background-color: #f8f8f8; padding: 10px; border-radius: 5px; margin-top: 20px;">
                    <p><strong>Device:</strong> ' . $userAgent . '</p>
                </div>
                <p style="margin-top: 20px;"><strong>Please note:</strong> To confirm your request, please use the 6-digit code below:</p>
                <h1 style="font-size: 36px; color: #333; text-align: center;">' . $otp . '</h1>
                <p style="color: #555; font-size: 14px;">The verification code will be valid for 5 minutes. Please do not share your code with anyone.</p>
            </div>
        ';

        $mail->Body = $emailBody;
        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Error saving OTP to the database: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
