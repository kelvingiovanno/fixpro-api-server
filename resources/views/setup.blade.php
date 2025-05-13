@extends('layouts.app')

@section('title', 'Server Setup Guide')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white my-5">
  
  <form action="{{ route('setup.submit') }}" method="POST">
    @csrf
    <div class="transition duration-300 ease-in-out mb-10">
        
      <div class="mb-5">
        <h2 class="text-2xl font-bold mb-4">Set Up Area</h2>
        <p class="text-sm text-gray-700">
          In this step, you'll define essential settings for area.
        </p>
      </div>
    
      <div class="flex flex-col gap-1 my-2">
        <p class="text-xs bg-white text-gray-700">Area Name</p>
        <input type="text" name="area_name" placeholder="Enter area name" class="text-sm flex-1 p-2 border border-gray-300 rounded w-96">   
      </div>
        
      <div class="flex flex-col gap-1 my-2">
        <p class="text-xs bg-white text-gray-700">Join Policy</p>
        <select name="join_policy" class="text-sm p-2 border border-gray-300 rounded w-96">
          <option value="OPEN">Open</option>
          <option value="APROVAL_NEEDED" selected>Approval Needed</option>
          <option value="CLOSED">Closed</option>
        </select>
      </div>
      
      <div class="text-xs bg-gray-100 mt-4 w-96 rounded-md p-2">
        <div class=" space-y-1">    
          <p><strong>Open:</strong> Anyone can join.</p>
          <p><strong>Approval Needed:</strong> Admin approval required.</p>
          <p><strong>Closed:</strong> Members are added manually.</p>
        </div>
      </div>

    </div>
    

    <div class="transition duration-300 ease-in-out mb-10">
      <h2 class="text-2xl font-bold mb-4">Set Up Member Custom Data</h2>
      <p class="text-sm text-gray-700">
        In this step, you'll define which data fields should be collected for each member, such as email addresses, phone numbers, or other custom information.
        This ensures the system captures all necessary details for user management and communication.
      </p>

      <h3 class="text-lg font-normal mb2 mt-7">Customize Member Data Fields</h3>
      <p class="text-sm text-gray-600 mb-4">
          Add or remove custom fields that you want to collect from members.
      </p>
      
      <div id="custom-fields-user-data" class="space-y-2 w-96 mb-2">  
      </div>
    
      <button type="button" onclick="addCustomFieldUserData()" class="text-sm border-2 border-solid py-2 px-2 rounded-md hover:bg-gray-100">
        + Add Custom Field
      </button>
    </div>

    <div class="transition duration-300 ease-in-out mb-10">
      <h2 class="text-2xl font-bold mb-4">Set Up Issue Type</h2>
      <p class="text-sm text-gray-700">
        In this step, you'll define what information needs to be collected for each issue type
      </p>
    
      <h3 class="text-lg font-normal mb-2 mt-7">Customize Issue Fields</h3>
      <p class="text-sm text-gray-600 mb-4">
        Add or remove custom fields relevant to each issue type.
      </p>
      
      <div id="custom-fields-log-type" class="space-y-2 w-96 mb-2">
        <div class="flex items-center space-x-2">
        </div>
      </div>
    
      <button type="button" onclick="addCustomFieldLogType()" class="text-sm border-2 border-solid py-2 px-2 rounded-md hover:bg-gray-100">
        + Add Custom Field
      </button>
    </div>
    

    <div class="transition duration-300 ease-in-out space-y-4 mb-10">
      <h2 class="text-2xl font-bold mb-4">Set Up Google Calendar</h2>
      <p class="text-sm text-gray-700">
        To integrate Google Calendar, follow the steps below to generate and share your Google API credentials.
      </p>
      
      <div class="pl-3 space-y-4 text-sm text-gray-800">
        <div>
          <p class="font-medium">1. Go to Google Cloud Console</p>
          <p class="pl-3">
            Visit <a href="https://console.cloud.google.com/" target="_blank" class="text-blue-600 underline">console.cloud.google.com</a> and sign in.
          </p>
        </div>
      
        <div>
          <p class="font-medium">2. Create a New Project</p>
          <p class="pl-3">
            Click the project dropdown at the top, then select <strong>New Project</strong>. Name it and click <strong>Create</strong>.
          </p>
        </div>
      
        <div>
          <p class="font-medium">3. Enable the Google Calendar API</p>
          <p class="pl-3">
            Go to <strong>APIs & Services &gt; Library</strong>, search for <strong>Google Calendar API</strong>, and click <strong>Enable</strong>.
          </p>
        </div>
      
        <div>
          <p class="font-medium">4. Configure OAuth Consent Screen</p>
          <p class="pl-3">
            Go to <strong>OAuth consent screen</strong>. Choose <strong>External</strong>, fill in the required fields, and save.
          </p>
        </div>
    
        <div>
          <p class="font-medium">5. Create OAuth Credentials</p>
          <p class="pl-3">
            Go to <strong>Credentials &gt; Create Credentials &gt; OAuth Client ID</strong>. Choose <strong>Web Application</strong>, add a name, and set the redirect URI (e.g., <code>https://yourapp.com/oauth2callback</code>).
          </p>
        </div>
    
        <div>
          <p class="font-medium mt-6">6. Enter Your Google API Credentials</p>
          <p class="pl-3 text-gray-600">
            After creating the OAuth credentials, please enter the following details below:
          </p>
        </div>

        <div class="pl-3 space-y-4 mt-4">
          <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-700" for="google-client-id">Google Client ID</label>
            <input type="text" id="google-client-id" name="google_client_id" placeholder="Enter your Google Client ID" class="text-sm p-2 border border-gray-300 rounded w-96" />
          </div>
            
          <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-700" for="google-client-secret">Google Client Secret</label>
            <input type="text" id="google-client-secret" name="google_client_secret" placeholder="Enter your Google Client Secret" class="text-sm p-2 border border-gray-300 rounded w-96" />
          </div>
    
          <div class="flex flex-col gap-1">
            <label class="text-xs text-gray-700" for="google-callback">Redirect URI (Callback)</label>
            <input type="text" id="google-callback" name="google_callback" placeholder="e.g. https://yourapp.com/oauth2callback" class="text-sm p-2 border border-gray-300 rounded w-96" />
          </div>
        </div>

        
        <div>
          <p class="font-medium mt-6">7. Redirect to Google Login</p>
          <p class="pl-3 text-gray-600">
            After submitting the form, you will be redirected to login with your Google account.
          </p>
        </div>
        

      </div>
    </div>

    @if ($errors->any())
    <div id="error-container" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 w-96">
        <ul class="list-disc pl-5 text-sm">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
    </div>
    @endif
      
    <button type="submit" 
      class="mt-10 w-36 bg-blue-500 hover:bg-blue-600 text-sm text-white font-medium py-2 rounded transition">
      Submit
    </button>
  </form>
</div>

<script src="//unpkg.com/alpinejs" defer></script>

<script>
    function addCustomFieldUserData() {
        let container = document.getElementById("custom-fields-user-data");

        let div = document.createElement("div");
        div.classList.add("flex", "items-center", "space-x-2");

        let input = document.createElement("input");
        input.type = "text";
        input.name = "forms[]";
        input.placeholder = "Enter custom data";
        input.classList.add("flex-1", "p-2", "border", "border-gray-300", "rounded", 'text-sm');

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

        div.appendChild(input);
        div.appendChild(removeButton);
        container.appendChild(div);
    }
  
    function addCustomFieldLogType() {
        let container = document.getElementById("custom-fields-log-type");

        let div = document.createElement("div");
        div.classList.add("flex", "items-center", "space-x-2");

        let input = document.createElement("input");
        input.type = "text";
        input.name = "issue_types[]";
        input.placeholder = "Enter custom issue types";
        input.classList.add("flex-1", "p-2", "border", "border-gray-300", "rounded", 'text-sm');

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

        div.appendChild(input);
        div.appendChild(removeButton);
        container.appendChild(div);
    }

    document.addEventListener('DOMContentLoaded', function() {
            const errorContainer = document.getElementById('error-container');
            if (errorContainer) {
                window.scrollTo({
                    top: errorContainer.offsetTop - 20, 
                    behavior: 'smooth' 
                });
            }
        });

    function removeField(button) {
        let container = button.parentElement;
        container.remove();
    }
</script>
@endsection
