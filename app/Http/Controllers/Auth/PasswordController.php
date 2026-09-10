<?php

namespace App\Http\Controllers\Auth;

use App\Concerns\PasswordValidationRules;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    use PasswordValidationRules;

    // -----------------------------------------------------------------------
    // FORGOT PASSWORD
    // -----------------------------------------------------------------------

    /**
     * Show the "Forgot Password" form.
     *
     * Replaces Fortify::requestPasswordResetLinkView(...)
     */
    public function showForgotForm(Request $request): Response
    {
        return Inertia::render('auth/forgot-password', [
            // Same status key Fortify used after sending the email.
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Send a password reset link to the user's email.
     *
     * Laravel's Password facade handles the token generation + email sending.
     * We just call it and handle the result.
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Password::sendResetLink() looks up the user by email, generates a
        // signed token, stores it in `password_reset_tokens`, and sends the
        // reset email. It returns one of two string constants:
        //   Password::RESET_LINK_SENT — success
        //   Password::INVALID_USER    — no user with that email found
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            // Flash a success status to show on the same page.
            return back()->with('status', __($status));
        }

        // On failure, throw a validation error pointing at the email field.
        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }

    // -----------------------------------------------------------------------
    // RESET PASSWORD
    // -----------------------------------------------------------------------

    /**
     * Show the password reset form (linked from the email).
     *
     * Replaces Fortify::resetPasswordView(...)
     * The reset link contains ?token=... and ?email=... as query params.
     */
    public function showResetForm(Request $request): Response
    {
        return Inertia::render('auth/reset-password', [
            // These are read by the React page to pre-fill hidden fields.
            'email' => $request->email,
            'token' => $request->route('token'),
            'passwordRules' => \Illuminate\Validation\Rules\Password::defaults()
                ->toPasswordRulesString(),
        ]);
    }

    /**
     * Reset the user's password.
     *
     * Laravel's Password::reset() validates the token, finds the user,
     * calls our closure to update the password, then clears the token.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => $this->passwordRules(),
        ]);

        // Password::reset() does all the token verification internally.
        // Our closure receives the User model and plain-text new password.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password): void {
                // forceFill bypasses mass-assignment protection — safe here
                // because we own every value (they came from validated input).
                $user->forceFill([
                    'password'       => $password,  // cast auto-hashes this
                    'remember_token' => Str::random(60),  // rotate the remember token
                ])->save();

                // Fire the PasswordReset event — Laravel listeners may use this
                // (e.g., to log the event or notify the user).
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            // Success: redirect to login with a status flash message.
            return redirect()->route('login')->with('status', __($status));
        }

        // Failure (bad token, user not found): validation error on email field.
        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}