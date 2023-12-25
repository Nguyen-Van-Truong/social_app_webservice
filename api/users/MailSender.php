<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/phpmailer/phpmailer/src/Exception.php';
require '../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../../vendor/phpmailer/phpmailer/src/SMTP.php';

class MailSender {
    public static function sendMail($to, $subject, $body) {
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'socialapp341@gmail.com';
            $mail->Password = 'hcmfpgieezkpoaoe';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Set charset to UTF-8
            $mail->CharSet = 'UTF-8';

            //Recipients
            $mail->setFrom('socialapp341@gmail.com', 'SocialApp');
            $mail->addAddress($to);

            //Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

?>