<?php

namespace App\Mail;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecapEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function envelope()
    {
        return new Envelope(
            subject: 'Site Recap!~~~',
        );
    }

    public function content()
    {
        return new Content(
            view: 'recapemail',
            with: ['postCount' => Post::count(), 'userCount' => User::count()]
        );
    }

}