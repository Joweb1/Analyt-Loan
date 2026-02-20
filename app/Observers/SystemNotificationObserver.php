<?php

namespace App\Observers;

use App\Models\SystemNotification;
use App\Notifications\PushSystemNotification;

class SystemNotificationObserver
{
    /**
     * Handle the SystemNotification "created" event.
     */
    public function created(SystemNotification $systemNotification): void
    {
        $explicitRecipientId = $systemNotification->recipient_id;

        // 1. Handle Explicit Recipient (Borrower or App Owner)
        if ($explicitRecipientId) {
            $recipient = \App\Models\User::find($explicitRecipientId);

            if ($recipient) {
                // Direct notifications always go through if the user has push enabled
                // (App Owner always receives them)
                if ($recipient->isAppOwner() || $recipient->pushEnabled()) {
                    $recipient->notify(new PushSystemNotification($systemNotification));
                }
            }

            return;
        }

        // 2. Handle Broadcast Notifications (Staff/Admins)
        // Respect organization-wide broadcast settings for generic staff alerts
        $org = $systemNotification->organization;
        if (! $org || ! $org->push_notifications_enabled) {
            return;
        }

        $orgId = $systemNotification->organization_id;
        if (! $orgId) {
            return;
        }

        $targetRoles = ['Admin', 'Loan Analyst', 'Vault Manager', 'Credit Analyst', 'Collection Specialist'];
        $existingRoles = \Spatie\Permission\Models\Role::whereIn('name', $targetRoles)->pluck('name')->toArray();

        $roleStaff = collect();
        if (! empty($existingRoles)) {
            $roleStaff = \App\Models\User::where('organization_id', $orgId)
                ->role($existingRoles)
                ->get();
        }

        $permissionStaff = \App\Models\User::where('organization_id', $orgId)
            ->permission('access_org_notifications')
            ->get();

        $authorizedStaff = $roleStaff->merge($permissionStaff)->unique('id');

        foreach ($authorizedStaff as $staff) {
            // Deduplication: If the notification is about a specific loan,
            // don't send a broadcast push to the person who is actually the borrower of that loan.
            // They would have already received a direct notification (or shouldn't be bothered by staff-targeted alerts for their own loan).
            $loan = $systemNotification->subject;
            if ($loan instanceof \App\Models\Loan) {
                if ($loan->borrower->user_id === $staff->id) {
                    continue;
                }
            }

            if ($staff->pushEnabled()) {
                $staff->notify(new PushSystemNotification($systemNotification));
            }
        }
    }

    /**
     * Handle the SystemNotification "updated" event.
     */
    public function updated(SystemNotification $systemNotification): void
    {
        //
    }

    /**
     * Handle the SystemNotification "deleted" event.
     */
    public function deleted(SystemNotification $systemNotification): void
    {
        //
    }

    /**
     * Handle the SystemNotification "restored" event.
     */
    public function restored(SystemNotification $systemNotification): void
    {
        //
    }

    /**
     * Handle the SystemNotification "force deleted" event.
     */
    public function forceDeleted(SystemNotification $systemNotification): void
    {
        //
    }
}
