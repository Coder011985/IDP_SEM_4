<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Notification List
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $userId = Auth::id();

        $notifications = Notification::where('user_id', $userId)
            ->latest()
            ->paginate(20);

        $unreadCount = Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        return view('notifications.index', compact(
            'notifications',
            'unreadCount'
        ));
    }



    /*
    |--------------------------------------------------------------------------
    | Mark Single Notification Read
    |--------------------------------------------------------------------------
    */

    public function markRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$notification->is_read) {
            $notification->update([
                'is_read' => true
            ]);
        }

        return $notification->link
            ? redirect($notification->link)
            : back();
    }



    /*
    |--------------------------------------------------------------------------
    | Mark All Notifications Read
    |--------------------------------------------------------------------------
    */

    public function markAllRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true
            ]);

        return back();
    }

}
