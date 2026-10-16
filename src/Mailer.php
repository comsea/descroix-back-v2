<?php

namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class Mailer
{
    public static function send(string $subject, string $body, ?string $replyTo = null): bool
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $_ENV['BREVO_SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['BREVO_SMTP_USERNAME'];
            $mail->Password   = $_ENV['BREVO_SMTP_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int) $_ENV['BREVO_SMTP_PORT'];
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME'] ?? '');
            $mail->addAddress($_ENV['MAIL_TO_ADDRESS']);

            if ($replyTo) {
                $mail->addReplyTo($replyTo);
            }

            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->isHTML(false);

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log('Mailer error: ' . $mail->ErrorInfo);
            return false;
        }
    }
}
