<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TwoFactorAuthenticationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $codigo2fa;
    public $usuario;

    /**
     * Create a new message instance.
     */
    public function __construct($codigo2fa, $usuario)
    {
        $this->codigo2fa = $codigo2fa;
        $this->usuario = $usuario;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Código de verificación 2FA')
                    ->view('Correo.2FA');
    }
}
