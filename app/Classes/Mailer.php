<?php

use PHPMailer\PHPMailer\PHPMailer;

// use PHPMailer\PHPMailer\PHPMailer;

/**
 * 
 */
class Mailer extends PHPMailer
{

    function __construct()
    {
        //PHPMailer Object
        // $mail = new PHPMailer;
        // //itsnewsalesmailbox

        $this->IsSMTP();
        $this->Host = $_ENV['MAIL_HOST'];  // specify main and backup server
        $this->SMTPAuth = false;     // turn on SMTP authentication
        $this->Username = $_ENV['MAIL_USERNAME'];  // SMTP username
        $this->Password = $_ENV['MAIL_PASSWORD']; // SMTP password
        /* 
 */


        if ($_ENV['APP_ENV'] == 'local') {
            $this->Port = $_ENV['MAIL_PORT'];
            $this->SMTPAuth = true;
        }



        $this->SMTPKeepAlive = true; // SMTP connection will not close after each email sent, reduces SMTP overhead

        //From email address and name
        $this->From = $_ENV['MAIL_FROM_ADDRESS'];

        $from_name = $_ENV['MAIL_FROM_NAME'];

        if (isset($from_name) && !empty($from_name)) {
            $this->FromName = $from_name;

            //Address to which recipient will reply
            $this->addReplyTo($_ENV['MAIL_DEFAULT_REPLY_TO'], $from_name);
        } else {
            $this->FromName = $from_name;

            //Address to which recipient will reply
            $this->addReplyTo($_ENV['MAIL_DEFAULT_REPLY_TO'], "$from_name");
        }

        //Send HTML or Plain Text email
        $this->isHTML(true);

        //To address and name
        $this->clearAddresses();
        // $this->addAddress("$sendto_email", "$sendto_name");

        //Set BCC address

    }


    /*For live production
public  function sendMail($to, $subject, $body){

     
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

    if (
        this($to, $subject, $body, $headers)
) {

        return true;
    }else{

        return false;
    }

}


*/

    public function include_attachments(array $attachments)
    {
        foreach ($attachments as $key => $value) {

            $this->addAttachment($value['attachment']);
        }

        return $this;
    }

    public function include_message($subject, $body)
    {
        $this->Subject = $subject;
        $this->Body    = $body;
        return $this;
    }

    public function sendMail($to, $subject, $body, $recipient_name = '')
    {
        $this->clearAddresses();



        if (is_array($to)) {

            foreach ($to as $email => $name) {
                if (array_key_first($to) == $email) {
                    $this->addAddress($email, $name);
                } else {
                    $this->AddCC($email, $name);
                }
            }
        } else {
            $this->addAddress("$to", $recipient_name);
        }


        $this->Subject = $subject;
        $this->Body    = $body;
        if ($this->send()) {
            return true;
        } else {

            return false;
        }
    }
}
