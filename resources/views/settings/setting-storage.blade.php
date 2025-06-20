@extends('layouts.app')

@section('title', 'Setting Storage - ProFix')

@section('content')
<div class="max-w-4xl min-h-[700px] mx-auto p-6 bg-white my-5 flex space-x-6">
    @include('settings.layout.setting-sidebar')
    <form action="{{ route('settings.storage.submit') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <section class="w-full">
            <h1 class="text-2xl font-bold mb-4">Setting Storage</h1>
            
            <div class="flex flex-col gap-1 my-2">
                <label class="text-xs bg-white text-gray-700">Storage Type</label>
                <select id="storage-type" name="storage_type" class="text-sm p-2 border border-gray-300 rounded w-96">
                    <option value="CLOUD" {{ old('storage_type') == 'CLOUD' ? 'selected' : '' }}>Google Cloud</option>
                    <option value="LOCAL" {{ old('storage_type', 'LOCAL') == 'LOCAL' ? 'selected' : '' }}>Local</option>
                </select>
            </div>



            <!-- Your Cloud Credentials Section -->
            <div id="cloud-credentials" class="space-y-4 mt-4 hidden">
                <div id="cloud-intro" class="text-sm text-gray-800 space-y-2 w-96">
                    <h2 class="text-base font-semibold text-gray-900">Connecting to Google Cloud</h2>
                    <p>To upload files to your Google Cloud Storage, you need to connect your Google Cloud account and provide the following:</p>
                    <ul class="list-disc list-inside">
                        <li>Your <strong>Bucket Name</strong> from Google Cloud Storage</li>
                        <li>A valid <strong>service account key</strong> with Storage permissions (JSON file)</li>
                    </ul>
                    <p>If you haven’t set this up yet:</p>
                    <ol class="list-decimal list-inside text-gray-700">
                        <li>Go to <a href="https://console.cloud.google.com/" class="text-blue-600 underline" target="_blank">Google Cloud Console</a></li>
                        <li>Create a project (or select an existing one)</li>
                        <li>Enable the “Cloud Storage” API</li>
                        <li>Create a service account with “Storage Admin” role</li>
                        <li>Download the JSON key file (you’ll use it for authentication)</li>
                        <li>Create a storage bucket or use an existing one</li>
                    </ol>
                    <p class="text-yellow-700">Note: Never share your JSON key publicly. Keep it safe!</p>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs text-gray-700">Bucket Name</label>
                    <input type="text" id="google-client-id" name="bucket_name" placeholder="Enter your Google Client ID" class="text-sm p-2 border border-gray-300 rounded w-96" />
                    @error('bucket_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex flex-col gap-1 mt-2">
                    <label class="text-xs text-gray-700">Upload json key</label>
                    <input name="gcs_key_file" class="block file:bg-gray-200 file:rounded-l-lg file:p-2 file:mr-4 file:border-0 w-96 text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-white focus:outline-none" type="file">
                    @error('gcs_key_file')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>


            {{-- Local Storage Path Section --}}
            <div id="local-path" class="flex flex-col gap-2 mt-4 text-sm text-gray-800">
                <p class="w-96">
                    This is the path where uploaded files will be saved on your server. <br />
                    <span class="text-gray-600">By default, files are stored in the server's public directory at <code>/storage/public</code>.</span>
                </p>

                <div class="flex flex-col gap-1 mt-3">
                    <label class="text-xs text-gray-700" for="local-path-input">Local Storage Path</label>
                    <input type="text" id="local-path-input" name="local_storage_path" placeholder="/var/www/storage" class="text-sm p-2 border border-gray-300 rounded w-96" />
                    @error('local_storage_path')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <p class="text-gray-600 mt-1">
                        If you want to change the location, enter the full path here.
                </p>
            </div>
                
            <button type="submit" class="mt-24 w-36 bg-blue-500 hover:bg-blue-600 text-sm text-white font-medium py-2 rounded transition"> Submit</button>
        </section>
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

{{-- JavaScript to handle show/hide --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('storage-type');
        const cloudFields = document.getElementById('cloud-credentials');
        const localPath = document.getElementById('local-path');

        function toggleStorageInputs() {
            if (select.value === 'CLOUD') {
                cloudFields.classList.remove('hidden');
                localPath.classList.add('hidden');
            } else {
                cloudFields.classList.add('hidden');
                localPath.classList.remove('hidden');
            }
        }

        // Initial load
        toggleStorageInputs();

        // Listen for change
        select.addEventListener('change', toggleStorageInputs);
    });

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
