<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Mail\EmailMailable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct() {
        $this->middleware('jwt.verify', ['except' => ['login', 'register', 'enterVerifyCode', 'sendResetCodeEmail', 'resetPassword']]);
    }

    public function login(Request $request){
    	$validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return $this->apiResponse((object)[], $validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();
        if(!$user){                                                         // check email exist
            return $this->apiResponse((object)[], 'The email or password is incorrect.', 401);
        }
        if(!$user->is_verified){
            return $this->apiResponse((object)[], 'Your email has not been verified.', 401);
        }
        if (! $token = auth()->attempt($validator->validated())) {          // check pass correct
            return $this->apiResponse((object)[], 'The email or password is incorrect.', 401);
        }
        return $this->createNewToken($token);
    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return $this->apiResponse((object)[], $validator->errors(), 400);
        }

        $verifyCode = rand(10000, 99999);
        $user = User::create(array_merge(
            $validator->validated(),
            [
                'password' => bcrypt($request->password),
                'verifyCode' => $verifyCode,
            ]
        ));

        Mail::to($request->email)->send(new EmailMailable($request->name, $verifyCode, true));  // send mail
        return $this->apiResponse(new UserResource($user), 'Verification code sent to your email. Please enter it to verify your account.', 201);
    }

    public function enterVerifyCode(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'verifyCode' => 'required|integer|digits:5',
        ]);
        if ($validator->fails()) {
            return $this->apiResponse((object)[], $validator->errors(), 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return $this->apiResponse((object)[], 'Email not found', 404);
        }

        if(!$user->verifyCode && $user->is_verified){
            return $this->apiResponse((object)[], 'Your email has been verified.', 404);
        }

        if($request->verifyCode != $user->verifyCode){
            return $this->apiResponse((object)[], 'This code is incorrect.', 404);
        }

        $user->update(['is_verified' => true, 'verifyCode' => null]);
        return $this->apiResponse((object)[], 'User successfully registered', 200);
    }

    public function changePassword(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return $this->apiResponse((object)[], $validator->errors(), 400);
        }


        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return $this->apiResponse((object)[], 'Email not found', 404);
        }
        if(!$user->is_verified){
            return $this->apiResponse((object)[], 'Your email has not been verified.', 401);
        }

        if (!Hash::check($request->current_password, $user->password)) { // check current password is correct
            return $this->apiResponse((object)[], 'Current password is incorrect', 400);
        }

        $user->password = bcrypt($request->new_password);
        $user->save();
        return $this->apiResponse((object)[], 'Password successfully updated', 200);
    }

    public function sendResetCodeEmail(Request $request) {                   // fn 1 for forgetPassword
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return $this->apiResponse((object)[], $validator->errors(), 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return $this->apiResponse((object)[], 'The email is incorrect.', 404);
        }

        if(!$user->is_verified){
            return $this->apiResponse((object)[], 'Your email has not been verified.', 401);
        }

        $resetCode = rand(10000, 99999);
        $user->resetCode = $resetCode;
        $user->save();

        Mail::to($request->email)->send(new EmailMailable($user->name, $resetCode, false));  // send mail
        return $this->apiResponse((object)[], 'Password reset link sent to your email', 200);
    }

    public function resetPassword(Request $request) {                // fn 2 for forgetPassword
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'resetCode' => 'required|integer|digits:5',
        ]);
        if ($validator->fails()) {
            return $this->apiResponse((object)[], $validator->errors(), 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return $this->apiResponse((object)[], 'Email not found', 404);
        }

        if(!$user->is_verified){
            return $this->apiResponse((object)[], 'Your email has not been verified.', 401);
        }

        if($request->resetCode != $user->resetCode){
            return $this->apiResponse((object)[], 'This code is incorrect.', 404);
        }

        $user->password = bcrypt($request->password);
        $user->is_reset = true;
        $user->resetCode = null;
        $user->save();
        return $this->apiResponse((object)[], 'Password reset successfully', 200);
    }

    public function logout() {
        auth()->logout();
        return $this->apiResponse((object)[], 'User successfully signed out', 200);
    }

    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

    public function userProfile() {
        $user = auth()->user();
        return $this->apiResponse(new UserResource($user), 'User profile retrieved successfully', 200);
    }

    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => new UserResource(auth()->user())
        ]);
    }
}
