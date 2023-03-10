<x-profile :sharedData="$sharedData" doctitle="Who {{ $sharedData['username'] }} follows">
    <div class="list-group">
        @foreach ($following as $follow)
            <a href="/profile/{{ $follow->userFollowed->username }}" class="list-group-item list-group-item-action">
                <img class="avatar-tiny" src="{{ $follow->userFollowed->avatar }}" />
                {{ $follow->userFollowed->username }}
            </a>
        @endforeach
    </div>
</x-profile>
