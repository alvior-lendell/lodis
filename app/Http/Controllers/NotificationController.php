<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $filter = $request->get('filter', 'all');

        $query = $filter === 'unread' 
            ? $user->unreadNotifications() 
            : $user->notifications();

        $notifications = $query->paginate(15)->withQueryString();

        return view('notifications.index', compact('notifications'));
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
        $notification = auth()->user()->notifications()->where('id', $id)->first();
    
        if ($notification) {
            if (is_null($notification->read_at)) {
                $notification->markAsRead();
            }
    
            // Redirect to notification URL if present, fallback to dashboard
            $targetUrl = $notification->data['url'] ?? route('dashboard');
    
            return redirect($targetUrl);
        }
    
        return redirect()->route('notifications.index');
    }
}