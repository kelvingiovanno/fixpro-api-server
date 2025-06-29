<?php

namespace App\Http\Controllers;

use App\Enums\JoinPolicyEnum;
use App\Models\Enums\TicketIssueType;
use App\Models\SystemSetting;

use App\Services\AreaService;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

use Throwable;

class SettingController extends Controller
{
    public function __construct(
        protected AreaService $areaService,
        protected GoogleCalendarService $googleCalendarService,
    ) { }

    public function index()
    {
        return redirect()->route('settings.area');
    }
    
    public function area(Request $_request)
    {
        return view('settings.setting-area');
    }

    public function member(Request $_request)
    {

        $joinform = array_filter(
            $this->areaService->get_join_form(),
            fn($field) => $field !== 'name'
        );

        $response_data = [
            'form' => $joinform,
        ];

        return view('settings.setting-member', $response_data);
    }

    public function issue(Request $_request)
    {
        return view('settings.setting-issue');
    }

    public function storage(Request $_request)
    {
        return view('settings.setting-storage');
    }

    public function calender(Request $_request)
    {
        return view('settings.setting-calender');
    }


    public function submitSettingArea(Request $_request)
    {
        $request_data = $_request->only([
            'area_name',
            'join_policy',
        ]);

        $validator = Validator::make($request_data,[
            'area_name' => 'required|string',
            'join_policy' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator) 
                ->withInput(); 
        }

        try
        {
            $this->areaService->set_name($request_data['area_name']);
            $this->areaService->set_join_policy(JoinPolicyEnum::from($request_data['join_policy']));

            return redirect()->back()
                ->with('success', 'Settings updated successfully!')
                ->with('area_name', $request_data['area_name'])
                ->with('join_policy', $request_data['join_policy']);

        }
        catch (Throwable $e)
        {
            Log::error('Error occurred during submit setting area', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return redirect()->back()->withErrors(['setup_error' => 'Setup failed. Please try again.']);
        }
    }


    public function submitSettingMember(Request $_request)
    {
        $request_data = $_request->only([
            'forms',
        ]);

        $validator = Validator::make(
            $request_data,
            [
                'forms' => 'required|array',
                'forms.*' => 'required|string|filled',
            ],
            [
                'forms.required' => 'The custom field is required.',
                'forms.array' => 'The custom field must be an array.',
                'forms.*.required' => 'Each custom field is required.',
                'forms.*.string' => 'Each custom field must be a string.',
            ],
        );

        if($validator->fails())
        {
            return redirect()->back()
                ->withErrors($validator);
        }

        try
        {
            $forms = $_request->input('forms', []);
            $forms = array_map(function ($field) {
                return strtolower(str_replace(' ', '_', trim($field)));
            }, $forms);

            $this->areaService->set_join_form($forms);

            $protected = [
                'id', 'role_id', 'name', 'title',
                'member_since', 'member_until', 'deleted_at',
                'created_at', 'updated_at',
            ];

            $allColumns = Schema::getColumnListing('members');

            $toAdd = array_diff($forms, $allColumns);
            $toDelete = array_diff($allColumns, array_merge($forms, $protected));

            if (!empty($toAdd)) {
                Schema::table('members', function (Blueprint $table) use ($toAdd) {
                    foreach ($toAdd as $column) {
                        if (!Schema::hasColumn('members', $column)) {
                            $table->string($column)->nullable();
                        }
                    }
                });
            }

            if (!empty($toDelete)) {
                Schema::table('members', function (Blueprint $table) use ($toDelete) {
                    foreach ($toDelete as $column) {
                        if (Schema::hasColumn('members', $column)) {
                            $table->dropColumn($column);
                        }
                    }
                });
            }

            return redirect()->back()
                ->with('success', 'Settings updated successfully!');

        }
        catch (Throwable $e)
        {
            Log::error('Error occurred during submit setting member', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return redirect()->back()->withErrors(['setup_error' => 'Setup failed. Please try again.']);
        }
    }

    public function submitSettingIssue(Request $_request)
    {
        $request_data = $_request->only([
            'issue_types',
            'sla_duration_hour',
        ]);

        $validator = Validator::make($request_data, 
            [
                'issue_types' => 'required|array',
                'issue_types.*' => 'required|string|distinct|unique:ticket_issue_types,name',
                'sla_duration_hour' => 'required|array',
                'sla_duration_hour.*' => 'required|numeric', 
            ], 
            [
                'issue_types.required' => 'The issue name is required.',
                'issue_types.*.required' => 'The issue name is required.',
                'issue_types.*.distinct' => 'Issue names must be unique.',
                'issue_types.*.unique' => 'An issue name already exists in the database.',
                'issue_types.array' => 'The issue_types field must be an array.',
                'sla_duration_hour.*.required' => 'Each SLA Duration is required.',
                'sla_duration_hour.*.numeric' => 'Each SLA Duration must be a number.',
            ],
        );

        if($validator->fails())
        {
            return redirect()->back()
                ->withErrors($validator);
        }

        try
        {
            $issues = $request_data['issue_types'];
            $sla_duration_hour = $request_data['sla_duration_hour'];
            
            foreach($issues as $index => $name)        
            {
                TicketIssueType::create([
                    'name' => $name,
                    'sla_hours' => $sla_duration_hour[$index],
                ]);
            }

            return redirect()->back()
                ->with('success', 'Settings updated successfully!');
        }
        catch (Throwable $e)
        {
            Log::error('Error occurred during submit setting issue', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return redirect()->back()->withErrors(['setup_error' => 'Setup failed. Please try again.']);
        }
    }

    public function submitSettingStorage(Request $_request)
    {
        $request_data = $_request->only([
            'storage_type',
        ]);

        if($request_data['storage_type'] == 'CLOUD')
        {

            $validator = Validator::make($_request->all(), 
                [
                'bucket_name' => 'required|string',
                'gcs_key_file' => 'required|file|mimes:json|max:2048',
                ], 
                [
                    'bucket_name.required' => 'Bucket name is required.',
                    'gcs_key_file.required' => 'The key file is required.',
                    'gcs_key_file.file' => 'The key must be a file.',
                    'gcs_key_file.mimes' => 'The key file must be a JSON file.',
                    'gcs_key_file.max' => 'The key file must not be greater than 2MB.',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            try 
            {
                $bucket_name = $_request['bucket_name'];
                $storage_type = $_request['storage_type'];

                SystemSetting::put('google_cloud_bucket_name', $bucket_name);
                SystemSetting::put('storage_type', $storage_type);

                $file = $_request->file('gcs_key_file');
                $file->storeAs('gcs-key.json');

                SystemSetting::put('gcs_key_store', '1');

                return redirect()->back()
                    ->with('success', 'Settings updated successfully!');
                        
            }
            catch (Throwable $e)
            {
                Log::error('Error occurred during submit setting google cloud storage', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
        
                return redirect()->back()->withErrors(['setup_error' => 'Setup failed. Please try again.']);
            }

        }
        else if ($request_data['storage_type'] == 'LOCAL')
        {
            $validator = Validator::make($_request->all(), 
                [
                'local_storage_path' => 'required|string',
                ], 
                [
                    'local_storage_path.required' => 'Local Storage path is required.',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            try
            {
                SystemSetting::put('storage_type',$_request['storage_type']);
                SystemSetting::put('local_storage_path', $_request['local_storage_path']);
                
                return redirect()->back()
                    ->with('success', 'Settings updated successfully!');
            }
            catch (Throwable $e)
            {
                Log::error('Error occurred during submit setting local cloud storage', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
        
                return redirect()->back()->withErrors(['setup_error' => 'Setup failed. Please try again.']);
            }
        }
        else
        {
            return redirect('/');
        }

    }

    public function submitSettingCalender(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'google_client_id' => 'required|string',
            'google_client_secret' => 'required|string',
            'google_callback' => 'required|string|url',
        ], [
            'google_client_id.required' => 'Google Client ID is required.',
            'google_client_id.string' => 'Google Client ID must be a valid string.',
            
            'google_client_secret.required' => 'Google Client Secret is required.',
            'google_client_secret.string' => 'Google Client Secret must be a valid string.',

            'google_callback.required' => 'Callback URL is required.',
            'google_callback.string' => 'Callback URL must be a valid string.',
            'google_callback.url' => 'Callback URL must be a valid URL.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
               ->withErrors($validator)
                ->withInput();
        }

        try
        {
            $this->googleCalendarService->set_client_id($request['google_client_id']);
            $this->googleCalendarService->set_client_secret($request['google_client_secret']);
            $this->googleCalendarService->set_redirect_uri($request['google_callback']);

            $this->areaService->mark_calendar_setup();

            return redirect()->route('google.auth');
        }
        catch (Throwable $e)
        {
            Log::error('Error occurred during submit setting calender', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        
            return redirect()->back()->withErrors(['setup_error' => 'Setup failed. Please try again.']);
        }
    }
}
