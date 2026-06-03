@extends('admin.layout')
@section('title', 'แจ้งเตือนการจอง')
@section('content')
<div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 mb-5 sm:mb-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-semibold">แจ้งเตือนการจอง</h1>
        <p class="text-sm text-slate-600 mt-1">
            บันทึกอัตโนมัติเมื่อมีจองใหม่ ชำระเงิน โอนรอตรวจ ยกเลิก หรือเลื่อนนัด
            @if ($unreadCount > 0)
                · <span class="text-amber-600 font-medium">{{ $unreadCount }} ยังไม่อ่าน</span>
            @endif
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('admin.booking-notifications.read-all') }}">
                @csrf
                <button type="submit" class="text-sm border rounded-lg px-3 py-2 hover:bg-slate-50">อ่านทั้งหมด</button>
            </form>
        @endif
        <a
            href="{{ route('admin.booking-notifications.index', ['filter' => $filter === 'unread' ? '' : 'unread']) }}"
            class="text-sm border rounded-lg px-3 py-2 {{ $filter === 'unread' ? 'bg-emerald-600 text-white border-emerald-600' : 'hover:bg-slate-50' }}"
        >
            {{ $filter === 'unread' ? 'แสดงทั้งหมด' : 'เฉพาะที่ยังไม่อ่าน' }}
        </a>
    </div>
</div>

<div class="space-y-3">
    @forelse ($notifications as $n)
        <article class="rounded-xl border bg-white p-4 shadow-sm {{ $n->isUnread() ? 'border-emerald-300 ring-1 ring-emerald-100' : 'border-slate-200' }}">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    @if ($n->isUnread())
                        <span class="size-2 rounded-full bg-emerald-500 shrink-0" title="ยังไม่อ่าน"></span>
                    @endif
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $n->isUnread() ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                        {{ $n->eventLabel() }}
                    </span>
                    <h2 class="font-semibold text-slate-900 truncate">{{ $n->title }}</h2>
                </div>
                <time class="text-xs text-slate-500 whitespace-nowrap">{{ $n->created_at->format('d/m/Y H:i') }}</time>
            </div>
            <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $n->message }}</p>
            <div class="mt-3 flex flex-wrap gap-2">
                @if ($n->appointment_id)
                    <a href="{{ route('admin.appointments.edit', $n->appointment_id) }}" class="text-sm text-emerald-600 font-medium hover:underline">
                        เปิดการจอง
                    </a>
                @endif
                @if ($n->isUnread())
                    <form method="POST" action="{{ route('admin.booking-notifications.read', $n) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-slate-600 hover:text-slate-900">ทำเครื่องหมายว่าอ่านแล้ว</button>
                    </form>
                @endif
            </div>
        </article>
    @empty
        <div class="rounded-xl border bg-white p-12 text-center text-slate-500">
            ยังไม่มีการแจ้งเตือน — เมื่อมีการจองใหม่จะแสดงที่นี่
        </div>
    @endforelse
</div>

@include('admin.partials.pagination', ['paginator' => $notifications])
@endsection
