<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class NfeMail extends Mailable
{
    use Queueable, SerializesModels;

    private $titulo;
    private $conteudo;
    private $mes;
    private $ano;
    private $chave;
    private $tipo;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($titulo, $conteudo, $mes, $ano, $chave, $tipo)
    {
        $this->titulo = $titulo;
        $this->conteudo = $conteudo;
        $this->mes = $mes;
        $this->ano = $ano;
        $this->chave = $chave;
        $this->tipo = $tipo; // valores aceitos: nfe, cc, cancelada
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->tipo == 'nfe') {
            $this->sendNfeEmail();
        } else if ($this->tipo == 'cc') {
            $this->sendCCEmail();
        } else if ($this->tipo == 'cancelada') {
            $this->sendCanceladaEmail();
        }
    }

    public function sendNfeEmail()
    {
        return $this->markdown('mail.nfe-mail')->with([
            'titulo' => $this->titulo,
            'conteudo' => $this->conteudo,
        ])
            ->attach(Storage::disk('local')->path("public/" . session('tenant')->nome . "/nfe/{$this->mes}-{$this->ano}/{$this->chave}.pdf"), [
                'as' => "{$this->chave}.pdf",
                'mime' => 'application/pdf',
            ])
            ->attach(Storage::disk('local')->path("public/" . session('tenant')->nome . "/nfe/{$this->mes}-{$this->ano}/{$this->chave}.xml"), [
                'as' => "{$this->chave}.xml",
                'mime' => 'application/xml',
            ]);
    }

    public function sendCCEmail()
    {
        return $this->markdown('mail.nfe-mail')->with([
            'titulo' => $this->titulo,
            'conteudo' => $this->conteudo,
        ])
            ->attach(Storage::disk('local')->path("public/" . session('tenant')->nome . "/nfeCorrecoes/{$this->mes}-{$this->ano}/{$this->chave}.pdf"), [
                'as' => "{$this->chave}.pdf",
                'mime' => 'application/pdf',
            ])
            ->attach(Storage::disk('local')->path("public/" . session('tenant')->nome . "/nfeCorrecoes/{$this->mes}-{$this->ano}/{$this->chave}.xml"), [
                'as' => "{$this->chave}.xml",
                'mime' => 'application/xml',
            ]);
    }

    public function sendCanceladaEmail()
    {
        return $this->markdown('mail.nfe-mail')->with([
            'titulo' => $this->titulo,
            'conteudo' => $this->conteudo,
        ])
            ->attach(Storage::disk('local')->path("public/" . session('tenant')->nome . "/nfeCanceladas/{$this->mes}-{$this->ano}/{$this->chave}.xml"), [
                'as' => "{$this->chave}.xml",
                'mime' => 'application/xml',
            ]);
    }
}
