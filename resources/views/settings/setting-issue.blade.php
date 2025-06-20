@extends('layouts.app')

@section('title', 'Setting issue - ProFix')

@section('content')
<div class="max-w-4xl min-h-[700px] mx-auto p-6 bg-white my-5 flex space-x-6">
    @include('settings.layout.setting-sidebar')

    <form action="{{ route('settings.issue.submit')}}" method="POST">
        @csrf

        <section class="w-3/4">
            
            <h2 class="text-2xl font-bold mb-4">Set Up Issue Type</h2>
            <p class="text-sm text-gray-700">
                Settings for area.
            </p>
            
            <h3 class="text-lg font-normal mb-2 mt-7">Customize Issue Fields</h3>
            <p class="text-sm text-gray-600 mb-4">
                Add custom issue type.
            </p>
            
            <div id="custom-fields-log-type" class="space-y-2 w-96 mb-2">
                <div class="flex items-center space-x-2">
                </div>
            </div>
            
            <button type="button" onclick="addCustomFieldLogType()" class="text-sm border-2 border-solid py-2 px-2 rounded-md hover:bg-gray-100">
                + Add Custom Issue
            </button>

            @error('issue_types')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            @error('issue_types.*')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            @error('sla_duration_hour')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            @error('sla_duration_hour.*')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

        </section>
        <button type="submit" class="mt-24 w-36 bg-blue-500 hover:bg-blue-600 text-sm text-white font-medium py-2 rounded transition"> Submit</button>
    </form>    
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
    function addCustomFieldLogType() {
        let container = document.getElementById("custom-fields-log-type");

        let div = document.createElement("div");
        div.classList.add("flex", "items-center", "space-x-2");

        let issueInput = document.createElement("input");
        issueInput.type = "text";
        issueInput.name = "issue_types[]";
        issueInput.placeholder = "Enter name of issue";
        issueInput.classList.add("flex-1", "p-2", "border", "border-gray-300", "rounded", 'text-sm');

        let slaInput = document.createElement("input");
        slaInput.type = "number";
        slaInput.name = "sla_duration_hour[]";
        slaInput.placeholder = "Enter SLA Duration (hour)";
        slaInput.classList.add("flex-1", "p-2", "border", "border-gray-300", "rounded", 'text-sm');

        let removeButton = document.createElement("button");
        removeButton.type = "button";
        removeButton.classList.add("text-white", "p-2", "rounded");
        removeButton.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-black" viewBox="0 0 448 512" fill="currentColor">
                <path d="M135.2 17.7C140.6 6.8 151.7 0 163.8 0L284.2 0c12.1 0 23.2 6.8 28.6 17.7L320 32l96 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 96C14.3 96 0 81.7 0 64S14.3 32 32 32l96 0 7.2-14.3zM32 128l384 0 0 320c0 35.3-28.7 64-64 64L96 512c-35.3 0-64-28.7-64-64l0-320zm96 64c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16zm96 0c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16zm96 0c-8.8 0-16 7.2-16 16l0 224c0 8.8 7.2 16 16 16s16-7.2 16-16l0-224c0-8.8-7.2-16-16-16z"/>
            </svg>`;
        removeButton.onclick = function () {
            removeField(removeButton);
        };

        div.appendChild(issueInput);
        div.appendChild(slaInput);
        div.appendChild(removeButton);
        container.appendChild(div);
    }

    function removeField(button) {
        let container = button.parentElement;
        container.remove();
    }

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
