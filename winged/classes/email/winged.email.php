<?php

/**
 * This class aims to send e-mail.
 * @version 1.0.0.0
 * @access public new object
 * @author Matheus Prado Rodrigues
 * @copyright (c) 2015, Winged Framework
 */
class CoreEmail
{
    private $paths = array(), $fail = false, $html = "text/html", $input, $inputn;

    /**
     * @access public
     * @param String $files_name this variable can be the name of an input file type that exists in multiple html and it or not, or it could be the name of a single or several files comma in the string. (function's behavior depends on the variable '$ in_input').
     * @param String $path this variable is only necessary if the variable '$ is_input' is false. In this variable you should put the folder path where the file names passed in '$ files_name'.
     * @param Boolean $is_input set whether the files will come from a folder or a file input type.
     * @param Boolean $is_html set up the email body will be a treated as text or html.
     * @return void
     */
    public function setOptions($files_name, $path = "", $is_input = false, $is_html = true)
    {
        if ($is_input == true) {
            $input = $_FILES[$files_name];
            if (count($input["name"]) > 1) {
                for ($x = 0; $x < count($input["name"]); $x++) {
                    $this->paths[] = $input["tmp_name"][$x];
                }
            } else {
                $this->paths[] = $input["tmp_name"];
            }
        } else {
            $exp = explode(",", $files_name);
            for ($x = 0; $x < count($exp); $x++) {
                if ($path[strlen($path) - 1] != "/") {
                    $path .= "/";
                }
                $real = $path . trim($exp[$x]);
                if (file_exists($real)) {
                    $this->paths[] = $real;
                } else {
                    $this->fail = true;
                    break;
                }
            }
        }
        $this->input = $is_input;
        $this->inputn = $files_name;
        if ($is_html == false) {
            $this->html = "text/plain";
        }

    }

    /**
     * @access public
     * @param String $email_server_name a false or real email that has the domain name after the @ sign (prevents boxes and e-mail addresses email as span).
     * @param String $mask_name that name masks the real name of who sent the email.
     * @param String $main_email the person who will be sent an email to.
     * @param String $subject email subject.
     * @param String $replay_to case who received the message wanted to respond to email, the response will be sent to the email placed in this variable.
     * @param String $body email body.
     * @param String $send_to others who receive the message. place several emails within a single string separated by commas.
     * @param String $send_name others who receive the message. place several names in the same order of the previous variable within a single string separated by commas.
     * @return Boolean
     */
    public function sendEmail($email_server_name, $mask_name, $main_email, $subject, $replay_to, $body, $send_to = "", $send_name = "")
    {

        $part = $this->toBccAndCc($send_to, $send_name);

        $boundary = strtotime("NOW");

        $headers = "From: " . $email_server_name . "\n";
        $headers .= "Reply-To: " . $replay_to . "\n";
        $headers .= "Bcc: $part\r\n";
        $headers .= "MIME-Version: 1.0\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"" . $boundary . "\"\n";

        if (empty($this->paths)) {
            $msg = "--" . $boundary . "\n";
            $msg .= "Content-Type: " . $this->html . "; charset=\"utf-8\"\n";
            $msg .= "Content-Transfer-Encoding: quoted-printable\n\n";
            $msg .= "" . $body . "\n";
        } else {
            $msg = "--" . $boundary . "\n";
            $msg .= "Content-Type: " . $this->html . "; charset=\"utf-8\"\n";
            $msg .= "Content-Transfer-Encoding: quoted-printable\n\n";
            $msg .= "" . $body . "\n";

            for ($x = 0; $x < count($this->paths); $x++) {

                if ($this->input == true) {
                    if (gettype($_FILES[$this->inputn]["name"]) == "array") {
                        $exp = array($_FILES[$this->inputn]["name"][$x]);
                    } else {
                        $exp = array($_FILES[$this->inputn]["name"]);
                    }
                } else {
                    $exp = explode("/", $this->paths[$x]);
                }
                $msg .= "--" . $boundary . "\n";
                $msg .= "Content-Transfer-Encoding: base64\n";
                $msg .= "Content-Disposition: attachment; filename=\"" . end($exp) . "\"\n\n";
                ob_start();
                readfile($this->paths[$x]);
                $enc = ob_get_contents();
                ob_end_clean();
                $msg_temp = base64_encode($enc) . "\n";
                $tmp[1] = strlen($msg_temp);
                $tmp[2] = ceil($tmp[1] / 76);
                for ($b = 0; $b <= $tmp[2]; $b++) {
                    $tmp[3] = $b * 76;
                    $msg .= substr($msg_temp, $tmp[3], 76) . "\n";
                }
                unset($msg_temp, $tmp, $enc);
            }
        }
        if ($this->fail == false) {
            /*
            ob_start();
            echo($headers);
            $enc = ob_get_contents();
            ob_end_clean();
            pre(htmlentities($enc));
            exit;
            */
            $f = mail($main_email, $subject, $msg, $headers);
            return $f;
        }
    }

    private function toBccAndCc($send_to, $send_name)
    {
        $expt = explode(",", $send_to);
        //$expn = explode(",", $send_name);
        $part = "";
        if ($send_to != "") {
            for ($x = 0; $x < count($expt); $x++) {
                $email = trim($expt[$x]);
                //$name = trim($expn[$x]);
                //$part .= "Cc: ".$name." <".$email.">" . "\r\n";
                if ($x == 0) {
                    $part .= "" . $email . "";
                } else {
                    $part .= ", " . $email . "";
                }

            }
        }
        return $part . "\r\n";
    }
}
