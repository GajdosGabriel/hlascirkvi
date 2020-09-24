<div style="margin-bottom: 3rem">
    <h5 style="font-weight: bold">Kraje Slovenska</h5>

    <ul>
        <li><a href="{{ route('event.index') }}"> @if(!request()->has('region')) <i style="color: #3b32b3" class="fas fa-check"></i> @endif Všetky regiony</a></li>
    @forelse(\App\Region::all() as $region)
            <li class="level">
                <a href="?region={{ $region->slug }}"> @if(request('region') == $region->slug) <i style="color: #3b32b3" class="fas fa-check"></i> @endif {{ $region->title }}</a>
                <span>{{ \App\Event::activeEvents()->where('village_id', $region->id)->get()->count() }}</span>
            </li>

    @empty
            Žiadne regióny
    @endforelse


        @can('admin')
            <li class="level">
                <a href="?published=0"> @if(request()->has('published')) <i style="color: #3b32b3" class="fas fa-check"></i> @endif
                    Nepublikované
                </a>
                    <span>{{ \App\Event::where('published', 0)->get()->count() }}</span>
            </li>
        @endcan
    </ul>

</div>

