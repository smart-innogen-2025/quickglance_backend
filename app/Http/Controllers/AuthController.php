<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;

class AuthController extends Controller
{

    public function show () {
        try {
            $authUser = Auth::user();
            return response()->json([
                'message' => 'User retrieved successfully',
                'user' => convertKeysToCamelCase($authUser->toArray()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while retrieving the user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function register(Request $request)
    {
        $validator = validator($request->all(), [
            'firstName' => 'required|string',
            'middleName'=> 'nullable|string',
            'lastName' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Create the user
        $user = User::create([
            'first_name' => $request->firstName,
            'middle_name' => $request->middleName,
            'last_name' => $request->lastName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        
        // Assign a default role (e.g., 'user')
        $userRole = Role::whereIn('name', [$request->assignedRole, 'user'])->first();

        if ($userRole) {
            $user->assignRole($userRole);
        }

        // Create a token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        
        // Return the token and user details
        return response()->json([
            'message' => 'User created successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 201); // 201 Created status code
    }

    public function update (Request $request, $id) {
        try{
            $authUser = Auth::user();

            if($authUser->id != $id) {
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            $validator = validator($request->all(), [
                'firstName' => 'nullable|string',
                'middleName'=> 'nullable|string',
                'lastName' => 'nullable|string',
                'email' => 'nullable|email',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            // Update the user
            $authUser->update([         // This still works even the php intelliphense doesn't recognize it
                'first_name' => $request->firstName ?? $authUser->first_name,
                'middle_name' => $request->middleName ?? $authUser->middle_name,
                'last_name' => $request->lastName ?? $authUser->last_name,
                'email' => $request->email ?? $authUser->email,
            ]);

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $authUser,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = validator($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Attempt to authenticate the user
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login details',
            ], 401);
        }

        // Get the authenticated user
        $user = User::where('email', $request->email)->first();

        // Create a token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return the token and user details
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        // Revoke the current user's token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    // public function sanctumCookie(Request $request) {
    //     return response()->noContent();
    // }
}