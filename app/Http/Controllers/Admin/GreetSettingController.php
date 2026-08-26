<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GreetSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GreetSettingController extends Controller
{
    /**
     * Public greet page — anyone can see the message.
     */
    public function index(): Response
    {
        $greetSetting = GreetSetting::first();

        return Inertia::render('greet', ['message'=> $greetSetting?->message,]);
    }

    /**
     * Admin form to edit the greeting message.
     */
    public function edit(): Response
    {
        $greetSetting = GreetSetting::first();

        return Inertia::render('admin/greet-setting', ['message'=> $greetSetting?->message,]);
    }

    /**
     * Save the updated greeting message.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $greetSetting = GreetSetting::first();
        $greetSetting->update(['message' => $request->message]);

        return back()->with('success', 'Greeting updated!');
    }
}
