<?php

namespace App\Helpers;

use App\Models\SystemNotification;
use Illuminate\Support\Facades\Auth;

class SystemLogger
{
    /**
     * Log a system activity as a notification.
     */
    public static function log($title, $message, $type = 'info', $category = null, $subject = null, $isActionable = false, $actionLink = null, $priority = 'low')
    {
        $userId = Auth::id();
        $orgId = Auth::user()?->organization_id;

        // If subject is provided and has organization_id, use it
        if ($subject && isset($subject->organization_id)) {
            $orgId = $subject->organization_id;
        }

        return SystemNotification::create([
            'organization_id' => $orgId,
            'user_id' => $userId,
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

    public static function success($title, $message, $category = null, $subject = null, $isActionable = false, $actionLink = null, $priority = 'low')
    {
        return self::log($title, $message, 'success', $category, $subject, $isActionable, $actionLink, $priority);
    }

    public static function warning($title, $message, $category = null, $subject = null, $isActionable = false, $actionLink = null, $priority = 'low')
    {
        return self::log($title, $message, 'warning', $category, $subject, $isActionable, $actionLink, $priority);
    }

    public static function danger($title, $message, $category = null, $subject = null, $isActionable = false, $actionLink = null, $priority = 'low')
    {
        return self::log($title, $message, 'danger', $category, $subject, $isActionable, $actionLink, $priority);
    }

    public static function action($title, $message, $actionLink, $category = 'task', $subject = null, $priority = 'medium')
    {
        return self::log($title, $message, 'info', $category, $subject, true, $actionLink, $priority);
    }
}
