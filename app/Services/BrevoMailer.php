<?php

namespace App\Services;

use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\SendSmtpEmail;
use GuzzleHttp\Client as HttpClient;

class BrevoMailer
{
    protected TransactionalEmailsApi $apiInstance;

    public function __construct()
    {
        $apiKey = config('services.brevo.key');

        if (empty($apiKey)) {
            \Log::error('Brevo API key mancante. Controlla BREVO_API_KEY nel .env');
            throw new \RuntimeException('Brevo API key mancante');
        }

        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
        $this->apiInstance = new TransactionalEmailsApi(new HttpClient(), $config);
    }

    public function sendEmail(string $toEmail, string $subject, string $view, array $data = [])
    {
        try {
            $htmlContent = view($view, $data)->render();

            $sendSmtpEmail = new SendSmtpEmail([
                'subject' => $subject,
                'sender' => [
                    'name' => config('services.brevo.sender_name'),
                    'email' => config('services.brevo.sender_email'),
                ],
                'to' => [['email' => $toEmail]],
                'htmlContent' => $htmlContent,
            ]);

            return $this->apiInstance->sendTransacEmail($sendSmtpEmail);
        } catch (\Exception $e) {
            \Log::error('Errore invio mail Brevo: ' . $e->getMessage());
            throw $e;
        }
    }
}
