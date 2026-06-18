<?php

namespace App\Observers;

use App\Models\CashbookEntry;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Carbon;

class CashbookUnlockObserver
{
    /**
     * Unlock cashbook if it was already verified.
     */
    public function handle(string $date, int $organizationId): void
    {
        $entry = CashbookEntry::where('organization_id', $organizationId)
            ->where('entry_date', $date)
            ->where('status', 'verified')
            ->first();

        if ($entry) {
            $entry->update([
                'status' => 'pending',
                'verified_at' => null,
                'audit_hash' => null,
            ]);

            // Notify Admins
            $admins = User::role('Admin')->where('organization_id', $organizationId)->get();
            foreach ($admins as $admin) {
                SystemNotification::create([
                    'user_id' => $admin->id,
                    'title' => 'Cashbook Unlocked',
                    'message' => "Cashbook for {$date} was automatically unlocked due to a late transaction.",
                    'category' => 'cashbook',
                ]);
            }
        }
    }
}
