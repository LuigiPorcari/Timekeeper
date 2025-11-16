<?php

namespace App\Services;

use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\SendSmtpEmail;
use GuzzleHttp\Client as HttpClient;

class BrevoMailer
{
    public function sendEmail($toEmail, $subject, $view, $data = [])
    {
        try {
            \Mail::send($view, $data, function ($message) use ($toEmail, $subject) {
                $message->to($toEmail)
                        ->subject($subject);
            });

        } catch (\Exception $e) {
            \Log::error('Errore invio SMTP: ' . $e->getMessage());
            throw $e;
        }
    }
}
