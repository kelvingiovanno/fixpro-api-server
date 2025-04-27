@extends('layouts.app')

@section('Dynamic Form', 'About')

@section('content')

    <div class="h-[847px] flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
            <h3 class="text-lg font-semibold text-gray-700">Select Options</h3>
    
            <form action="{{ route('user-setting.submit') }}" method="POST" class="space-y-4">
                @csrf  
    
                <!-- Checkboxes -->
                <div class="space-y-2">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="email" value="1" class="w-5 h-5 text-blue-600 border-gray-300 rounded">
                        <span>Email</span>
                    </label>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="phone_number" value="1" class="w-5 h-5 text-blue-600 border-gray-300 rounded">
                        <span>Phone Number</span>
                    </label>
                </div>
    
                <!-- Custom Fields -->
                <h3 class="text-lg font-semibold text-gray-700">Custom Fields</h3>
                <div id="custom-fields" class="space-y-2"></div>
    
                <button type="button" onclick="addCustomField()" 
                    class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-2 rounded transition">
                    + Add Custom Field
                </button>
    
                <!-- Submit Button -->
                <button type="submit" 
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 rounded transition">
                    Submit
                </button>
    
            </form>
        </div>        
    </div>

    <script>
        function addCustomField() {
            let container = document.getElementById("custom-fields");

            let div = document.createElement("div");
            div.classList.add("custom-field", "flex", "items-center", "space-x-2");

            let input = document.createElement("input");
            input.type = "text";
            input.name = "custom[]";
            input.placeholder = "Enter value";
            input.classList.add("flex-1", "p-2", "border", "border-gray-300", "rounded");

            let removeButton = document.createElement("button");
            removeButton.type = "button";
            removeButton.textContent = "✖";
            removeButton.classList.add("bg-red-500", "hover:bg-red-600", "text-white", "p-2", "rounded", "transition");
            removeButton.onclick = function() {
                container.removeChild(div);
            };

            div.appendChild(input);
            div.appendChild(removeButton);
            container.appendChild(div);
        }
    </script>
@endsection
