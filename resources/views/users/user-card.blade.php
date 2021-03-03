

<div class="card">

    @if($post->orgamization->avatar)
        <img style="width: 100%" src="{{ url('storage/organizations/' . $post->organization_id . '/' . $post->organization->avatar) }}">
    @else
        <img style="width: 40%" src="{{ asset('images/avatar.png') }}">
    @endif

</div>
