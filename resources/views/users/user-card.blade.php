

<div class="card">

    @if($post->user->avatar)
        <img style="width: 100%" src="{{ url('storage/users/' . $post->user->id . '/' . $post->user->avatar) }}">
    @else
        <img style="width: 40%" src="{{ asset('images/avatar.png') }}">
    @endif

</div>
