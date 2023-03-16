<?php

namespace App\Service;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Sending mail class is used to send mail messages to the user and gmail
 * SMTP server is used to send the mail and those important information are
 * added in the env file. At the same moment if mail is sent successfully a
 * boolean flag is added. If any error occurs then the errors will be returned.
 * 
 * @package PHPMailer
 * @see https://packagist.org/packages/phpmailer/phpmailer
 *   
 * @author Kumaresh Baksi <kumaresh.baksi@innoraft.com>
 */
class SendEmail
{
  /**
   * Send Email function send a mail according to the parameter provided.
   *
   * @param string $email
   *   This mail id the receiver email id.
   * @param string $link
   *   Link will act as a OTP and password reset link.
   * @param string $msg
   *   Message contains the message which will be sent with the email.
   * 
   * @return mixed
   *   If mail is send successfully returns TRUE instead it returns the
   *   exception message.
   */
  public function sendEmail(string $email, string $link, string $msg)
  {
    // Creating the object of the PHPMailer.
    $mail = new PHPMailer(TRUE);

    try {
      $mail->isSMTP();

      // Setting host
      $mail->Host     = $_ENV['MAILER_HOST'];
      $mail->SMTPAuth = TRUE;

      // Setting username and password from GMAIL SMTP server
      $mail->Username = $_ENV['MAILER_USERNAME'];
      $mail->Password = $_ENV['MAILER_PASSWORD'];

      // This tls encrypt the whole SMTP process.
      $mail->SMTPSecure = 'tls';
      $mail->Port = 587;

      $mail->setFrom('baksikumaresh123@gmail.com', 'Innoraft');
      $mail->addAddress("$email", '');

      // isHTML function in PHPMailer allows you.
      $mail->isHTML(TRUE);
      $mail->Subject = 'Innoraft';
      $mail->Body = $msg . $link;

      // If send function returns TRUE, showing user a positive
      // response instead show the failed message.
      if ($mail->send()) {
        return TRUE;
      }
    } 
    catch (Exception $e) {
      return $e->getMessage();
    }
  }
}