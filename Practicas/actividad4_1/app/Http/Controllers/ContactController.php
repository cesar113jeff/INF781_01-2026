<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Rules\Recaptcha;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'              => ['required', 'string', 'max:100'],
            'email'               => ['required', 'email'],
            'mensaje'             => ['required', 'string', 'max:1000'],
            'g-recaptcha-response' => ['required', 'string', new Recaptcha],
        ]);

        Contact::create([
            'name'    => $data['nombre'],
            'email'   => $data['email'],
            'message' => $data['mensaje'],
            'ip'      => $request->ip(),
        ]);

        return redirect()->route('contact.show')
            ->with('success', 'Mensaje enviado correctamente.');
    }
}
