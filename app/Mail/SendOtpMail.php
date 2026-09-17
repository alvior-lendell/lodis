<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance with optional customizable title and description.
     */
    public function __construct(
        public string $otp,
        public string $name,
        public string $title = 'Account Security Verification',
        public string $description = 'We received a request to authenticate your identity on LODISv2. Use the One-Time Password (OTP) below to proceed:'
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "LODISv2 - {$this->title}",
        );
    }

    /**
     * Get the message content definition.
     * Note: Public properties are automatically accessible inside the 'emails.otp' Blade view.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}