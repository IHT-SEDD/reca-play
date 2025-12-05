<?php

namespace App\Mail;

use App\Models\Record\RecordedVideo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecordedVideoReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public RecordedVideo $video;

    /**
     * Create a new message instance.
     */
    public function __construct(RecordedVideo $video)
    {
        $this->video = $video;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your video is ready on Reca Play',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.video-ready',
            with: [
                'recording' => $this->video->recording,
                'video' => $this->video,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
