<table class="table-auto border-2 border-gray-400 rounded-md w-full">
    <thead class="bg-gray-500 text-white">
        <tr>
            <th>Id</th>
            <th>Názov</th>
            <th>Adresa</th>
            <th>Mesto</th>
            <th>Detaily</th>
            <th>Tagy</th>
            <th>Admin</th>
            <th>Stav</th>
            <th>Akcia</th>
        </tr>
    </thead>

    <tbody class="">
        @forelse($organizations as $organization)
            <tr
                class="border-2 border-gray-300  hover:bg-gray-100
        @if ($organization->id == auth()->user()->org_id)
        bg-gray-300
        @endif ">
                <td class="px-2">{{ $organization->id }}</td>
                <td class="">
                    <a href="{{ route('user.organization.show', [auth()->user()->id, $organization->id]) }}"
                        title="{{ $organization->title }}">
                        {{ \Illuminate\Support\Str::limit($organization->title, 30) }}
                    </a>
                </td>
                <td>{{ $organization->street }}
                </td>
                <td class="whitespace-nowrap ">{{ $organization->village->fullname }}</td>
                <td>
                    @foreach ($organization->updaters as $updater)
                        @if ($updater->type == 'dayOfWeek')
                            <span>Akt.: {{ $updater->title }}</span>
                        @endif
                    @endforeach
                </td>
                <td>
                    @forelse($organization->updaters as $updater)

                        @if ($updater->slug == 'front-user')
                            <i title="{{ $updater->title }}" style="color: black" class="fas fa-user"></i>
                        @endif

                        @if ($updater->slug == 'catholic')
                            <i title="{{ $updater->title }}" class="fab fa-korvue"></i>
                        @endif

                        @if ($updater->slug == 'evangelical')
                            <i title="{{ $updater->title }}" class="fab fa-product-hunt"></i>
                        @endif

                        @if ($updater->slug == 'zive-vysielanie')
                            <i title="{{ $updater->title }}" style="color: black" class="fas fa-church"></i>
                        @endif

                        @if ($updater->slug == 'vzdelavanie')
                            <i title="{{ $updater->title }}" style="color: black" class="fas fa-graduation-cap"></i>
                        @endif
                    @empty
                        nič
                    @endforelse

                    {{-- @foreach ($organization->updaters as $updater) --}}
                    {{-- <span style="font-size: 70%">{{ $updater->title }}</span> --}}
                    {{-- @endforeach --}}
                </td>

                <td>
                    @foreach ($organization->users as $user)
                        <a>
                            {{ $user->last_name }}
                        </a>
                    @endforeach
                </td>
                <td>

                    <form action="{{ route('user.update', auth()->id()) }}" method="post">
                        @method('PUT') @csrf
                        <input type="hidden" name="org_id" value="{{ $organization->id }}" />
                        <button
                            class="px-2 text-xs bg-blue-500 text-gray-100 rounded border-2 border-blue-700 hover:bg-blue-600">
                            Prepnúť na kanál
                        </button>
                    </form>


                    @if (!$organization->published)
                        <div
                            class="px-2 text-xs bg-red-500 text-gray-100 rounded border-2 border-red-700 hover:bg-red-600">
                            Nepublikované
                        </div>
                    @endif


                </td>
                <td class="px-2">
                    <a href="{{ route('user.organization.edit', [$user->id, $organization->id]) }}">
                        <i title="Upraviť" class="fas fa-edit"></i>
                    </a>

                    @can('admin|superadmin')

                    @endcan
                </td>
            </tr>
        @empty
            <table>
                <thead>
                    <tr>Bez záznamu</tr>
                </thead>
            </table>
        @endforelse
    </tbody>
</table>
