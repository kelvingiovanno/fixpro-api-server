@extends('layouts.app')

@section('QR Code Generator', 'Home')

@section('content')

        <div class="h-[825px] flex justify-center items-center">

            <div class="bg-white shadow-lg rounded-lg p-10 text-center w-96 ">
                <h1 class="text-2xl font-bold text-gray-800 mb-4">Area Join Code</h1>
        
                <!-- Display QR Code -->
                <div class="border p-4 rounded-lg bg-gray-50 inline-block">
                    <img src="{{ route('qrcode.show') }}" alt="QR Code" class="w-40 h-40 mx-auto">
                </div>
                <p class="text-sm text-gray-600 mt-2">Referral Code</p>
                <p class="text-xl font-bold text-blue-500 ">{{ $referral_code }}</p>

                <p class="text-gray-600 mt-4 w-48 mx-auto">Scan this QR code through your FixPro Mobile.</p>
        
                <!-- Generate New QR Code Button -->
                <a href="/" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                    Refresh QR Code
                </a>
            </div>
    
        </div>
    
@endsection
