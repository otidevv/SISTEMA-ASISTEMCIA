<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class AsistenciaDocenteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $asunto = "Registro de Asistencia - " . $this->data['nombre_docente'] . " [" . $this->data['fecha'] . "]";
        
        // Agregar la ruta del logo para poder incrustarlo (embedding)
        $this->data['logo_path'] = public_path('assets/images/logo cepre.png');

        return $this->subject($asunto)
                    ->view('emails.asistencia-docente');
    }
}
