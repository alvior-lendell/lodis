@auth
    <header-navigation
        :user-role='@json($userRole)'
        :full-name='@json($fullName)'
        :first-name='@json($firstName)'
        :user-photo='@json($userPhoto)'
        :auth-user-id='@json($authUser?->id)'
        :unread-count='@json($unreadCount)'
        :notifications='@json($notifications)'
        csrf-token="{{ csrf_token() }}"
        logo-url="{{ asset('images/LODISv2.png') }}"
        sound-url="{{ asset('sounds/notification.mp3') }}"
        :routes='@json($routes)'
    ></header-navigation>
@endauth