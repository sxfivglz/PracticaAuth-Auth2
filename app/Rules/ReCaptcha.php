<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReCaptcha implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
public function passes($attribute, $value)
{
    $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
        'secret' => config('services.recaptcha.secret'),
        'response' => $value,
    ]);

    $decodedResponse = json_decode($response->body());

    if ($decodedResponse->success) {
        return true;
    } else {

        //Redirigir a registro con error
        redirect()->route('registro')->with(['mensaje' => 'El google reCAPTCHA es necesario, confirme las credenciales o inténtelo más tarde.']);

        
        Log::error('reCAPTCHA verification failed. Response: ' . $response->body());

        return false;
    }
}

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'El google reCAPTCHA es necesario, confirme las credenciales o inténtelo más tarde.';
    }
}