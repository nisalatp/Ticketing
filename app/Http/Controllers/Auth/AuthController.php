<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\TokenStore\TokenCache;
use Illuminate\Http\Request;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Client\Provider\GenericProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function signin()
    {
        if (!config('app.azure_auth_enabled')) {
            abort(404);
        }
        $oauthClient = new GenericProvider([
            'clientId'                => config('azure.appId'),
            'clientSecret'            => config('azure.appSecret'),
            'redirectUri'             => config('azure.redirectUri'),
            'urlAuthorize'            => config('azure.authority') . config('azure.authorizeEndpoint'),
            'urlAccessToken'          => config('azure.authority') . config('azure.tokenEndpoint'),
            'urlResourceOwnerDetails' => '',
            'scopes'                  => config('azure.scopes')
        ]);

        $authUrl = $oauthClient->getAuthorizationUrl();
        $state = $oauthClient->getState();
        session(['oauthState' => $state]);
        session()->save();

        Log::info('Auth redirecting to Microsoft', ['state' => $state, 'session_id' => session()->getId()]);

        return redirect()->away($authUrl);
    }

    public function callback(Request $request)
    {
        if (!config('app.azure_auth_enabled')) {
            abort(404);
        }
        $expectedState = session('oauthState');
        $providedState = $request->query('state');

        Log::info('Auth callback received', [
            'expected_state' => $expectedState,
            'provided_state' => $providedState,
            'session_id' => session()->getId()
        ]);

        if (!isset($expectedState) || !isset($providedState) || $expectedState != $providedState) {
            Log::error('Invalid auth state', [
                'expected' => $expectedState,
                'provided' => $providedState
            ]);
            return redirect('/')->with('error', 'Invalid auth state');
        }

        $authCode = $request->query('code');
        if (isset($authCode)) {
            $oauthClient = new GenericProvider([
                'clientId'                => config('azure.appId'),
                'clientSecret'            => config('azure.appSecret'),
                'redirectUri'             => config('azure.redirectUri'),
                'urlAuthorize'            => config('azure.authority') . config('azure.authorizeEndpoint'),
                'urlAccessToken'          => config('azure.authority') . config('azure.tokenEndpoint'),
                'urlResourceOwnerDetails' => '',
                'scopes'                  => config('azure.scopes')
            ]);

            try {
                $accessToken = $oauthClient->getAccessToken('authorization_code', [
                    'code' => $authCode
                ]);

                $graph = new Graph();
                $graph->setAccessToken($accessToken->getToken());

                // Fetch User Details
                $graphUser = $graph->createRequest('GET', '/me?$select=displayName,mail,UserPrincipalName,givenName,surname,jobTitle')
                    ->setReturnType(Model\User::class)
                    ->execute();

                // Fetch Profile Photo
                $photoPath = null;
                try {
                    $photoMeta = $graph->createRequest('GET', '/me/photo')
                        ->execute();
                    
                    if ($photoMeta) {
                        $photoStream = $graph->createRequest('GET', '/me/photo/$value')
                            ->setReturnType(\GuzzleHttp\Psr7\Stream::class)
                            ->execute();
                        
                        if ($photoStream) {
                            $photoContents = $photoStream->getContents();
                            if (!empty($photoContents)) {
                                $photoName = 'photo_' . $graphUser->getUserPrincipalName() . '.jpg';
                                $photoPath = 'profile-photos/' . $photoName;
                                Storage::disk('public')->put($photoPath, $photoContents);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not fetch user photo from MS Graph: ' . $e->getMessage());
                }

                $this->verifyUser(
                    strtolower($graphUser->getUserPrincipalName()), 
                    ucwords($graphUser->getDisplayName()), 
                    $graphUser->getJobTitle(),
                    $photoPath
                );

                $tokenCache = new TokenCache();
                $tokenCache->storeTokens($accessToken, $graphUser);

                return redirect()->intended('/dashboard');
            } catch (\Exception $e) {
                return redirect('/')->with('error', 'Error requesting access token: ' . $e->getMessage());
            }
        }

        return redirect('/')->with('error', $request->query('error'));
    }

    public function signout()
    {
        $tokenCache = new TokenCache();
        $tokenCache->clearTokens();
        Auth::logout();
        session()->flush();
        return redirect('/');
    }

    private function verifyUser($email, $name, $jobTitle, $photoPath = null)
    {
        $adminEmail = config('app.super_admin_email');

        // We no longer need to bypass global scopes as we are using a local scope
        $user = User::where('email', $email)->first();

        if (!$user) {
            $isAdmin = (strtolower($email) === strtolower($adminEmail));
            
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'job_title' => $jobTitle,
                'profile_photo_path' => $photoPath,
                'password' => bcrypt(Str::random(16)),
                'is_admin' => $isAdmin ? 1 : 0,
                'email_verified_at' => now(),
                'auth_type' => 'microsoft',
            ]);
        } else {
            // Update details from O365 strictly
            $user->update([
                'name' => $name,
                'job_title' => $jobTitle,
                'profile_photo_path' => $photoPath ?? $user->profile_photo_path,
                'auth_type' => 'microsoft', // Ensure it's marked correctly
            ]);
        }

        Auth::login($user);
    }
}
