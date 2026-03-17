@extends('layouts.app')

@section('title','Profile')

@section('content')

<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- PAGE TITLE --}}
        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                Profile Settings
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                Manage your account information and security settings.
            </p>
        </div>


        {{-- UPDATE PROFILE --}}
        <div class="p-6 bg-white shadow rounded-lg">
            @include('profile.partials.update-profile-information-form')
        </div>


        {{-- UPDATE PASSWORD --}}
        <div class="p-6 bg-white shadow rounded-lg">
            @include('profile.partials.update-password-form')
        </div>


        {{-- DELETE ACCOUNT --}}
        <div class="p-6 bg-white shadow rounded-lg">
            @include('profile.partials.delete-user-form')
        </div>


        {{-- BACK TO DASHBOARD --}}
        <div class="text-center pt-2">
            <a href="{{ route('client.dashboard') }}"
               class="text-sm text-blue-600 hover:underline">

                ← Back to Dashboard

            </a>
        </div>

    </div>
</div>

@endsection
