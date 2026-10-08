<?php

namespace App\Services;

use App\Models\Setting;
use PHPMailer\PHPMailer\PHPMailer;

/** Sends email with the SMTP details from Admin → Site Settings → Email / SMTP. */
class SiteMailer
{
    public static function make(?array $override = null): PHPMailer
    {
        $s = $override ?? Setting::allSettings();
        $g = fn ($k, $d = '') => isset($s[$k]) && $s[$k] !== null ? $s[$k] : $d;

        $m = new PHPMailer(true);
        $m->CharSet = 'UTF-8';
        $pass = str_replace(' ', '', (string) $g('smtp_password'));
        $user = trim((string) $g('smtp_username'));
        $useSmtp = filter_var($g('smtp_on', true), FILTER_VALIDATE_BOOLEAN) && $pass !== '' && $g('smtp_host') !== '';

        if ($useSmtp) {
            $m->isSMTP();
            $m->Host = trim((string) $g('smtp_host', 'smtp.hostinger.com'));
            $m->Port = (int) $g('smtp_port', 465);
            $m->SMTPAuth = true;
            $m->Username = $user;
            $m->Password = $pass;
            $secure = strtolower((string) $g('smtp_secure', 'ssl'));
            if ($secure === 'tls') {
                $m->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($secure === 'ssl') {
                $m->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $m->SMTPSecure = '';
                $m->SMTPAutoTLS = false;
            }
            $m->Timeout = 20;
            $from = filter_var($g('from_email'), FILTER_VALIDATE_EMAIL) ? $g('from_email') : $user;
        } else {
            $m->isMail();
            $host = preg_replace('/^www\./', '', request()->getHost() ?: 'localhost');
            $from = filter_var($g('from_email'), FILTER_VALIDATE_EMAIL) ? $g('from_email') : 'no-reply@'.$host;
            if (! filter_var($from, FILTER_VALIDATE_EMAIL)) {
                $from = 'no-reply@example.com';
            }
        }
        $m->setFrom($from, (string) $g('from_name', $g('business_name', 'Website')));

        return $m;
    }

    /** Adds comma / semicolon separated addresses. */
    public static function addList(PHPMailer $m, string $list, bool $cc = false): int
    {
        $n = 0;
        foreach (preg_split('/[,;]+/', $list) as $addr) {
            $addr = trim($addr);
            if (filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                $cc ? $m->addCC($addr) : $m->addAddress($addr);
                $n++;
            }
        }

        return $n;
    }
}
