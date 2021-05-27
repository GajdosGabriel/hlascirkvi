@extends('layouts.app')


@section('content')

<div class="grid grid-cols-12 gap-6  ">

    <div class="grid col-span-2  min-h-screen">
        <div class="flex flex-col bg-gray-200">

            @include('admins._profil-menu')

        </div>
    </div>


    <div class="grid col-span-10 p-5">


                <h3 class="page_title">Registrovaný užívatelia</h3>
                <input class="text-gray-700 p-1 border-2 border-gray-700 rounded-sm md:w-3/4"
                    placeholder="Search by name, email ..." type="text">

                    <table class="table-auto border-2 border-gray-400 rounded-md w-full">
                        <thead class="bg-gray-500 text-white">
                        <tr>
                            <th>Por./Id</th>
                            <th>Názov</th>
                            <th>Email</th>
                            <th>5. pád</th>
                            <th>Vypnuté</th>
                            <th>Overené</th>
                            <th>Denominácia</th>
                            <th>Registrácia</th>
                            <th>Akcia</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($users as $user)
                        <tr class="border-2 border-gray-300">
                                <td>{{ $users->count() - $loop->iteration + 1 }} / {{ $user->id }} </td>
                                <td class="whitespace-no-wrap">
                                    <a href="{{ route('organizations.edit', [$user->id, $user->slug]) }}">
                                        {{ $user->first_name }} {{ $user->last_name }}
                                    </a>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->vocative }}</td>
                                <td>{{ $user->disabled }}</td>
                                <td>{{ $user->verified }}</td>
                                <td>{{ $user->set_denomination }}</td>
                                <td>{{ $user->created_at->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('organizations.edit', [$user->id, $user->slug]) }}">
                                        <i title="Upraviť" class="fas fa-edit"></i>
                                    </a>
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

            </div>


        </div>

        <div class="page-aside">


            {{-- <new-organization :user="{{ auth()->user() }}"></new-organization> --}}

        </div>


    </div>


@endsection
