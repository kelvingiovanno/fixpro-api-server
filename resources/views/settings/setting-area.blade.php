@extends('layouts.app')

@section('title', 'Setting Area - ProFix')

@section('content')
<div class="max-w-4xl min-h-[700px] mx-auto p-6 bg-white my-5 flex space-x-6">
    @include('settings.layout.setting-sidebar')

    <section class="w-3/4">
        <div class="mb-5">
            <h2 class="text-2xl font-bold mb-4">Set Up Area</h2>
            <p class="text-sm text-gray-700">
                Settings for area.
            </p>
        </div>
        
        <form action="{{ route('settings.area.submit') }}" method="POST">
            @csrf
            
            <div class="flex flex-col gap-1 my-2">
                <p class="text-xs bg-white text-gray-700">Area Name</p>
                <input type="text" name="area_name" placeholder="Enter area name" value="{{ old('area_name', session('area_name', App\Models\SystemSetting::get('area_name'))) }}" class="text-sm p-2 border border-gray-300 outline-none rounded w-96">   
                @error('area_name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            
            <div class="flex flex-col gap-1 mt-4">
                <p class="text-xs bg-white text-gray-700">Join Policy</p>
                <select name="join_policy" class="text-sm p-2 border rounded w-96">
                    @php
                        $selectedJoinPolicy = old('join_policy', session('join_policy', App\Models\SystemSetting::get('area_join_policy')));
                    @endphp

                    <option value="OPEN" {{ $selectedJoinPolicy == 'OPEN' ? 'selected' : '' }}>Open</option>
                    <option value="APROVAL_NEEDED" {{ $selectedJoinPolicy == 'APROVAL_NEEDED' ? 'selected' : '' }}>Approval Needed</option>
                    <option value="CLOSED" {{ $selectedJoinPolicy == 'CLOSED' ? 'selected' : '' }}>Closed</option>
                </select>
                @error('join_policy')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        
            <div class="text-xs bg-gray-100 mt-4 w-96 rounded-md p-2">
                <div class=" space-y-1">    
                    <p><strong>Open:</strong> Anyone can join as member.</p>  
                    <p><strong>Approval Needed:</strong> Management approval required.</p>
                    <p><strong>Closed:</strong> Nobody can apply.</p>
                </div>
            </div>

            <button type="submit" class="mt-6 w-36 bg-blue-500 hover:bg-blue-600 text-sm text-white font-medium py-2 rounded transition">Submit</button>

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
