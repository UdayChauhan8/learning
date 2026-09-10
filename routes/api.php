<?php

use App\Http\Controllers\Api\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────────────
// Protected API routes — require a valid Passport Bearer token
// ─────────────────────────────────────────────────────────────────────────────
// The `auth:api` middleware uses the `api` guard (which we set to `passport`
// in config/auth.php). Clients must send:
//   Authorization: Bearer <access_token>
//
// Unlike the web guard (session cookies), the API guard is stateless —
// no session is created, no cookies are set.
Route::middleware('auth:api')->group(function () {

    // Returns the currently authenticated user's data.
    // Useful for mobile/SPA clients to confirm who they are.
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Your existing API resource — now protected with Passport tokens
    Route::apiResource('items', OrderController::class);
});