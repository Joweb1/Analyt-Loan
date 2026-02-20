<?php

namespace App\Observers;

use App\Models\SystemNotification;
use App\Models\User;

class UserObserver
{
    public function created(User $user)
    {
        // If a new staff is added to any organization, notify App Owner
        if ($user->organization_id && ! $user->hasRole('Borrower')) {
            $this->notifyAppOwner(
                'New Staff Onboarding',
                "{$user->name} has been added as staff to {$user->organization->name}.",
                'person_add',
                route('admin.organizations')
            );
        }
    }

    private function notifyAppOwner($title, $message, $category, $link)
    {
        $appOwner = User::where('email', config('app.owner'))->first();

        if ($appOwner) {
            SystemNotification::create([
                'organization_id' => $appOwner->organization_id,
                'recipient_id' => $appOwner->id,
                'title' => $title,
                'message' => $message,
                'category' => $category,
                'priority' => 'normal',
                'is_actionable' => true,
                'action_link' => $link,
            ]);
        }
    }
}
