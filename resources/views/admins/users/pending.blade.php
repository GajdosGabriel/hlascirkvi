@php
    $seo = ['title' => 'Čakajúce účty · Administrácia', 'noindex' => true];
@endphp
@extends('layouts.admin')
@section('content')
<x-pages.admin>
    <x-slot name="title">Čakajúce účty</x-slot>
    <x-slot name="lead">Tieto adresy nie sú overené. Účet sa aktivuje až po potvrdení adresy. Obsah starších účtov je zachovaný.</x-slot>
    <x-slot name="title_right">
        <a class="ar-btn ar-btn--quiet" href="{{ route('admin.user.index') }}"><i class="ph ph-users" aria-hidden="true"></i> Všetci užívatelia</a>
    </x-slot>
    <x-slot name="page">
        <x-dashboard.table label="Čakajúce účty">
            <thead><tr><th>E-mail</th><th>Vytvorené</th><th>Stav</th></tr></thead>
            <tbody>
            @forelse ($pending as $row)
                @php($createdAt = \Illuminate\Support\Carbon::parse($row->created_at))
                <tr><td>{{ $row->email }}</td><td title="{{ $createdAt->format('d.m.Y H:i:s') }}">{{ $createdAt->diffForHumans() }}</td><td>{{ $row->kind === 'system' ? 'Archív technického účtu' : 'Čaká na potvrdenie e-mailu' }}</td></tr>
            @empty
                <tr><td colspan="3"><x-dashboard.empty>Žiadne čakajúce účty.</x-dashboard.empty></td></tr>
            @endforelse
            </tbody>
        </x-dashboard.table>
        @if ($pending->hasPages())
            <div class="mt-8">{{ $pending->links() }}</div>
        @endif
    </x-slot>
</x-pages.admin>
@endsection
