<?php

declare(strict_types=1);

namespace Infrastructure\Mail;

use Domain\Project\Model\Project;
use Domain\User\Model\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;

final class ProjectInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Project $project, public User $user)
    {
    }

    public function envelope(): Envelope
    {
        return  new Envelope(
            subject: sprintf('You have been invited to calloborate on "%s', $this->project->name)
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.project_invitation'
        );
    }
}
