<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $pdfPath;
    public $settings;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, $pdfPath = null)
    {
        $this->order = $order;
        $this->pdfPath = $pdfPath;
        // Récupérer les settings pour les utiliser dans l'email
        $this->settings = \App\Models\Setting::getPublicSettings();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmation de commande ' . $this->order->order_number . ' - KAZARIA',
            from: new \Illuminate\Mail\Mailables\Address('kazaria2025@gmail.com', 'Kazaria Marketplace'),
            replyTo: [
                new \Illuminate\Mail\Mailables\Address('contact@kazaria.ci', 'Support KAZARIA'),
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order-confirmation',
            with: [
                'settings' => $this->settings,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];
        
        if ($this->pdfPath && file_exists($this->pdfPath)) {
            $attachments[] = Attachment::fromPath($this->pdfPath)
                ->as('facture-' . $this->order->order_number . '.pdf')
                ->withMime('application/pdf');
        }
        
        return $attachments;
    }
}
