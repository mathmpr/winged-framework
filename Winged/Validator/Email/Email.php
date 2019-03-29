<?php

namespace Winged\Validator\Email;

use Dompdf\Exception;

class Email
{
    public static function validate($email)
    {
        return (boolean)filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function check($toemail, $fromemail, $getdetails = false)
    {
        set_time_limit(0);
        if (!self::validate($toemail) || !self::validate($fromemail)) {
            return false;
        }
        $details = '';
        $result = false;
        $mxweight = null;
        $email_arr = explode('@', $toemail);
        $domain = array_slice($email_arr, -1);
        $domain = $domain[0];
        $domain = ltrim($domain, '[');
        $domain = rtrim($domain, ']');
        if ('IPv6:' == substr($domain, 0, strlen('IPv6:'))) {
            $domain = substr($domain, strlen('IPv6') + 1);
        }
        $mxhosts = [];
        if (filter_var($domain, FILTER_VALIDATE_IP)) {
            $mxhosts[] = $domain;
        } else {
            getmxrr($domain, $mxhosts);
        }
        if (empty($mxhosts)) {
            if (filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $record_a = dns_get_record($domain, DNS_A);
            } elseif (filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $record_a = dns_get_record($domain, DNS_AAAA);
            }
            if (!empty($record_a)) {
                $mxhosts[] = $record_a[0]['ip'];
            } else {
                $result = false;
                $details = 'No suitable MX records found.';
                return ((true == $getdetails) ? ['status' => $result, 'details' => $details] : $result);
            }
        }
        if (!empty($mxhosts)) {
            foreach ($mxhosts as $key => $host) {
                if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) &&
                    !filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
                ) {
                    $mxhosts[$key] = gethostbynamel($host)[0];
                }
            }
            foreach ($mxhosts as $key => $host) {
                $connect = false;
                try {
                    $connect = @fsockopen($host, 25, $errno, $errstr, 5);
                } catch (\Exception $e) {
                }
                if ($connect) {
                    if (preg_match('/^220/i', $out = fgets($connect, 1024))) {
                        fputs($connect, "HELO $host\r\n");
                        $out = fgets($connect, 1024);
                        $details .= $out . "\n";

                        fputs($connect, "MAIL FROM: <$fromemail>\r\n");
                        $from = fgets($connect, 1024);
                        $details .= $from . "\n";

                        fputs($connect, "RCPT TO: <$toemail>\r\n");
                        $to = fgets($connect, 1024);
                        $details .= $to . "\n";

                        fputs($connect, 'QUIT');
                        fclose($connect);

                        if (preg_match('/^250/i', $from) || preg_match('/^250/i', $to)) {
                            return true;
                        }
                    }
                }
            }
        }
        if ($getdetails) {
            $result = false;
            $details = 'Can\'t connect in MX servers.';
            return ['status' => $result, 'details' => $details];
        } else {
            return $result;
        }
    }
}