<?php declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

class Mailer
{
    private static function build(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host    = $_ENV['MAIL_HOST'] ?? 'localhost';
        $mail->Port    = (int) ($_ENV['MAIL_PORT'] ?? 1025);
        $mail->CharSet = 'UTF-8';

        $user = $_ENV['MAIL_USER'] ?? '';
        $pass = $_ENV['MAIL_PASS'] ?? '';

        // Mailpit / desarrollo: sin autenticación ni cifrado
        if ($pass !== '' && $user !== '') {
            $mail->SMTPAuth   = true;
            $mail->Username   = $user;
            $mail->Password   = $pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPAuth   = false;
            $mail->SMTPSecure = false;
        }

        $from = $user !== '' ? $user : 'noreply@movix.test';
        $mail->setFrom($from, APP_NAME);
        return $mail;
    }

    public static function send(string $toEmail, string $toName, string $subject, string $body): bool
    {
        try {
            $mail = self::build();
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->send();
            return true;
        } catch (MailException $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        }
    }

    public static function verificacion(string $email, string $nombre, string $token): bool
    {
        $url = BASE_URL . '/verificar/' . $token;
        ob_start();
        require APP_PATH . 'views/emails/verificacion.php';
        $body = ob_get_clean();
        return self::send($email, $nombre, 'Verifica tu cuenta en ' . APP_NAME, (string) $body);
    }

    public static function recuperacion(string $email, string $nombre, string $token): bool
    {
        $url = BASE_URL . '/recuperar/' . $token;
        ob_start();
        require APP_PATH . 'views/emails/recuperacion.php';
        $body = ob_get_clean();
        return self::send($email, $nombre, 'Recupera tu contraseña en ' . APP_NAME, (string) $body);
    }
}
