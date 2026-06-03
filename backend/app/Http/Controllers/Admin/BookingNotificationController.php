<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $query = BookingNotification::with(['appointment.clinic', 'appointment.service'])
            ->orderByDesc('created_at');

        if ($request->string('filter') === 'unread') {
            $query->whereNull('read_at');
        }

        return view('admin.booking-notifications.index', [
            'notifications' => $query->paginate(config('admin.per_page'))->withQueryString(),
            'unreadCount' => BookingNotification::whereNull('read_at')->count(),
            'filter' => $request->string('filter')->toString(),
        ]);
    }

    public function markRead(BookingNotification $booking_notification): RedirectResponse
    {
        $booking_notification->markRead();

        return back()->with('success', 'ทำเครื่องหมายว่าอ่านแล้ว');
    }

    public function markAllRead(): RedirectResponse
    {
        BookingNotification::whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'อ่านทั้งหมดแล้ว');
    }
}
