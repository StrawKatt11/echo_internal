<?php
// ============================================================
//  ECHO RUNNER – Email konfiguráció (Gmail SMTP)
//  Változtasd meg az alábbi adatokat saját fiókodhoz!
// ============================================================

define('MAIL_HOST',       'smtp.gmail.com');
define('MAIL_PORT',       587);
define('MAIL_SECURE',     'tls');          // 'tls' = STARTTLS (587)
define('MAIL_USERNAME',   'echorunner01@gmail.com');
define('MAIL_PASSWORD',   'herb uamw zdux znip');  // Gmail App Password (16 karakter)
define('MAIL_FROM_EMAIL', 'echorunner01@gmail.com');
define('MAIL_FROM_NAME',  'ECHO RUNNER');

// ============================================================
//  FONTOS: Gmail „App Password" létrehozása
//  1. Menj ide: https://myaccount.google.com/security
//  2. Kapcsold be a 2-faktoros hitelesítést
//  3. Keresd meg: „App passwords" → Hozz létre egyet
//  4. A kapott 16 karakteres jelszót illeszd be MAIL_PASSWORD-ba
//  5. A normál Gmail jelszavad NEM fog működni!
// ============================================================

/**
 * Segédfüggvény: PHPMailer példány előkonfigurálva Gmail SMTP-vel.
 * Használat:
 *   $mail = createMailer();
 *   $mail->addAddress($email);
 *   $mail->Subject = '...';
 *   $mail->Body    = '...';
 *   $mail->send();
 */
function createMailer(): \PHPMailer\PHPMailer\PHPMailer {
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->Port       = MAIL_PORT;
    $mail->SMTPSecure = MAIL_SECURE;
    $mail->SMTPAuth   = true;
    $mail->Username   = MAIL_USERNAME;
    $mail->Password   = MAIL_PASSWORD;
    $mail->CharSet    = 'UTF-8';
    $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
    return $mail;
}
