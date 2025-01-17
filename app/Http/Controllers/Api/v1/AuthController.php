<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except('authenticate'); 
       /*  $this->middleware('auth:api', ['except' => ['authenticade']]); */
    }

    public function authenticate(Request $request)
    {
        // grab credentials from the request
        $credentials = $request->only('email', 'password');

        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {

            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token', $e->getStatusCode()], 500);
        }

        $user = auth()->user();

        // all good so return the token
        return response()->json(compact('token', 'user'));
    }

    public function getAuthenticatedUser()
    {
        try {

            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {

            return response()->json(['token_expired',$e->getStatusCode()]);
        } catch (TokenInvalidException $e) {

            return response()->json(['token_invalid',$e->getStatusCode()]);
        } catch (JWTException $e) {

            return response()->json(['token_absent',$e->getStatusCode()]);
        }

        // the token is valid and we have found the user via the sub claim
        return response()->json(compact('user'));
    }


    public function refreshToken()
    {


        if (!$token = JWTAuth::getToken()) {
            return response()->json(['error' => 'token não enviado!', 404]);

            try {
                $token = JWTAuth::resfresh();
            } catch (TokenInvalidException $e) {
                return response()->json(['token_invalid',$e->getStatusCode()]);
            }
            return response()->json(compact('token'));
        }
    }
}
