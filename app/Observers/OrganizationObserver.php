<?php

namespace App\Observers;

use App\Models\Organization;
use App\Models\SystemNotification;
use App\Models\User;

class OrganizationObserver
{
    public function created(Organization $organization)
    {
        $this->notifyAppOwner(
            'New Organization Onboarded',
            "{$organization->name} has just registered on the platform.",
            'business',
            route('admin.organizations')
        );
    }

    public function updated(Organization $organization)
    {
        if ($organization->isDirty('kyc_status')) {
            $this->notifyAppOwner(
                'Organization KYC Update',
                "KYC status for {$organization->name} changed to ".strtoupper($organization->kyc_status),
                'verified_user',
                route('admin.organizations')
            );
        }

        if ($organization->isDirty('logo_path')) {
            $this->notifyAppOwner(
                'Brand Identity Update',
                "{$organization->name} updated their organization logo.",
                'image',
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
