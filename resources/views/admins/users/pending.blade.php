@extends('layouts.admin')
@section('content')
<x-pages.admin>
    <x-slot name="title">Čakajúce účty a registrácie</x-slot>
    <x-slot name="page">
        <a class="ar-btn ar-btn--quiet mb-4" href="{{ route('admin.user.index') }}">Registrovaní používatelia</a>
        <p class="mb-4">Tieto adresy nie sú overené. Účet sa aktivuje až po potvrdení adresy. Obsah starších účtov je zachovaný.</p>
        <x-dashboard.table label="Čakajúce účty">
            <thead><tr><th>E-mail</th><th>UUID</th><th>Vytvorené</th><th>Stav</th></tr></thead>
            <tbody>
            @forelse ($pending as $row)
                <tr><td>{{ $row->email }}</td><td class="text-xs font-mono whitespace-nowrap">{{ $row->uuid ?? '—' }}</td><td>{{ $row->created_at }}</td><td>{{ $row->kind === 'system' ? 'Archív technického účtu' : 'Čaká na potvrdenie e-mailu' }}</td></tr>
            @empty
                <tr><td colspan="4">Žiadne čakajúce účty.</td></tr>
            @endforelse
            </tbody>
        </x-dashboard.table>
        {{ $pending->links() }}
    </x-slot>
</x-pages.admin>
@endsection
