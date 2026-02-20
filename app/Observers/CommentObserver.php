<?php

namespace App\Observers;

use App\Helpers\SystemLogger;
use App\Models\Comment;
use App\Models\Loan;

class CommentObserver
{
    /**
     * Handle the Comment "created" event.
     */
    public function created(Comment $comment): void
    {
        $loan = $comment->commentable;
        if ($loan instanceof Loan) {
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
