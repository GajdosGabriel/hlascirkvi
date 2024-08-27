@extends('layouts.app')

@section('content')
    <x-pages.dashboard>

        <x-slot name="title">
            Kanál {{ $organization->title }}
        </x-slot>

        <x-slot name="title_right">

        </x-slot>


        <x-slot name="page">

            <div class="flex">

                <div class="mb-7 w-1/3 p-6">
                    <x-organization.statistic />
                </div>

                <div class="mb-7 w-1/3 p-6">
                    <h3 class="font-semibold">Prihlásiť sa do kanálu</h3>
                    <form action="{{ route('admin.user.update', auth()->id()) }}" method="post" class="mr-4 mb-4">
                        @method('PUT') @csrf
                        <input type="hidden" name="org_id" value="{{ $organization->id }}" />
                        <button
                            class="px-2 bg-blue-700 text-gray-100 rounded border-2 border-blue-900 hover:bg-blue-600">Nastaviť
                            {{ $organization->title }}
                        </button>
                    </form>
                </div>


            </div>
        </x-slot>
        </x-pages.admin>
    @endsection
