<?php

namespace App\Services\Contracts;

use App\Models\OauthAccount;

interface TokenServiceContract
{
    public function refreshToken(OauthAccount $oauthAccount);
}


