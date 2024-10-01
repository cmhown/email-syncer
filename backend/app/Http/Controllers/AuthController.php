<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\SyncEmailFolders;
use App\Models\OauthAccount;
use App\Models\User;
use App\Services\Contracts\SyncJobDispatcherInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            // Create a token for the user
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json(['token' => $token], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token,], 201);
    }

    // Handle user logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out'], 200);
    }



    public function providerRedirectURL($provider)
    {
        switch ($provider) {
            case 'microsoft':
                $authURL = Socialite::driver($provider)
                    ->stateless()
                    ->scopes(['https://outlook.office.com/IMAP.AccessAsUser.All', 'offline_access', 'openid', 'email', 'profile'])
                    ->redirect()
                    ->getTargetUrl();
                break;
            case 'google':
                $authURL = Socialite::driver($provider)
                    ->stateless()
                    ->scopes(['https://mail.google.com/', 'email', 'profile', 'https://www.googleapis.com/auth/gmail.readonly'])
                    ->with(['access_type' => 'offline', 'prompt' => 'consent'])
                    ->redirect()
                    ->getTargetUrl();
                break;
            default:
                return response()->json(['message' => 'Email provider not supported'], 404);
                break;
        }
        return response()->json(['authorization_url' => $authURL], 200);
    }

    public function handleProviderCallback($provider, Authenticatable $user)
    {
        $socialUser = Socialite::driver($provider)->stateless()->user();

        // Find or create the linked OAuth account
        $oauthAccount = OauthAccount::updateOrCreate(
            [
                'user_id' => $user->id,
                'provider' => $provider,
            ],
            [
                'provider_user_id' => $socialUser->getId(),
                'provider_email' => $socialUser->getEmail(),
                'access_token' => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken,
                'token_expires_at' => now()->addSeconds($socialUser->expiresIn),
            ]
        );

        SyncEmailFolders::dispatch($oauthAccount);

        return response()->json(['status' => true], 200);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        $user->load('oauthAccounts');
        return $user;
    }
}
