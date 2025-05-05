@extends('layouts.app')

@section('Authenticate with Token', 'Home')

@section('content')
    <div class="h-screen flex justify-center items-center">
        <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-md">
            <h2 class="mb-4 text-xl font-bold text-center">Enter Auth Token</h2>
            
            @if(session('error'))
                <div class="p-2 mb-4 text-red-700 bg-red-100 border border-red-400 rounded">
                    {{ session('error') }}
                </div>
            @endif
    
            <form action="{{ route('auth.login') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-700">Auth Token</label>
                    <input type="text" name="auth_token" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" autocomplete="off" required>
                </div>
    
                <button type="submit" class="w-full px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    Authenticate
                </button>
            </form>
        </div>
    </div>
@endsection
