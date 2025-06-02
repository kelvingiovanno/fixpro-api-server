@extends('layouts.app')

@section('title', 'Setting Calendar - ProFix')

@section('content')
<div class="max-w-4xl min-h-[700px] mx-auto p-6 bg-white my-5 flex space-x-6">
    @include('settings.layout.setting-sidebar')

    <section class="w-3/4">
        <h2 class="text-2xl font-bold mb-4">Set Up Google Calendar</h2>
        <p class="text-sm text-gray-700">
            To integrate Google Calendar, follow the steps below to generate and share your Google API credentials.
        </p>

        <div class="mt-6 text-sm text-gray-800 space-y-3 w-96">
            <h3 class="text-base font-semibold text-gray-900">How to Set Up Google Calendar</h3>
            <ol class="list-decimal list-inside space-y-1 text-gray-700">
                <li>
                    Go to the 
                    <a href="https://console.cloud.google.com/" target="_blank" class="text-blue-600 underline">
                        Google Cloud Console
                    </a>.
                </li>
                <li>
                    Create a new project or select an existing one.
                </li>
                <li>
                    In the navigation menu, go to <strong>APIs & Services > Library</strong>, then search for and enable:
                    <ul class="list-disc list-inside ml-4">
                        <li>Google Calendar API</li>
                        <li>Google People API (optional, for user details)</li>
                    </ul>
                </li>
                <li>
                    Go to <strong>APIs & Services > Credentials</strong> and click <strong>+ Create Credentials</strong> → <strong>OAuth 2.0 Client IDs</strong>.
                </li>
                <li>
                    Configure your OAuth consent screen and then fill in:
                    <ul class="list-disc list-inside ml-4">
                        <li><strong>Authorized redirect URI:</strong> Use the same URI you enter in the “Redirect URI” field below (e.g. <code>https://yourapp.com/oauth2callback</code>).</li>
                    </ul>
                </li>
                <li>
                    After creating the credentials, you’ll receive a <strong>Client ID</strong> and <strong>Client Secret</strong>. Paste them in the form fields below.
                </li>
                <li>
                    Submit the form. You’ll be redirected to log in with your Google account and grant calendar access.
                </li>
            </ol>
            <p class="text-yellow-700 mt-2">
                Note: Make sure your redirect URI matches exactly in both your Google Console and your app settings.
            </p>
        </div>

        {{-- GOOGLE CREDENTIALS FORM --}}
        <form action="{{ route('settings.calendar.submit') }}" method="POST" class="pl-3 space-y-4 mt-8">
            @csrf

            <div class="flex flex-col gap-1">
                <label class="text-xs text-gray-700" for="google-client-id">Google Client ID</label>
                <input type="text" id="google-client-id" name="google_client_id" placeholder="Enter your Google Client ID" class="text-sm p-2 border border-gray-300 rounded w-96"  />
                @error('google_client_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
                
            <div class="flex flex-col gap-1">
                <label class="text-xs text-gray-700" for="google-client-secret">Google Client Secret</label>
                <input type="text" id="google-client-secret" name="google_client_secret" placeholder="Enter your Google Client Secret" class="text-sm p-2 border border-gray-300 rounded w-96"  />
                @error('google_client_secret')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs text-gray-700" for="google-callback">Redirect URI (Callback)</label>
                <input type="text" id="google-callback" name="google_callback" placeholder="e.g. https://yourapp.com/oauth2callback" class="text-sm p-2 border border-gray-300 rounded w-96"  />
                @error('google_callback')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <p class="mt-4  text-sm">After submitting the form, you will be redirected to log in with your Google account.</p>

            <button type="submit" class="mt-6 w-36 bg-blue-500 hover:bg-blue-600 text-sm text-white font-medium py-2 rounded transition">
                Submit
            </button>
        </form>

    </section>
</div>

@if(session('success'))
    <div id="toast-success" class="absolute right-5 bottom-5 flex items-center w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow-sm opacity-0 transition-all duration-500 ease-in-out" role="alert">
        <div class="inline-flex items-center justify-center shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg">
            <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
            </svg>
            <span class="sr-only">Check icon</span>
        </div>
        <div class="ms-3 text-sm font-normal">
            {{ session('success') }}
        </div>
        <button type="button" onclick="document.getElementById('toast-success').remove()" class="ms-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg  p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8" aria-label="Close">
            <span class="sr-only">Close</span>
            <svg class="w-3 h-3 " fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
        </button>
    </div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toast = document.getElementById('toast-success');
        if (toast) {

            setTimeout(() => {
                toast.classList.remove('opacity-0');
                toast.classList.add('opacity-100');
                toast.classList.add('-translate-y-4');
            }, 100);

            setTimeout(() => {
                toast.classList.remove('opacity-100');
                toast.classList.add('translate-y-0');
                toast.classList.add('opacity-0');
                
                setTimeout(() => toast.remove(), 500);
            }, 3000); 
        }
    });
</script>
@endsection
