<?php

namespace Chestnut\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoginController extends Controller
{

    public function apiLogin(Request $request)
    {
        $credentials = $request->only('phone', 'password');

        if (Auth::attempt($credentials)) {
            $user = auth()->user();
            $api_token = Str::random(64);

            $user->api_token = $api_token;
            $user->save();

            $time = time();

            $token_body = [
                'sub' => $api_token,
                'nbf' => $time,
                'exp' => $time + config('chestnut.auth.expire'),
            ];

            $token = json_encode($token_body);

            return [
                "code" => 200,
                "message" => "login success",
                "token" => base64_encode($token . '.' . app('hash')->make($token)),
            ];

        }

        return [
            'code' => 400,
            'message' => "Login Failed.",
        ];
    }

    public function microappLogin(Request $request)
    {
        if ($request->filled("code")) {
            $code = $request->code;

            $client = new \GuzzleHttp\Client(['base_uri' => 'https://api.weixin.qq.com/']);

            $response = $client->get(
                'sns/jscode2session', [
                    'query' => [
                        'appid' => env("MICROAPP_ID"),
                        'secret' => env("MICROAPP_SECRET"),
                        'js_code' => $code,
                        'grant_type' => 'authorization_code',
                    ],
                ]
            );

            $data = json_decode($response->getBody()->getContents());

            if ($user = User::where('openid', $data->openid)->orWhere('phone', $request->phone)->first()) {
                $user->session_key = $data->session_key;
                $token = $this->generateToken($user);

                if ($request->phone !== null) {
                    $user->openid = $data->openid;
                }

                $user->save();

                return [
                    "code" => 200,
                    "message" => "login success",
                    "token" => $this->encryptToken($token),
                ];
            } else {
                if ($request->phone !== null) {
                    return [
                        "code" => -21,
                        "message" => "手机号码错误，找不到账号。",
                    ];
                }

                $user = User::create(
                    [
                        'openid' => $data->openid,
                        'session_key' => $data->session_key,
                        'role_id' => 1,
                    ]
                );
                $token = $this->generateToken($user);

                return [
                    "code" => 200,
                    "message" => "login success",
                    "token" => $this->encryptToken($token),
                ];
            }

            return ["code" => $data->errcode, "errmsg" => $data->errmsg];
        }

        return [
            "code" => -20,
            "message" => "login failed, code not found.",
        ];

    }

    private function generateToken(User $user)
    {
        $api_token = str_random(64);

        $user->api_token = $api_token;
        $user->save();

        $time = time();

        $token_body = [
            'sub' => $api_token,
            'nbf' => $time,
            'exp' => $time + config('chestnut.auth.expire'),
        ];

        $token = json_encode($token_body);

        return $token;
    }

    private function encryptToken($token)
    {
        return base64_encode($token . '.' . app('hash')->make($token));
    }
}
