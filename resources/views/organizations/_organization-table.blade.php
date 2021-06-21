<table class="table-auto border-2 border-gray-400 rounded-md w-full">
    <thead class="bg-gray-500 text-white">
    <tr>
        <th style="width: 7%">Id</th>
        <th>Názov</th>
        <th>Adresa</th>
        <th>Región</th>
        <th>Detaily</th>
        <th style="width: 10%">Tagy</th>
        <th>Admin</th>
        <th>Akcia</th>
    </tr>
    </thead>

    <tbody class="">
    @forelse($organizations as $organization)
        <tr class="border-2 border-gray-300
        @if($organization->id == auth()->user()->org_id)
        bg-gray-400
        @endif " >
            <td class="pl-4">{{ $loop->iteration }}</td>
            <td class="">
                <form action="{{ route('users.update', auth()->id() ) }}" method="post">
                    @method('PUT') @csrf
                    <input type="hidden" name="org_id" value="{{ $organization->id }}"/>
                    <button type="submit">{{ $organization->title }}</button>
                </form>
            </td>
            <td>{{ $organization->street }}<br>
                {{ $organization->city }}
                {{--<a href="{{ route('organization.delete', [ $organization->id, $organization->slug]) }}">--}}
                    {{--<i title="Zmazať" class="fas fa-trash-alt"></i>--}}
                {{--</a>--}}

            </td>
            <td class="whitespace-nowrap ">{{ $organization->region->title }}</td>
            <td>
                @foreach($organization->updaters as $updater)
                    @if($updater->type == 'dayOfWeek')
                        <span>Akt.: {{ $updater->title }}</span>
                    @endif
                @endforeach
            </td>
            <td>
                @forelse($organization->updaters as $updater)

                    @if($updater->slug == 'front-user')
                        <i title="{{ $updater->title }}" style="color: black" class="fas fa-user"></i>
                    @endif

                    @if($updater->slug == 'catholic')
                        <i title="{{ $updater->title }}" class="fab fa-korvue"></i>
                    @endif

                    @if($updater->slug == 'evangelical')
                        <i title="{{ $updater->title }}" class="fab fa-product-hunt"></i>
                    @endif

                    @if($updater->slug == 'zive-vysielanie')
                        <i title="{{ $updater->title }}" style="color: black" class="fas fa-church"></i>
                    @endif

                    @if($updater->slug == 'vzdelavanie')
                        <i title="{{ $updater->title }}" style="color: black" class="fas fa-graduation-cap"></i>
                    @endif
                @empty
                    nič
                @endforelse


                {{--@foreach($organization->updaters as $updater)--}}
                {{--<span style="font-size: 70%">{{ $updater->title }}</span>--}}
                {{--@endforeach--}}
            </td>

            <td>
                @foreach($organization->users as $user)
                    <a>
                        {{ $user->last_name }}
                    </a>
                @endforeach
            </td>
            <td class="flex space-x-3 items-center px-2">
                <a href="{{ route('organizations.edit', [ $organization->id ]) }}">
                    <i title="Upraviť" class="fas fa-edit"></i>
                </a>

                @can( 'admin|superadmin')

                @endcan
            </td>
        </tr>
    @empty
        <table><thead><tr>Bez záznamu</tr></thead></table>
    @endforelse
    </tbody>
</table>
