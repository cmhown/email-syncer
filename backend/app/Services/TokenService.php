<?php

namespace App\Services;

use App\Models\OauthAccount;
use App\Services\Contracts\TokenServiceContract;
use Carbon\Carbon;

class TokenService
{

    public function getToken(OauthAccount $oauthAccount)
    {
        $accessToken = $oauthAccount->access_token;
        // Refresh token if it is expired
        if ($oauthAccount->token_expires_at < Carbon::now()) {
            $tokenService = app()->make(TokenServiceContract::class, ['provider' => $oauthAccount->provider]);
            $accessToken = $tokenService->refreshToken($oauthAccount);
        }
        return $accessToken;
    }

}


