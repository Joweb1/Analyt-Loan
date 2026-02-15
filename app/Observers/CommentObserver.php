<?php

namespace App\Observers;

use App\Models\Comment;
use App\Helpers\SystemLogger;
use App\Models\Loan;

class CommentObserver
{
    /**
     * Handle the Comment "created" event.
     */
    public function created(Comment $comment): void
    {
        // Check if the comment is on a Loan
        if ($comment->commentable_type === Loan::class) {
            $loan = $comment->commentable;
            $user = $comment->user;

            SystemLogger::log(
                'New Comment',
                "{$user->name} commented on Loan #{$loan->loan_number}: \"{$comment->body}\"",
                'info',
                'comment',
                $loan
            );
        }
    }

    /**
     * Handle the Comment "updated" event.
     */
    public function updated(Comment $comment): void
    {
        //
    }

    /**
     * Handle the Comment "deleted" event.
     */
    public function deleted(Comment $comment): void
    {
        //
    }

    /**
     * Handle the Comment "restored" event.
     */
    public function restored(Comment $comment): void
    {
        //
    }

    /**
     * Handle the Comment "force deleted" event.
     */
    public function forceDeleted(Comment $comment): void
    {
        //
    }
}