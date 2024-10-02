<?php

namespace App\Services;

use App\Models\OauthAccount;
use Webklex\IMAP\Facades\Client;

const PROVIDERS_MAPPING = [
    'microsoft' => 'outlook',
    'google' => 'gmail'
];

class MailService
{
    protected $client;
    protected $mailProvider;
    protected $tokenService;
    protected $oauthAccount;

    public function __construct(OauthAccount $oauthAccount)
    {
        $this->oauthAccount = $oauthAccount;
        $this->mailProvider = PROVIDERS_MAPPING[$oauthAccount->provider];

        $this->configureClient();
    }

    public function getClient()
    {
        return $this->client;
    }

    private function configureClient()
    {
        $this->tokenService = new TokenService();

        $accessToken = $this->tokenService->getToken($this->oauthAccount);

        // Dynamically update IMAP configuration for the user
        config([
            "imap.accounts.{$this->mailProvider}.username" => $this->oauthAccount->provider_email,
            "imap.accounts.{$this->mailProvider}.password" => $accessToken,
        ]);

        // Initialize the IMAP client with the updated config
        $this->client = Client::account($this->mailProvider);

        $this->client->connect();
    }

}
