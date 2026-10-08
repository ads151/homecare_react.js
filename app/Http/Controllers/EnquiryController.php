<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Setting;
use App\Services\SiteMailer;
use App\Services\SiteRenderer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/** One handler for every enquiry form on the website. */
class EnquiryController extends Controller
{
    /** true = request came from the Next.js website (already checked with the secret key). */
    protected bool $apiMode = false;

    /** POST /api/enquiry — used by the Next.js website. */
    public function api(Request $request)
    {
        $this->apiMode = true;

        return $this->store($request);
    }

    public function store(Request $request)
    {
        SiteRenderer::boot();
        $GLOBALS['HC'] = ['settings' => Setting::allSettings(), 'content' => [], 'page' => 'send', 'preview' => false];

        $isAjax = $this->apiMode || $request->ajax() || $request->input('ajax') === '1';
        $thankYou = $this->apiMode ? '/thank-you' : hc_url('thank-you');

        $reply = function (bool $ok, string $message, string $redirect = '') use ($isAjax, $request) {
            if ($isAjax) {
                return response()->json(['ok' => $ok, 'message' => $message, 'redirect' => $redirect]);
            }
            if ($ok && $redirect) {
                return redirect()->to($redirect, 303);
            }
            $back = $request->headers->get('referer') ?: hc_url('');

            return response('<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Please check the form</title>'
                .'<style>body{font-family:system-ui,Arial,sans-serif;background:#eef6f4;margin:0;padding:24px;color:#0f2a52}.b{max-width:520px;margin:60px auto;background:#fff;border-radius:16px;padding:28px;box-shadow:0 10px 30px rgba(0,0,0,.08)}a{display:inline-block;margin:8px 8px 0 0;padding:12px 20px;border-radius:30px;background:#2c9a55;color:#fff;text-decoration:none;font-weight:700}a.c{background:#1f5c99}</style></head><body><div class="b">'
                .'<h1 style="font-size:1.3rem">Please check the form</h1><p>'.hc_e($message).'</p>'
                .'<a href="'.hc_e($back).'">&larr; Go back</a><a class="c" href="'.hc_e(hc_tel()).'">Call '.hc_e(s('mobile')).'</a></div></body></html>', 400);
        };

        $val = function ($k, $max = 1000) use ($request) {
            $v = $request->input($k, '');
            if (is_array($v)) {
                $v = implode(', ', $v);
            }
            $v = trim(strip_tags((string) $v));
            $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $v) ?? $v;

            return mb_substr($v, 0, $max, 'UTF-8');
        };

        /* ---------------- SPAM PROTECTION ---------------- */
        if ($val('website') !== '') {
            return $reply(true, 'OK', $thankYou);
        }
        if (! $this->apiMode) {
        $parts = explode('.', $val('hc_ts', 100));
        $validToken = count($parts) === 2 && ctype_digit($parts[0])
            && hash_equals(substr(hash_hmac('sha256', $parts[0], hc_secret()), 0, 24), $parts[1]);
        $age = $validToken ? time() - (int) $parts[0] : -1;
        if (! $validToken || $age > 172800) {
            return $reply(false, 'This form has expired. Please refresh the page and try again, or call us on '.s('mobile').'.');
        }
        if ($age < 3) {
            return $reply(false, 'That was very fast! Please wait a moment and submit again.');
        }
        }

        /* ---------------- VALIDATION ---------------- */
        $data = [];
        $errors = [];
        foreach (hc_form_fields() as $f) {
            $v = $val($f['name'], $f['type'] === 'textarea' ? 2000 : 200);
            if ($f['type'] === 'tel' && $v !== '') {
                $d = hc_digits($v);
                if (strlen($d) === 12 && str_starts_with($d, '91')) {
                    $d = substr($d, 2);
                }
                if (strlen($d) === 11 && $d[0] === '0') {
                    $d = substr($d, 1);
                }
                if (! preg_match('/^[6-9][0-9]{9}$/', $d)) {
                    $errors[] = 'Please enter a valid 10-digit mobile number (e.g. 9876543210).';
                }
                $v = $d;
            }
            if ($f['type'] === 'email' && $v !== '' && ! filter_var($v, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Please enter a valid email address.';
            }
            if ($f['type'] === 'date' && $v !== '' && (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) || $v < date('Y-m-d'))) {
                $errors[] = 'Please choose today or a future date.';
            }
            if ($f['required'] && $v === '') {
                $errors[] = 'Please fill: '.$f['label'].'.';
            }
            $data[$f['name']] = ['label' => $f['label'], 'value' => $v, 'type' => $f['type']];
        }
        if ($errors) {
            return $reply(false, implode(' ', array_unique($errors)));
        }

        /* ---------------- DETAILS ---------------- */
        $item = $val('enquiry_item', 150);
        $formName = $val('form_name', 150);
        $pageUrl = $val('page_url', 500) ?: mb_substr((string) $request->headers->get('referer'), 0, 500);
        if ($item === '' && isset($data['service']) && $data['service']['value'] !== '' && $data['service']['value'] !== 'Other / Not Decided') {
            $item = $data['service']['value'];
        }
        $card = hc_find_card($item);
        $details = $card ? hc_card_details($card) : '';

        $name = $data['name']['value'] ?? '';
        $mobile = '';
        $email = '';
        foreach ($data as $d) {
            if ($d['type'] === 'tel' && $mobile === '') {
                $mobile = $d['value'];
            }
            if ($d['type'] === 'email' && $email === '') {
                $email = $d['value'];
            }
        }
        $when = now()->format('d M Y, h:i A');
        $ip = $this->apiMode ? (string) ($request->header('X-Client-Ip') ?: $request->ip()) : (string) $request->ip();
        $biz = s('business_name', 'Website');

        $subject = $item !== ''
            ? 'New '.s('buttons.email_action_word', 'Enquiry').' – '.$item.' – '.$name
            : 'New Enquiry – '.($formName !== '' ? $formName : 'Website').' – '.$name;

        /* ---------------- SAVE LEAD ---------------- */
        $lead = Lead::create([
            'name' => $name,
            'mobile' => $mobile,
            'email' => $email,
            'item' => $item,
            'form_name' => $formName,
            'page_url' => $pageUrl,
            'data' => collect($data)->map(fn ($d) => ['label' => $d['label'], 'value' => $d['value']])->all(),
            'ip' => $ip,
            'status' => 'new',
        ]);

        /* ---------------- EMAIL ---------------- */
        $rows = [];
        foreach ($data as $d) {
            $rows[] = [$d['label'], $d['value']];
        }
        if ($item !== '') {
            $rows[] = ['Enquiry For', $item];
        }
        if ($details !== '') {
            $rows[] = ['Service Details', $details];
        }
        $rows[] = ['Form', $formName];
        $rows[] = ['Page', $pageUrl];
        $rows[] = ['Date & Time', $when.' (IST)'];
        $rows[] = ['IP Address', $ip];

        $waNum = '91'.$mobile;
        $waText = rawurlencode('Hi '.$name.', this is '.$biz.'. Thank you for your enquiry'.($item !== '' ? ' for '.$item : '').'.');
        $html = '<div style="font-family:Arial,sans-serif;max-width:620px;margin:auto;border:1px solid #dbe7ea;border-radius:12px;overflow:hidden">'
            .'<div style="background:#0f2a52;color:#fff;padding:18px 22px;font-size:18px;font-weight:bold">'.hc_e($subject).'</div>'
            .'<table cellpadding="10" cellspacing="0" style="width:100%;border-collapse:collapse;font-size:14px">';
        foreach ($rows as $i => $r) {
            $html .= '<tr style="background:'.($i % 2 ? '#ffffff' : '#f4f8fa').'"><td style="width:150px;font-weight:bold;color:#0f2a52;border-bottom:1px solid #e6eef1">'.hc_e($r[0]).'</td><td style="border-bottom:1px solid #e6eef1">'.nl2br(hc_e($r[1])).'</td></tr>';
        }
        $html .= '</table><div style="padding:18px 22px">';
        if ($mobile !== '') {
            $html .= '<a href="tel:+91'.hc_e($mobile).'" style="display:inline-block;background:#1f5c99;color:#fff;padding:12px 20px;border-radius:30px;text-decoration:none;font-weight:bold;margin:0 8px 8px 0">&#128222; Call Customer</a>'
                .'<a href="https://wa.me/'.hc_e($waNum).'?text='.$waText.'" style="display:inline-block;background:#25D366;color:#fff;padding:12px 20px;border-radius:30px;text-decoration:none;font-weight:bold;margin:0 8px 8px 0">WhatsApp Customer</a>';
        }
        $html .= '</div></div>';

        $text = $subject."\n\n";
        foreach ($rows as $r) {
            $text .= $r[0].': '.$r[1]."\n";
        }

        $mailOk = false;
        $mailError = '';
        try {
            $m = SiteMailer::make();
            $count = SiteMailer::addList($m, (string) s('mail_to', ''));
            SiteMailer::addList($m, (string) s('mail_cc', ''), true);
            if ($count === 0) {
                throw new \RuntimeException('No "Send enquiries to" email set in Site Settings.');
            }
            if ($email !== '') {
                $m->addReplyTo($email, $name);
            }
            $m->isHTML(true);
            $m->Subject = $subject;
            $m->Body = $html;
            $m->AltBody = $text;
            $mailOk = $m->send();
        } catch (\Throwable $e) {
            $mailError = $e->getMessage();
            Log::warning('[Enquiry] Email failed: '.$mailError);
        }
        $lead->update(['mail_sent' => $mailOk, 'mail_error' => $mailError ?: null]);

        // Auto-reply to the customer
        if ($mailOk && $email !== '' && on(s('autoreply_on', false))) {
            try {
                $r = SiteMailer::make();
                $r->addAddress($email, $name);
                $itemText = $item !== '' ? $item : 'our services';
                $r->Subject = str_replace(['{name}', '{item}'], [$name, $itemText], (string) s('autoreply_subject', 'Thank you'));
                $r->isHTML(false);
                $r->Body = hc_fill(str_replace(['{name}', '{item}'], [$name, $itemText], (string) s('autoreply_message', '')));
                $r->send();
            } catch (\Throwable) {
                // auto-reply failure never blocks the enquiry
            }
        }

        $q = array_filter(['name' => $name, 'item' => $item]);

        return $reply(true, 'OK', $thankYou.($q ? '?'.http_build_query($q) : ''));
    }
}
