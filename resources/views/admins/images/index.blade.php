@extends('layouts.app')

@section('content')

    <div class="container">

        <div class="page">

            <div class="page-content">

                <div>
                    @if( auth()->user()->hasRole('admin'))
                        <h5>Images panel</h5>
                        <a class="tag" href="{{ route('admin.images.destroy') }}">Images for destroy {{ $images->count() }}</a>
                    @endif
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                    </form>
                </div>


            </div>

            <div class="page-aside">

            </div>


        </div>

    </div>


    @endsection