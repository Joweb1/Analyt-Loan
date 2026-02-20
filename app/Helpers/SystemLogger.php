<?php

namespace App\Helpers;

use App\Models\SystemNotification;
use Illuminate\Support\Facades\Auth;

class SystemLogger
{
    /**
     * Log a system activity as a notification.
     */
    public static function log($title, $message, $type = 'info', $category = null, $subject = null, $isActionable = false, $actionLink = null, $priority = 'low', $recipientId = null)
    {
        $userId = Auth::id();
        $orgId = Auth::user()?->organization_id;

        // If subject is provided, try to find organization_id
        if ($subject) {
            if ($subject instanceof \App\Models\Organization) {
                $orgId = $subject->id;
            } else {
                $orgId = data_get($subject, 'organization_id') 
                    ?? data_get($subject, 'loan.organization_id')
                    ?? data_get($subject, 'borrower.organization_id')
                    ?? Auth::user()?->organization_id;
            }
        }

        return SystemNotification::create([
            'organization_id' => $orgId,
            'user_id' => $userId,
            'recipient_id' => $recipientId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'category' => $category,
            'is_actionable' => $isActionable,
            'action_link' => $actionLink,
            'priority' => $priority,
            'subject_id' => $subject?->id,
            'subject_type' => $subject ? get_class($subject) : null,
        ]);
    }

    public static function success($title, $message, $category = null, $subject = null, $isActionable = false, $actionLink = null, $priority = 'low', $recipientId = null)
    {
        return self::log($title, $message, 'success', $category, $subject, $isActionable, $actionLink, $priority, $recipientId);
    }

    public static function warning($title, $message, $category = null, $subject = null, $isActionable = false, $actionLink = null, $priority = 'low', $recipientId = null)
    {
        return self::log($title, $message, 'warning', $category, $subject, $isActionable, $actionLink, $priority, $recipientId);
    }

    public static function danger($title, $message, $category = null, $subject = null, $isActionable = false, $actionLink = null, $priority = 'low', $recipientId = null)
    {
        return self::log($title, $message, 'danger', $category, $subject, $isActionable, $actionLink, $priority, $recipientId);
    }

    public static function action($title, $message, $actionLink, $category = 'task', $subject = null, $priority = 'medium', $recipientId = null)
    {
        return self::log($title, $message, 'info', $category, $subject, true, $actionLink, $priority, $recipientId);
    }
}
