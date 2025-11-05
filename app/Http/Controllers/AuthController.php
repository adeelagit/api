<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Register a new user, optionally creating a new company.
     */
    public function register(Request $request)
    {
        $company = Company::where('name', $request->company_name)->first();

        $request->validate([
            // User Validation
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',

            // Company Registration/Assignment Fields
            'company_name' => [
                'required', 'string', 'max:255',
                Rule::unique('restaurants', 'name')->ignore($company ? $company->id : null),
            ],
            'company_mobile_number' => [
                Rule::requiredIf(!$company),
                'string',
                'max:15',
                Rule::unique('restaurants', 'mobile_number')->ignore($company ? $company->id : null),
            ],
            'company_subdomain' => [
                Rule::requiredIf(!$company),
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('restaurants', 'subdomain')->ignore($company ? $company->id : null),
            ],
            'company_address' => 'nullable|string',
            'company_location' => 'nullable|string',
        ]);

        try {
            return DB::transaction(function () use ($request, $company) {
                $new_company = false;
                if (!$company) {
                    // Create New Company
                    $new_company = true;
                    $company = Company::create([
                        'name' => $request->company_name,
                        'mobile_number' => $request->company_mobile_number,
                        'subdomain' => $request->company_subdomain,
                        'address' => $request->company_address,
                        'location' => $request->company_location,
                    ]);
                }

                // 3. Create User and link to Company
                $user = User::create([
                    'company_id' => $company->id,
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                // 4. Generate JWT Token
                $token = JWTAuth::fromUser($user);

                // Save token in user table
                $user->access_token = "bearer ".$token;
                $user->save();

                $message = ($new_company)? 'User and Company registered successfully.': 'User registered successfully';
                return response()->json([
                    'message' => $message,
                    'user' => $user->load('company'),
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60,
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Registration failed.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Log in a user and return a JWT token.
     */
    public function login(Request $request)
    {
        $tenant = $this->tenantService->getTenant();
        $host = $request->getHost();

        if (!$tenant) {
            if (str_contains($host, '127.0.0.1') && $request->header('X-Tenant-Subdomain') === null) {
                return response()->json([
                    'error' => 'Tenant identification required.',
                    'message' => 'Login failed. When using IP/Port (127.0.0.1:8000), please provide the company subdomain using the **X-Tenant-Subdomain** header in your request.'
                ], 400);
            }
        }

        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Attempt to authenticate the user within the current tenant
        $token = JWTAuth::attempt($credentials);

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }


    /**
     * Log the user out (Invalidate the token).
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message'=>'Successfully logged out']);
        } catch (\Exception $e){
            return response()->json(['error'=>'Failed to logout'], 500);
        }
    }

    /**
     * Refresh a token.
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => auth()->user(),
        ]);
    }
}