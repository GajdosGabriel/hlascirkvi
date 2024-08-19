@extends('layouts.app')

@section('content')
    <x-pages.dashboard>

        <x-slot name="title">
            Upraviť užívateľa
        </x-slot>

        <x-slot name="title_right">

        </x-slot>


        <x-slot name="page">
            <form method="post" action="{{ route('admin.user.update', [$user->id]) }}">
                @csrf @method('PUT')
                <div class="card-body" style="width: 50%">

                    <div class="form-group">
                        <label for="first_name">Meno</label>
                        <input type="text" name="first_name" id="first_name" value="{{ $user->first_name }}"
                            class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Priezvisko</label>
                        <input type="text" name="last_name" id="last_name" value="{{ $user->last_name }}"
                            class="form-control" required>
                    </div>

                    @can('superadmin')
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="text" name="email" id="email" value="{{ $user->email }}" class="form-control"
                                required>
                        </div>

                        <div class="flex justify-between my-3">
                            <div class="inline">
                                <label>Účet blokovaný</label>
                                <input type="radio" value="1"
                                    @if (isset($user->disabled) and $user->disabled == 1) checked @else checked @endif name="disabled">
                            </div>

                            <div class="inline">
                                <label>Účet aktívny</label>
                                <input type="radio" value="0" @if (isset($user->disabled) and $user->disabled == 0) checked @endif
                                    name="disabled">
                            </div>
                        </div>
                    @endcan

                    <button type="submit" class="btn btn-primary">Uložiť</button>
            </form>
        </x-slot>
        </x-pages.admin>
    @endsection
