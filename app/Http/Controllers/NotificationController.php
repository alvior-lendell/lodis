<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $currentFilter = $request->get('filter', 'all');

        $query = $currentFilter === 'unread' 
            ? $user->unreadNotifications() 
            : $user->notifications();

        $notifications = $query->paginate(15)->withQueryString();

        // Format notification models to strictly match Vue prop expectations
        $formattedNotifications = collect($notifications->items())->map(function ($notification) {
            $data = $notification->data ?? [];

            return [
                'id'               => $notification->id,
                'data'             => [
                    'type'       => $data['type'] ?? 'info',
                    'title'      => $data['title'] ?? 'System Notification',
                    'message'    => $data['message'] ?? $data['body'] ?? 'You have a new system alert.',
                    'url'        => $data['url'] ?? null,
                    'ip_address' => $data['ip_address'] ?? null,
                    'platform'   => $data['platform'] ?? null,
                ],
                'is_unread'        => is_null($notification->read_at),
                'created_at_human' => $notification->created_at?->diffForHumans() ?? '',
                'read_url'         => Route::has('notifications.readAndRedirect') 
                                        ? route('notifications.readAndRedirect', $notification->id) 
                                        : '#',
                'mark_read_url'    => Route::has('notifications.markRead') 
                                        ? route('notifications.markRead', $notification->id) 
                                        : '#',
                'destroy_url'      => Route::has('notifications.destroy') 
                                        ? route('notifications.destroy', $notification->id) 
                                        : '#',
            ];
        });

        $totalCount  = $user->notifications()->count();
        $unreadCount = $user->unreadNotifications()->count();

        $filterUrls = [
            'all'    => route('notifications.index', ['filter' => 'all']),
            'unread' => route('notifications.index', ['filter' => 'unread']),
        ];

        return view('notifications.index', compact(
            'notifications',
            'formattedNotifications',
            'totalCount',
            'unreadCount',
            'currentFilter',
            'filterUrls'
        ));
    }

    public function markRead(string $id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }

        return back()->with('status', 'Notification marked as read.');
    }

    public function markAllRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'All notifications marked as read.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->delete();
        }

        return back()->with('status', 'Notification deleted successfully.');
    }
    
    public function readAndRedirect(string $id): RedirectResponse
    {
        $user = auth()->user();
        $notification = $user->notifications()->where('id', $id)->first();
    
        if ($notification) {
            if (is_null($notification->read_at)) {
                $notification->markAsRead();
            }
    
            $data     = $notification->data ?? [];
            $type     = $data['type'] ?? null;
            $category = $data['category'] ?? null;

            // Resolve target URL with type-specific fallbacks
            $targetUrl = $data['url'] 
                ?? $data['action_url'] 
                ?? $data['link'] 
                ?? $data['redirect_url']
                // Fallback for Device Workstation Review
                ?? (isset($data['auth_id']) && Route::has('device.review') 
                    ? route('device.review', $data['auth_id']) 
                    : null)
                // Fallback for New User Registration / Management
                ?? (($type === 'user_registered' || $category === 'user_management' || isset($data['employee_id'])) && Route::has('users.index') 
                    ? route('users.index') 
                    : null)
                // Default Page Fallback
                ?? (Route::has('users.index') ? route('users.index') : route('dashboard'));
    
            return redirect($targetUrl);
        }

        // Fallback for real-time notifications or unmatched IDs
        $latestUnreadUserNotif = $user->unreadNotifications()
            ->where('type', \App\Notifications\UserRegisteredNotification::class)
            ->latest()
            ->first();

        if ($latestUnreadUserNotif) {
            $latestUnreadUserNotif->markAsRead();
        }
    
        return Route::has('users.index') ? redirect()->route('users.index') : redirect()->route('dashboard');
    }
}