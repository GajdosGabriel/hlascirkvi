@extends('layouts.admin')
@section('title')
    <title>{{ 'Admin komentáre.' }}</title>
@endsection

@section('content')
    <x-pages.admin>

        <x-slot name="title">
            {{ $title ?? 'Komentáre' }}
        </x-slot>

        <x-slot name="page">
            @php
                $num = fn ($value) => number_format((int) $value, 0, ',', ' ');
                $postUrl = fn ($post) => route('post.show', [$post->id, $post->slug]) . '#komentare';
            @endphp

            <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
                <x-dashboard.metric label="Spolu" :value="$num($summary->total)">bez zmazaných</x-dashboard.metric>
                <x-dashboard.metric label="Za 24 hodín" :value="$num($summary->day)">nové komentáre</x-dashboard.metric>
                <x-dashboard.metric label="Za 7 dní" :value="$num($summary->week)">≈ {{ $num(round($summary->week / 7)) }} denne</x-dashboard.metric>
                <x-dashboard.metric label="Z YouTube" :value="$num($summary->youtube)">stiahnuté pod videami</x-dashboard.metric>
                <x-dashboard.metric label="Odpovede" :value="$num($summary->replies)">v rozhovoroch</x-dashboard.metric>
                <x-dashboard.metric label="Neschválené" :value="$num($summary->unpublished)">čakajú na zverejnenie</x-dashboard.metric>
            </div>

            <nav class="mb-6 flex flex-wrap gap-3" aria-label="Zdroj komentárov">
                @foreach (['users' => 'Registrovaní používatelia', 'youtube' => 'YouTube', 'all' => 'Všetky komentáre'] as $value => $label)
                    <a href="{{ request()->fullUrlWithQuery(['source' => $value, 'page' => null]) }}"
                       class="ar-btn {{ $source === $value ? 'ar-btn--accent' : 'ar-btn--quiet' }}"
                       @if ($source === $value) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>

            @if ($hotPosts->isNotEmpty())
                <x-dashboard.panel title="Najživšie diskusie za 7 dní" flush class="mb-6">
                    @foreach ($hotPosts as $index => $row)
                        <a href="{{ $postUrl($row) }}" class="ar-row">
                            <span class="ar-rank {{ $index === 0 ? 'ar-rank--first' : '' }}">{{ $index + 1 }}</span>
                            <span class="min-w-0 flex-1">
                                <span class="ar-row__title ar-clamp-2">{{ $row->title }}</span>
                                <span class="ar-row__meta block">posledný komentár {{ \Carbon\Carbon::parse($row->last_at)->diffForHumans() }}</span>
                            </span>
                            <span class="ar-row__value"><i class="far fa-comment mr-1"></i>{{ $num($row->recent) }}</span>
                        </a>
                    @endforeach
                </x-dashboard.panel>
            @endif

            <x-dashboard.panel title="Komentáre" flush>
                <x-slot name="note">{{ $num($comments->total()) }} vo výbere</x-slot>

                @forelse ($comments as $comment)
                    @php
                        $post = $posts[$comment->commentable_id] ?? null;
                        $youtube = $comment->fromYoutube();
                        $authorName = $comment->user_name
                            ?: trim(($comment->user->first_name ?? '') . ' ' . ($comment->user->last_name ?? ''))
                            ?: 'Anonym';
                        $authorTotal = $authorCounts[$comment->user_id] ?? null;
                    @endphp

                    <article class="ar-item" data-admin-comment="{{ $comment->id }}">
                        <img class="ar-avatar mt-0.5" src="{{ $comment->user_avatar ?: ($comment->user->avatar ?? null) ?: '/images/avatar.png' }}"
                             alt="" loading="lazy" referrerpolicy="no-referrer"
                             onerror="this.onerror=null;this.src='/images/avatar.png'">

                        <div class="ar-item__body">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                @if (! $youtube && $comment->user_id)
                                    <a href="{{ route('admin.user.edit', $comment->user_id) }}" class="text-sm font-semibold text-[color:var(--ar-ink)] hover:text-[color:var(--ar-accent)]">{{ $authorName }}</a>
                                @else
                                    <strong class="text-sm text-[color:var(--ar-ink)]">{{ $authorName }}</strong>
                                @endif

                                @if ($youtube)
                                    <span class="ar-badge" style="background:#fef2f2;color:#b91c1c"><i class="fab fa-youtube"></i> YouTube</span>
                                @endif
                                @if ($comment->parent_id)
                                    <span class="ar-badge ar-badge--count"><i class="fas fa-reply"></i> odpoveď</span>
                                @endif
                                @if ($comment->published === null)
                                    <span class="ar-badge ar-badge--warn">neschválený</span>
                                @endif
                                @if ($comment->trashed())
                                    <span class="ar-badge ar-badge--warn">zmazaný</span>
                                @endif

                                <span class="text-xs text-gray-400" title="{{ $comment->created_at?->format('j. n. Y H:i') }}">
                                    {{ $comment->created_at?->diffForHumans() }}
                                </span>
                            </div>

                            @if ($comment->parent)
                                <p class="ar-comment__quote">
                                    na {{ $comment->parent->user_name ?: 'komentár' }}:
                                    „{{ \Illuminate\Support\Str::limit($comment->parent->body, 110) }}“
                                </p>
                            @endif

                            @if ($comment->moderation_reason)
                                <p class="text-sm text-red-700">Automaticky skrytý: {{ $comment->moderation_reason }}</p>
                            @endif
                            <p class="ar-comment__body">{{ $comment->body }}</p>

                            @if ($post)
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <a href="{{ $postUrl($post) }}" class="ar-comment__post" target="_blank" rel="noopener" title="{{ $post->title }}">
                                        @if ($post->video_id)
                                            <img src="https://i.ytimg.com/vi/{{ $post->video_id }}/default.jpg" alt="" class="h-5 w-8 flex-none rounded object-cover" loading="lazy">
                                        @else
                                            <i class="far fa-file-alt"></i>
                                        @endif
                                        <span>{{ $post->title }}</span>
                                        <i class="fas fa-external-link-alt text-[10px] text-gray-400"></i>
                                    </a>
                                    @if ($post->deleted_at)
                                        <span class="ar-badge ar-badge--warn">článok je zmazaný</span>
                                    @endif
                                </div>
                            @endif

                            <div class="ar-item__meta">
                                @if ($post?->canal)
                                    <a href="{{ route('organizations.show', [$post->canal_id]) }}" class="hover:text-[color:var(--ar-accent)]"><i class="fas fa-broadcast-tower"></i>{{ $post->canal }}</a>
                                @endif
                                @if ($post)
                                    <span title="Komentáre pod článkom"><i class="far fa-comments"></i>{{ $num($post->comments) }} v diskusii</span>
                                    <span title="Zhliadnutia článku"><i class="far fa-eye"></i>{{ $num($post->count_view) }}</span>
                                @endif
                                @if ($comment->replies_count)
                                    <span><i class="fas fa-reply-all"></i>{{ $num($comment->replies_count) }} {{ $comment->replies_count === 1 ? 'odpoveď' : ($comment->replies_count < 5 ? 'odpovede' : 'odpovedí') }}</span>
                                @endif
                                @if ($comment->favoritesCount)
                                    <span><i class="fas fa-heart text-red-400"></i>{{ $num($comment->favoritesCount) }}</span>
                                @endif
                                @if ($authorTotal > 1)
                                    <span title="Komentáre tohto autora spolu"><i class="far fa-user"></i>{{ $num($authorTotal) }} komentárov od autora</span>
                                @endif
                                <span title="Dĺžka komentára"><i class="fas fa-text-width"></i>{{ $num(mb_strlen($comment->body)) }} znakov</span>
                            </div>
                        </div>

                        @unless ($comment->trashed())
                            <div class="ar-item__actions">
                                <button type="button" class="ar-act ar-act--danger" data-admin-comment-delete="{{ $comment->id }}" title="Zmazať komentár" aria-label="Zmazať komentár">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                            </div>
                        @endunless
                    </article>
                @empty
                    <x-dashboard.empty>Bez komentárov.</x-dashboard.empty>
                @endforelse
            </x-dashboard.panel>

            <div class="md:block flex justify-center my-8">
                {{ $comments->links() }}
            </div>

        </x-slot>
    </x-pages.admin>
@endsection

@push('scripts')
    <script>
        // Mazanie ide cez to isté API ako vo Vue komponente komentára.
        // Počúva sa na document: Vue obsah #app pri pripojení prekreslí.
        document.addEventListener('click', (e) => {
            const button = e.target.closest?.('[data-admin-comment-delete]');
            if (!button || !window.confirm('Skutočne vymazať komentár?')) return;

            const id = button.dataset.adminCommentDelete;
            const row = document.querySelector(`[data-admin-comment="${id}"]`);
            button.disabled = true;

            window.axios.delete('/api/comments/' + id)
                .then(() => {
                    row.style.transition = 'opacity 300ms';
                    row.style.opacity = 0;
                    setTimeout(() => row.remove(), 300);
                })
                .catch(() => {
                    button.disabled = false;
                    window.alert('Komentár sa nepodarilo zmazať.');
                });
        });
    </script>
@endpush
