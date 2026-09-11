<?php

namespace App\Http\Controllers\Auth;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    use PasswordValidationRules, ProfileValidationRules;

    public function showLogin(Request $request): Response
    {
        return Inertia::render('auth/login', [
            // canResetPassword: tells the login page whether to show the
            // "Forgot your password?" link. Always true now.
            'canResetPassword' => true,

            // status: shown after a password reset email is sent.
            // Fortify flashed this to the session; we do the same.
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Show the registration form.
     */
    public function showRegister(): Response
    {
        return Inertia::render('auth/register', [
            // passwordRules: the browser uses this to show a tooltip.
            // We pull it from Laravel's Password::defaults() — same as Fortify did.
            'passwordRules' => Password::defaults()
                ->toPasswordRulesString(),
        ]);
    }

    // -----------------------------------------------------------------------
    // HANDLE FORM SUBMISSIONS
    // -----------------------------------------------------------------------

    /**
     * Handle a login request.
     *
     * This is what POST /login does. Fortify had its own pipeline for this.
     * Now we own every line of it — great for learning.
     */
    public function login(Request $request): RedirectResponse
    {
        // Step 1: Validate inputs — just email + password format checks.
        // This does NOT check against the database yet.
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Step 2: Rate limiting — stop brute-force attacks.
        // We throttle by email+IP, exactly as Fortify's default limiter does.
        // 'login' is the limiter name; 5 attempts per minute.
        $throttleKey = Str::transliterate(
            Str::lower($request->input('email')).'|'.$request->ip()
        );

        if (RateLimiter::tooManyAttempts('login.'.$throttleKey, 5)) {
            $seconds = RateLimiter::availableIn('login.'.$throttleKey);

            throw ValidationException::withMessages([
                'email' => [trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ])],
            ])->status(429);
        }

        // Step 3: Attempt to log in using the 'web' guard (session-based).
        // Auth::attempt() looks up the user by email, bcrypt-verifies the password,
        // then starts a session if correct. The 'remember' checkbox controls
        // whether a long-lived remember-me cookie is set.
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            // Wrong credentials: record the failed attempt for rate limiting
            // and throw a validation error back to the Inertia form.
            RateLimiter::hit('login.'.$throttleKey);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Step 4: Successful login — clear the rate limiter counter.
        RateLimiter::clear('login.'.$throttleKey);

        // Step 5: Regenerate the session ID to prevent session fixation attacks.
        // Session fixation: an attacker tricks you into using a known session ID,
        // then hijacks your session after you log in. Regenerating prevents this.
        $request->session()->regenerate();

        // Step 6: Redirect to the intended URL or fall back to /dashboard.
        // `intended()` remembers where the user was trying to go before being
        // redirected to login (e.g., they tried to visit /dashboard unauthenticated).
        return redirect()->intended('/dashboard');
    }

    /**
     * Handle a registration request.
     *
     * This replaces Fortify's CreateNewUser action pipeline.
     */
    public function register(Request $request): RedirectResponse
    {
        // Validate: profileRules() covers name + email uniqueness.
        // passwordRules() enforces production password strength.
        $request->validate([
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ]);

        // Create the user. The `password` cast on the model automatically
        // bcrypt-hashes the plain-text password before saving.
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        // Log the user in immediately after registration.
        // No need to redirect to login — we can auth them right here.
        Auth::login($user);

        // Regenerate session ID (same reason as in login()).
        $request->session()->regenerate();

        return redirect('/dashboard');
    }

    /**
     * Handle a logout request.
     *
     * Fortify registered POST /logout → this. We do the same three steps.
     */
    public function logout(Request $request): RedirectResponse
    {
        // Step 1: Log out of the web guard (destroys the session user reference).
        Auth::logout();

        // Step 2: Invalidate the session (clear all session data, generate new ID).
        $request->session()->invalidate();

        // Step 3: Regenerate the CSRF token so old tokens can't be replayed.
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
