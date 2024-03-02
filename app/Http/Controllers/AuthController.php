<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Services\WebServices;
use Spatie\Permission\Models\Role;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\IpUtils;
class AuthController extends Controller
{
    public function __construct(WebServices $exampleService)
    {
        $this->exampleService = $exampleService;
    }

public function register(Request $request)
{
    $data = $request->validate([
        'name' => 'required|string',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6',
    ]);

    $clientIps = $this->getClientIps($request);
    $lastLoginIp = $clientIps ? $clientIps[0] : $request->ip();

    $result = $this->exampleService->validateData($data);

    if (!isset($result['success']) || !$result['success']) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
        ], 400);
    }

    $data['password'] = Hash::make($request->password);
    $user = User::create($data);
    $credentials = $request->only(['email', 'password']);
    $token = auth()->attempt($credentials);
    $user->update(['last_login_ip' => $lastLoginIp]);

    return response()->json([
        'success' => true,
        'message' => 'Successfully registered',
        'id' => $user->id,
        'user' => $user,
        'access_token' => $token,
        'last_login_ip' => $lastLoginIp,
    ], 201);
}


public function login(Request $request)
{
    try {
        $data = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        $result = $this->exampleService->validateDatalog($data);

        if (!isset($result['success']) || !$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $result['errors'] ?? null,
            ], 400);
        }

        $credentials = $request->only(['email', 'password']);
        if ($token = auth()->attempt($credentials)) {
            $user = auth()->user();
            $clientIps = $this->getClientIps($request);
            $lastLoginIp = $clientIps ? $clientIps[0] : $request->ip();
            $user->update(['last_login_ip' => $lastLoginIp]);

            return response()->json([
                'access_token' => $token,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'last_login_ip' => $lastLoginIp,
                    'logLevel' => config('logging.channels.custom.level'),
                ],
            ]);
        } else {
            throw new \Exception('Invalid email or password');
        }
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 400);
    }
}

    public function getClientIps(Request $request)
    {
        $clientIps = array();
        $ip = $request->ip();
        if (!$request->isFromTrustedProxy()) {
            return array($ip);
        }
        if (self::$trustedHeaders[self::HEADER_FORWARDED] && $request->headers->has(self::$trustedHeaders[self::HEADER_FORWARDED])) {
            $forwardedHeader = $request->headers->get(self::$trustedHeaders[self::HEADER_FORWARDED]);
            preg_match_all('{(for)=("?\[?)([a-z0-9\.:_\-/]*)}', $forwardedHeader, $matches);
            $clientIps = $matches[3];
        } elseif (self::$trustedHeaders[self::HEADER_CLIENT_IP] && $request->headers->has(self::$trustedHeaders[self::HEADER_CLIENT_IP])) {
            $clientIps = array_map('trim', explode(',', $request->headers->get(self::$trustedHeaders[self::HEADER_CLIENT_IP])));
        }
        $clientIps[] = $ip;
        $ip = $clientIps[0];
        foreach ($clientIps as $key => $clientIp) {
            if (preg_match('{((?:\d+\.){3}\d+)\:\d+}', $clientIp, $match)) {
                $clientIps[$key] = $clientIp = $match[1];
            }
            if (IpUtils::checkIp($clientIp, self::$trustedProxies)) {
                unset($clientIps[$key]);
            }
        }
        return $clientIps ? array_reverse($clientIps) : array($ip);
    }
   public function logout(Request $request)
    {
        $request->user()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function DeleteMyAccount()
    {
        $user = auth()->user();
            User::where('id' , $user->id)->delete();
            Auth::logout();

        return response()->json([
            'message' => 'Account deleted Successsfuly'
        ]);

    }
}
