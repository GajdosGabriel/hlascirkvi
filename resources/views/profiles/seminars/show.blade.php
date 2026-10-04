@extends('layouts.dashboard')
@section('title')
    <title>{{ $seminar->title }} — správa kolekcie</title>
@endsection

@section('content')
    <x-pages.dashboard>
        <x-slot name="title">{{ $seminar->title }}</x-slot>
        <x-slot name="title_right">@include('seminars._actions')</x-slot>
        <x-slot name="page">
            <x-dashboard.panel class="mb-6">
                @include('seminars._info')
            </x-dashboard.panel>
            <p class="mb-4"><a class="ar-link" href="{{ route('seminars.show', $seminar) }}">Zobraziť verejný náhľad →</a></p>
            <x-dashboard.panel title="Príspevky v kolekcii">
                <p class="text-sm text-gray-600 mb-4">Označte príspevky a pridajte ich do kolekcie alebo ich z nej odoberte. Odobratie príspevok nezmaže. Jeden príspevok môže patriť do viacerých kolekcií.</p>
                <form method="get" action="{{ route('profile.canals.seminars.show', [$canal, $seminar]) }}" class="flex flex-wrap gap-3 mb-4">
                    <div class="flex-1"><label for="collection-q">Hľadať príspevky kanála</label><input id="collection-q" name="q" type="search" class="form-control" value="{{ $search }}" maxlength="200"></div>
                    <div><label for="membership">Zaradenie</label><select id="membership" name="membership" class="form-control">
                        <option value="all" @selected($membership === 'all')>Všetky príspevky kanála</option>
                        <option value="in" @selected($membership === 'in')>V tejto kolekcii</option>
                        <option value="out" @selected($membership === 'out')>Mimo tejto kolekcie</option>
                    </select></div>
                    <button class="btn" type="submit">Filtrovať</button>
                </form>
                @if ($errors->any()) <div role="alert" class="text-red-700 mb-3">{{ $errors->first() }}</div> @endif
                <form method="post" action="{{ route('profile.canals.seminars.posts', [$canal, $seminar]) }}">
                    @csrf
                    @forelse ($posts as $post)
                        <div class="border-b py-3 flex items-start gap-3">
                            <input id="member-{{ $post->id }}" type="checkbox" name="posts[]" value="{{ $post->id }}" class="mt-1" @checked(in_array($post->id, old('posts', [])))>
                            <div class="flex-1">
                                <label for="member-{{ $post->id }}" class="font-semibold">{{ $post->title }}</label>
                                <p class="text-sm text-gray-500">{{ $post->in_collection ? 'V tejto kolekcii' : 'Mimo tejto kolekcie' }} · {{ $post->published_at ? 'Zverejnené' : 'Vo fronte' }}</p>
                            </div>
                            @can('update', $post)
                                <a class="ar-link text-sm" href="{{ route('profile.posts.edit', $post) }}">Upraviť</a>
                            @endcan
                        </div>
                    @empty
                        <x-dashboard.empty>Žiadne príspevky nezodpovedajú výberu.</x-dashboard.empty>
                    @endforelse
                    @if ($posts->isNotEmpty())
                        <div class="flex flex-wrap gap-3 mt-4">
                            <button type="submit" name="action" value="add" class="btn">Pridať označené</button>
                            <button type="submit" name="action" value="remove" class="btn">Odobrať označené</button>
                        </div>
                    @endif
                </form>
                <div class="mt-4">{{ $posts->links() }}</div>
            </x-dashboard.panel>
        </x-slot>
    </x-pages.dashboard>
@endsection
