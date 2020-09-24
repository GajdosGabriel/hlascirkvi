<div style="margin-bottom: 3rem">
    <h5 style="font-weight: bold">Okresy Slovenska</h5>

    <ul>
        <li><a href="{{ route('event.index') }}"> @if(!request()->has('district')) <i style="color: #3b32b3" class="fas fa-check"></i> @endif Všetky regiony</a></li>
    @forelse($districts as $k => $v)
            <li class="level">
                @foreach($v as $item)
                    <a href="?district={{ $item->id  }}">
                        @if(request('district') == $item->id )  <i style="color: #3b32b3" class="fas fa-check"></i> @endif

                        @break
                        @endforeach
                    {{ $k }}
                </a>
                 <span> {{ count($v) }}</span>
            </li>
    @empty
            Žiadne regióny
    @endforelse


        @can('admin')
            <li class="level">
                <a href="?event=unpublished"> @if(request()->has('unpublished')) <i style="color: #3b32b3" class="fas fa-check"></i> @endif
                    Nepublikované
                </a>
                    <span>{{ \App\Event::where('published', 0)->get()->count() }}</span>
            </li>
        @endcan
    </ul>

</div>

