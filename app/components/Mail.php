<?php

namespace Testify\Component;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail {

    private $recipient;
    private $title;
    private $content;
    private $sender;

    public function __construct($sender, $recipient, $title, $content) {
        $this->sender = $sender;
        $this->recipient = $recipient;
        $this->title = $title;
        $this->content = $content;
    }

    public function sendMail(): bool {
        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = 0;
            $mail->CharSet = 'utf-8';
            $mail->IsSMTP();
            $mail->Host       = $this->sender['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->SMTPSecure = "tls";
            $mail->Port       = 587;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $mail->Username   = $this->sender['username'];
            $mail->Password   = $this->sender['password'];
            $mail->SetFrom($this->sender['username'], $this->sender['name']);
            $mail->addAddress($this->recipient);
            $mail->isHTML(true);
            $mail->Subject = $this->title;
            $mail->Body    = $this->content;
            $mail->AltBody = $this->content;
            return $mail->send();
        } catch (Exception $e) {
            return false;
        }
    }
}
