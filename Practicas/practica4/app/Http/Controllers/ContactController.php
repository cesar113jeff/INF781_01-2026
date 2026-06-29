<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Rules\RecaptchaV3Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('contact.form');
    }

    public function store(Request $request): RedirectResponse
    {
        if (! empty($request->input('website'))) {
            return back()->with('status', 'Tu mensaje fue enviado correctamente.');
        }

        $data = $request->validate([
            'name'                 => ['required', 'string', 'max:120'],
            'email'                => ['required', 'email', 'max:180'],
            'message'              => ['required', 'string', 'min:10', 'max:2000'],
            'g-recaptcha-response' => ['required', 'string', new RecaptchaV3Rule()],
        ]);

        Contact::create([
            'name'    => $data['name'],
            'email'   => $data['email'],
            'message' => $data['message'],
            'ip'      => $request->ip(),
        ]);

        return back()->with('status', 'Tu mensaje fue enviado correctamente.');
    }
}
