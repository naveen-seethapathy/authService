<?php

namespace App\Http\Controllers;

use App\Http\Services\LoginService;
use App\Http\Services\RedisService;
use App\Http\Traits\TransformsResponses;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use TransformsResponses;

    /**
     * Register endpoint to create users
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $requestData = $request->all();
        $validator = Validator::make(
            $requestData,
            [
                'name' => 'required|string|max:255',
                'email' => 'required|string|max:255|unique:users,email',
                'password' => 'required|min:6|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/'
            ],
            [
                'name.required' => 'Name is required to register',
                'name.string' => 'Name must be a string',
                'name.max' => 'Name should not be more than 255 characters',
                'email.required' => 'Email address is required to register',
                'email.string' => 'Email address provided must be valid',
                'email.max' => 'Email address should not be more than 255 characters',
                'email.unique' => 'Email address is already taken',
                'password.required' => 'Password is required to register',
                'password.min' => 'Password must have minimum 6 characters',
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one special character and one number'
            ]
        );

        if ($validator->passes()) {
            try {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password)
                ]);

                if ($user->id) {
                    $apiKey = LoginService::setAuthToken($user);
                    $data = $user->toArray();
                    $data['apiKey'] = $apiKey;
                    return $this->respond([
                        'status' => true,
                        'message' => 'User Created',
                        'data' => $data,
                        'errors' => []
                    ], [], 200);
                }
                throw new \Exception('Unable to create user.');
            } catch (\Exception $e) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Server Error',
                    'data' => [],
                    'errors' => [$e->getMessage()]
                ], [], 500);
            }
        } else {
            return $this->respond([
                'status' => false,
                'message' => 'validation failed',
                'data' => [],
                'errors' => $validator->errors()
            ], [], 400);
        }
    }

    /**
     * Get user info endpoint after authentication
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser(Request $request)
    {
        return $this->respond([
            'status' => true,
            'message' => 'User Info',
            'data' => $request->user->toArray(),
            'errors' => []
        ], [], 200);
    }

    /**
     * API Login endpoint
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $requestData = $request->all();
        $validator = Validator::make(
            $requestData,
            [
                'email' => 'required',
                'password' => 'required'
            ],
            [
                'email.required' => 'Email address is required to login',
                'password.required' => 'Password is required to login',
            ]
        );

        if ($validator->passes()) {
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return $this->respond([
                    'status' => false,
                    'message' => 'Invalid username or password',
                    'data' => [],
                    'errors' => $validator->errors()
                ], [], 400);
            }

            if (Hash::check($request->password, $user->password)) {
                $apiKey = LoginService::setAuthToken($user);
                $data = $user->toArray();
                $data['apiKey'] = $apiKey;

                return $this->respond([
                    'status' => true,
                    'message' => 'User Info',
                    'data' => $data,
                    'errors' => []
                ], [], 200);
            }

            return $this->respond([
                'status' => false,
                'message' => 'Invalid username or password',
                'data' => [],
                'errors' => $validator->errors()
            ], [], 400);
        } else {
            return $this->respond([
                'status' => false,
                'message' => 'validation failed',
                'data' => [],
                'errors' => $validator->errors()
            ], [], 400);
        }
    }

    /**
     * Logout User
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $bearer = $request->hasHeader('Authorization') ? $request->header('Authorization', '') : $request->header('authorization');
            $bearer = str_replace('bearer ', '', $bearer);
            $bearer = str_replace('Bearer ', '', $bearer);

            (new RedisService)->delData("auth:user:token:{$bearer}");
            return $this->respond([
                'status' => true,
                'message' => 'User Logged out',
                'data' => [],
                'errors' => []
            ], [], 200);
        } catch (\Exception $e) {
            return $this->respond([
                'status' => false,
                'message' => 'Unable to logout user',
                'data' => [],
                'errors' => [$e->getMessage()]
            ], [], 200);
        }
    }
}
