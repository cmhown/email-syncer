<?php

namespace App\Services;

use App\Services\Contracts\TokenServiceContract;
use App\Models\OauthAccount;
use Carbon\Carbon;
use GuzzleHttp\Client;

class MicrosoftTokenService implements TokenServiceContract
{

    public function refreshToken(OauthAccount $oauthAccount)
    {
        $client = new Client();

        // Set up the token refresh request
        $response = $client->post('https://login.microsoftonline.com/common/oauth2/v2.0/token', [
            'form_params' => [
                'client_id' => config('services.microsoft.client_id'),
                'client_secret' => config('services.microsoft.client_secret'),
                'refresh_token' => $oauthAccount->refresh_token,
                'grant_type' => 'refresh_token',
                'redirect_uri' => config('services.microsoft.redirect'),
            ],
        ]);

        $responseBody = json_decode((string) $response->getBody(), true);

        if (isset($responseBody['access_token'])) {
            $oauthAccount->access_token = $responseBody['access_token'];
            $oauthAccount->refresh_token = $responseBody['refresh_token'] ?? $oauthAccount->refresh_token;
            $oauthAccount->token_expires_at = Carbon::now()->addSeconds($responseBody['expires_in']);
            $oauthAccount->save();
        } else {
            throw new \Exception('Failed to refresh Microsoft token.');
        }
        return $oauthAccount->access_token;
    }
}


