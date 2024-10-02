<?php

namespace App\Services;

use App\Services\Contracts\TokenServiceContract;
use App\Models\OauthAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class GoogleTokenService implements TokenServiceContract
{

    public function refreshToken(OauthAccount $oauthAccount)
    {
        // Make the request to Google's OAuth 2.0 server
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $oauthAccount->refresh_token,
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
        ]);

        if ($response->successful()) {
            $data = $response->json();

            $oauthAccount->access_token = $data['access_token'];
            $oauthAccount->refresh_token = $data['refresh_token'] ?? $oauthAccount->refresh_token;
            $oauthAccount->token_expires_at = Carbon::now()->addSeconds($data['expires_in']);
            $oauthAccount->save();
        } else {
            throw new \Exception('Failed to refresh Google token.');
        }

        return $oauthAccount->access_token;
    }
}
