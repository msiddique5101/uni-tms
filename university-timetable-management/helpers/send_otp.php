    <?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require '../include/PHPMailer/src/PHPMailer.php';
    require '../include/PHPMailer/src/SMTP.php';
    require '../include/PHPMailer/src/Exception.php';

    function sendOTP($email, $otp) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';  // Replace with your SMTP host
            $mail->SMTPAuth = true;
            $mail->Username = 'msiddique5102@gmail.com';  // Replace with your email
            $mail->Password = 'afya ocpe yahx wkgd';  // Replace with your password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('msiddique5102@gmail.com', 'University Admin');
            $mail->addAddress($email);

            $mail->Subject = 'Your OTP Code';
            $mail->Body = "Your OTP code is: $otp";

            if (!$mail->send()) {
                return false;
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    ?>
