<aside class="w-1/4 pr-4 border-r">
    <h2 class="text-lg font-bold ml-4 mb-2">Settings</h2>
    <div class="flex flex-col gap-2"> 
        <a href="{{ route('settings.area') }}">
            <div class="h-11 flex items-center gap-2 rounded-lg px-3 hover:bg-gray-100 {{ request()->routeIs('settings.area') ? 'bg-gray-200' : '' }}">
                
                <svg class="w-6 h-6 text-gray-800 dark:text-black" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h12M6 4v16M6 4H5m13 0v16m0-16h1m-1 16H6m12 0h1M6 20H5M9 7h1v1H9V7Zm5 0h1v1h-1V7Zm-5 4h1v1H9v-1Zm5 0h1v1h-1v-1Zm-3 4h2a1 1 0 0 1 1 1v4h-4v-4a1 1 0 0 1 1-1Z"/>
                </svg>

                Area
            </div>
        </a>

        <a href="{{ route('settings.member') }}">
            <div class="h-11 flex items-center gap-2 rounded-lg px-3 hover:bg-gray-100 {{ request()->routeIs('settings.member') ? 'bg-gray-200' : '' }}">
                <svg class="w-6 h-6 text-gray-800 dark:text-black" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-width="2" d="M7 17v1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-4a3 3 0 0 0-3 3Zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                </svg>
                
                Member
            </div>
        </a>

        <a href="{{ route('settings.issue') }}">
            <div class="h-11 flex items-center gap-2 rounded-lg px-3 hover:bg-gray-100 {{ request()->routeIs('settings.issue') ? 'bg-gray-200' : '' }}">
    
                <svg class="w-6 h-6 text-gray-800 dark:text-black" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
                
                Issue
            </div>
        </a>

        <a href="{{ route('settings.storage') }}">
            <div class="h-11 flex items-center gap-2 rounded-lg px-3 hover:bg-gray-100 {{ request()->routeIs('settings.storage') ? 'bg-gray-200' : '' }}">
    
                <svg class="w-6 h-6 text-gray-800 dark:text-black" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 6c0 1.657-3.134 3-7 3S5 7.657 5 6m14 0c0-1.657-3.134-3-7-3S5 4.343 5 6m14 0v6M5 6v6m0 0c0 1.657 3.134 3 7 3s7-1.343 7-3M5 12v6c0 1.657 3.134 3 7 3s7-1.343 7-3v-6"/>
                </svg>

                Storage
            </div>
        </a>

        <a href="{{ route('settings.calender') }}">
            <div class="h-11 flex items-center gap-2 rounded-lg px-3 hover:bg-gray-100 {{ request()->routeIs('settings.calender') ? 'bg-gray-200' : '' }}">
                
                <svg class="w-6 h-6 text-gray-800 dark:text-black" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 10h16m-8-3V4M7 7V4m10 3V4M5 20h14a1 1 0 0 0 1-1V7a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1Zm3-7h.01v.01H8V13Zm4 0h.01v.01H12V13Zm4 0h.01v.01H16V13Zm-8 4h.01v.01H8V17Zm4 0h.01v.01H12V17Zm4 0h.01v.01H16V17Z"/>
                </svg>

                Calender
            </div>
        </a>
    </div>
</aside>
