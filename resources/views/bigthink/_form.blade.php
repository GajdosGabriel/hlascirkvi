@if ($post->video_id)

    <big-thing inline-template>

        <div class="w-full">

            <h4 @click="showFormToggle" style="padding-right: 1rem; margin: 1rem 0rem">Veľké myšlienky z videa <i
                    class="far fa-comment-dots"></i>
                <span style="font-size: 70%; cursor: pointer">pridať myšlienku</span>
            </h4>

            @forelse($post->bigThinks as $think)
                <div class="mb-3 hover:bg-gray-100 p-2 shadow-md flex justify-between">
                    <div class="flex items-center">
                        <div><span style="margin-right: .7rem;">{{ $loop->index + 1 }}</span> {{ $think->body }}
                        </div>
                    </div>
                    <div class="text-sm ml-4">{{ $think->organization->anonymizer() }}</div>
                </div>
                {{-- <p><span><i class="far fa-comments"></i></span>{{ $think->body }}</p> --}}
                {{-- <p><span class="big-thing">{{ $loop->index + 1 }}</span>{{ $think->body }}</p> --}}

            @empty
                {{-- <li>Toto video zatiaľ nikoho neinšpirovalo.</li> --}}
            @endforelse


            <form v-if="showForm" method="post" action="{{ route('profile.post.think.store', [$post->id]) }}"
                style="margin-bottom: 4rem">
                @csrf @method('POST')

                <div class="form-group">
                    <textarea class="w-full p-2 border-2 border-gray-400 rounded-md" name="body" rows="2" required
                        placeholder="Napíšte myšlienku ktorá Vás oslovila z videa ...">
                        {{ old('body') }}
                    </textarea>
                </div>

                @if (auth()->guest())
                    <div class="form-group">
                        <label>Som človek 7 plus 3 = </label>
                        <input type="number" name="iamHuman" class="form-control" placeholder="Zadajte výsledok"
                            style="opacity: .85;color: black; width: 25%" required>
                        <button type="submit" class="btn btn-small" style="float: right">Uložiť</button>
                    </div>
                @else
                    <div class="form-group relative">
                        <button type="submit" class="btn btn-small absolute right-0">Uložiť</button>
                    </div>
                @endif

            </form>

        </div>

    </big-thing>

@endif
