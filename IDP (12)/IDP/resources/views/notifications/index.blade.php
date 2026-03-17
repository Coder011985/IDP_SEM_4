@extends(
auth()->user()->role === 'designer'
    ? 'layouts.designer'
    : (auth()->user()->role === 'client'
        ? 'layouts.client'
        : 'layouts.admin')
)

@section('title','Notifications')

@section('content')

<div class="max-w-4xl mx-auto space-y-6">

    {{-- HEADER --}}
    <div class="bg-white shadow rounded-lg p-5 flex items-center justify-between">

        <div>
            <h2 class="text-lg font-semibold">
                Notifications
            </h2>

            <p class="text-xs text-gray-500">
                Unread: {{ $unreadCount ?? 0 }}
            </p>
        </div>

        {{-- MARK ALL READ --}}
        @if(($unreadCount ?? 0) > 0)
        <form method="POST" action="{{ route('notifications.readAll') }}">
            @csrf

            <button
                class="text-sm bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition">
                Mark All Read
            </button>
        </form>
        @endif

    </div>


    {{-- NOTIFICATION LIST --}}
    <div class="bg-white shadow rounded-lg divide-y">

        @forelse($notifications as $notification)

        <div class="p-5 flex justify-between items-center
            {{ $notification->is_read ? 'bg-white' : 'bg-blue-50' }}">

            <div class="flex-1">

                <p class="text-gray-800">
                    {{ $notification->message }}
                </p>

                <p class="text-xs text-gray-500 mt-1">
                    {{ optional($notification->created_at)->diffForHumans() }}
                </p>

            </div>


            <div class="flex items-center gap-4">

                {{-- OPEN LINK --}}
                @if($notification->link)
                <a href="{{ $notification->link }}"
                   class="text-sm text-blue-600 hover:underline">
                    Open
                </a>
                @endif


                {{-- MARK READ --}}
                @if(!$notification->is_read)

                <form method="POST"
                      action="{{ route('notifications.read',$notification) }}">
                    @csrf

                    <button class="text-sm text-blue-600 hover:underline">
                        Mark read
                    </button>

                </form>

                @endif

            </div>

        </div>

        @empty

        <div class="p-6 text-center text-gray-500">
            No notifications yet
        </div>

        @endforelse

    </div>


    {{-- PAGINATION --}}
    @if(method_exists($notifications,'links'))
    <div>
        {{ $notifications->links() }}
    </div>
    @endif

</div>

@endsection
