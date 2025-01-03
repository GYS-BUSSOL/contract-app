<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Handle an authentication attempt.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        try {
            $data = [
                'username' => ['required', 'string'],
                'password' => ['required', 'string'],
                'captcha' => ['required', 'string']
            ];

            $validated = $this->handleValidationException($request, $data);
            if ($validated instanceof JsonResponse) {
                return $validated;
            }

            $key = Cache::get('captcha_key');
            if (!captcha_api_check($validated['captcha'], $key)) {
                return response()->json([
                    'status' => 400,
                    'errors' => 'Failed captcha',
                    'message' => 'Captcha is incorrect'
                ], 400);
            }

            $username = $validated['username'];
            $password = $validated['password'];
            $adServers = ["ldap://gysdc01.gyssteel.com", "ldap://gysdc02.gyssteel.com"];

            $ldapConnected = false;
            foreach ($adServers as $server) {
                $ldap = @ldap_connect($server);
                if ($ldap) {
                    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
                    ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

                    $ldaprdn = "gys\\" . $username;
                    $bind = @ldap_bind($ldap, $ldaprdn, $password);

                    if ($bind) {
                        $ldapConnected = true;
                        break;
                    }
                }
            }

            if ($ldapConnected) {
                $filter = "(sAMAccountName=$username)";
                $result = @ldap_search($ldap, "dc=gyssteel,dc=com", $filter);

                if ($result) {
                    $entries = ldap_get_entries($ldap, $result);
                    if ($entries['count'] > 0) {
                        $user = $this->user->firstWhere('usr_name', $validated['username']);
                        $token = $user->createToken('access_token')->plainTextToken;

                        return response()->json([
                            'status' => 201,
                            'message' => 'Login successfully',
                            'userAbilityRules' => [
                                [
                                    'action' => 'manage',
                                    'subject' => 'all',
                                ],
                            ],
                            'userData' => $user,
                            'accessToken' => $token
                        ], 201);
                    }
                }
            } else {
                return response()->json([
                    'status' => 400,
                    'errors' => 'Unauthorized',
                    'message' => 'Login failed, username or password is incorrect'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'errors' => 'Server Error',
                'message' => 'Failed to login',
            ], 500);
        }
    }

    /**
     * Logout user (Revoke the token)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Logout successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Server Error',
                'message' => 'An error occurred while logging out. Please try again later',
            ], 500);
        }
    }
}
