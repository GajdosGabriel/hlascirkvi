{{-- Riadok článku v správe kanála. Očakáva: $post, $canal. --}}
@php
    // Prenosy prídu z YouTube s nulovou dĺžkou; „0:00“ na náhľade nič nehovorí.
    $duration = $post->video_duration === '0:00' ? null : $post->video_duration;
    $url = route('post.show', [$post->id, $post->slug]);
@endphp

<article class="ar-item">

    <a href="{{ $url }}" class="ar-item__thumb" title="{{ $post->title }}">
        @include('partials.thumb', ['model' => $post, 'alt' => $post->title, 'class' => ''])

        @if ($duration)
            <span class="ar-item__time">{{ $duration }}</span>
        @endif
    </a>

    <div class="ar-item__body">
        <a href="{{ $url }}" class="ar-item__title ar-clamp-2">{{ $post->title }}</a>

        <div class="ar-item__meta">
            @if ($showOrganization ?? false)
                {{-- /dashboard/posts ukazuje vždy aktívny kanál prihláseného,
                     preto odkaz na verejný profil kanála. --}}
                <a class="ar-link" href="{{ route('organizations.show', $post->organization_id) }}">{{ $post->organization->title }}</a>
            @endif
            <time datetime="{{ $post->created_at->toIso8601String() }}">
                {{ $post->created_at->locale('sk')->isoFormat('D. M. YYYY') }}
            </time>

            <span><i class="far fa-eye"></i>{{ number_format((int) $post->count_view, 0, ',', ' ') }}</span>
            <span><i class="far fa-comment"></i>{{ $post->comments_count }}</span>

            @if ($post->favorites->isNotEmpty())
                <span><i class="fas fa-star"></i>{{ $post->favorites->count() }}</span>
            @endif
        </div>

        <div class="ar-item__tags">
            @if ($post->deleted_at)
                <span class="ar-badge ar-badge--count">V koši</span>
            @elseif (! $post->published)
                <span class="ar-badge ar-badge--warn">Čaká v bufferi</span>
            @endif

            @if ($post->video_available !== null && ! $post->video_available)
                <span class="ar-badge ar-badge--warn">Video nedostupné</span>
            @endif

            @foreach ($post->updaters as $updater)
                <span class="ar-chip">{{ $updater->title }}</span>
            @endforeach
        </div>
    </div>

    @can('update', $post)
        <div class="ar-item__actions">
            <dropdown-slot>
                @if (! $post->deleted_at)
                    <a href="{{ route('profile.posts.edit', $post->id) }}"
                       class="ar-act">
                        <i class="fas fa-pen text-[.7rem]"></i> Upraviť
                    </a>

                    {{-- Odopne štítky aktualizátorov, čím sa článok vráti do buffera
                         (App\Http\Controllers\Api\PostSupportController). --}}
                    @if ($post->updaters->isNotEmpty())
                        <form action="{{ route('postSupport.update', [$post->id]) }}" method="post">
                            @csrf @method('PUT')
                            <button type="submit" class="ar-act">
                                <i class="fas fa-inbox text-[.7rem]"></i> Do buffera
                            </button>
                        </form>
                    @endif
                @endif

                <form action="{{ route('profile.posts.destroy', $post->id) }}"
                      method="post">
                    @csrf @method('DELETE')

                    @if ($post->deleted_at)
                        <button type="submit" class="ar-act ar-act--ok">
                            <i class="fas fa-undo text-[.7rem]"></i> Obnoviť
                        </button>
                    @else
                        <button type="submit" class="ar-act ar-act--danger">
                            <i class="far fa-trash-alt text-[.7rem]"></i> Zmazať
                        </button>
                    @endif
                </form>

            </dropdown-slot>
        </div>
    @endcan
</article>
