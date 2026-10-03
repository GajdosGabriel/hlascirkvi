{{-- <input class="text-gray-700 p-1 border-2 border-gray-700 rounded-sm md:w-3/4"
            placeholder="Search by name, email ..." type="text"> --}}

<x-dashboard.table label="Registrovaní používatelia">
    <thead class="bg-gray-500 text-white">
        <tr>
            @php
                // Klik na hlavičku radí podľa stĺpca (UserFilters::SORTS), ďalší klik
                // obráti smer. Dátumy začínajú od najnovších, texty od A.
                // Predvolene je výpis od najnovších registrácií.
                $columns = [
                    'id' => ['Id', false],
                    'name' => ['Názov', false],
                    'status' => ['Stav', false],
                    'created' => ['Registrácia', true],
                    'login' => ['Posledné prihlásenie', true],
                    'via' => ['Spôsob', false],
                ];
                $currentSort = (string) request('sort');
            @endphp
            @foreach ($columns as $key => [$label, $descFirst])
                @php
                    $asc = $currentSort === $key;
                    $desc = $currentSort === '-' . $key;
                    $next = $asc ? '-' . $key : ($desc ? $key : ($descFirst ? '-' . $key : $key));
                @endphp
                <th aria-sort="{{ $asc ? 'ascending' : ($desc ? 'descending' : 'none') }}">
                    <a href="{{ request()->fullUrlWithQuery(['sort' => $next, 'page' => null]) }}"
                       class="inline-flex items-center gap-1 whitespace-nowrap hover:underline" style="color: inherit">
                        {{ $label }}
                        <i class="ph {{ $asc ? 'ph-caret-up' : ($desc ? 'ph-caret-down' : 'ph-caret-up-down opacity-50') }}" aria-hidden="true"></i>
                    </a>
                </th>
            @endforeach
            <th>Akcia</th>
        </tr>
    </thead>

    <tbody>
        @forelse($users as $user)
            <tr class="border-2 border-gray-300">
                <td class="td">{{ $user->id }} </td>
                <td class="td">
                    <a href="{{ route('admin.user.show', $user->id) }}" class="font-semibold hover:underline">{{ $user->adminName() }}</a>
                    <div class="text-sm text-gray-500 break-all">{{ $user->email }}</div>
                </td>
                <td class="text-center">
                    <x-dashboard.status-badge :badge="$user->accountBadge()" />
                </td>
                <td class="text-sm">{{ $user->created_at->diffForHumans() }}</td>
                <td class="text-sm" title="{{ $user->last_login_at?->format('d.m.Y H:i:s') }}">
                    @if ($user->last_login_at)
                        {{ $user->last_login_at->diffForHumans() }}
                        @if ($user->last_login_ip)
                            <div class="text-xs text-gray-500">{{ $user->last_login_ip }}</div>
                        @endif
                    @else
                        Nikdy
                    @endif
                </td>
                <td class="text-sm">{{ $user->last_login_via_label ?? '—' }}</td>
                <td class="td">
                    <dropdown-slot label="Spravovať používateľa">
                        <a href="{{ route('admin.user.show', $user->id) }}">
                            <i class="ph ph-eye" aria-hidden="true"></i> Zobraziť
                        </a>
                        <a href="{{ route('admin.user.edit', [$user->id]) }}">
                            <i class="ph ph-pencil-simple" aria-hidden="true"></i> Upraviť
                        </a>
                    </dropdown-slot>
                </td>
            </tr>
        @empty
            <tr><td colspan="7"><x-dashboard.empty>Bez záznamu</x-dashboard.empty></td></tr>
        @endforelse
    </tbody>
</x-dashboard.table>
