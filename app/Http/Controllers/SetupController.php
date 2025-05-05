<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Services\AreaConfigService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

use Throwable;

class SetupController extends Controller
{
    private AreaConfigService $areaConfigService;

    public function __construct(AreaConfigService $_areaConfigService)
    {
        $this->areaConfigService = $_areaConfigService;
    }

    public function index()
    {
        if ($this->areaConfigService->isSetUp()) {
            return redirect('/');
        }

        return view('setup');
    }

    public function submit(Request $request)
    {
        $request_data = $request->only([
            'area_name',
            'join_policy',
            'forms',
            'google_client_id',
            'google_client_secret',
            'google_callback',
        ]);
    
        $validator = Validator::make($request_data, [
            'area_name' => 'required|string|max:255',
            'join_policy' => 'required|string',
            'forms' => 'required|array|min:1',
            'forms.*' => 'required|string|max:255',
            'google_client_id' => 'required|string|max:255',
            'google_client_secret' => 'required|string|max:255',
            'google_callback' => 'required|string|url|max:255',
        ], [
            'area_name.required' => 'Please provide an area name.',
            'join_policy.required' => 'Please select a join policy.',
            'forms.required' => 'Please provide at least one form.',
            'forms.array' => 'The forms field must be an array.',
            'forms.min' => 'You must have at least one form.',
            'forms.*.required' => 'Each form must be a valid string.',
            'google_callback.url' => 'The Google Callback URL must be a valid URL.',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator) 
                ->withInput(); 
        }
    
        try {
            if ($this->areaConfigService->isSetUp()) {
                return redirect('/');
            }
    
            $area_name = $request_data['area_name'];
            $join_policy = $request_data['join_policy'];
            $forms = $request_data['forms'];
    
            $client_id = $request_data['google_client_id'];
            $client_secret = $request_data['google_client_secret'];
            $callback = $request_data['google_callback'];
    
            $this->areaConfigService->setName($area_name);
            $this->areaConfigService->setJoinPolicy($join_policy);
    
            $formatted_forms = $this->formatForms($forms);
            $this->areaConfigService->setJoinForm($formatted_forms);
    
            $this->createTable($formatted_forms);
    
            SystemSetting::put('google_client_id', $client_id);
            SystemSetting::put('google_client_secret', $client_secret);
            SystemSetting::put('google_redirect_uri', $callback);
    
            $this->areaConfigService->markAsSetUp();
    
            return redirect('google/auth');
    
        } catch (Throwable $e) {
            Log::error('Error occurred during set up', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            return redirect()->back()->withErrors(['setup_error' => 'Setup failed. Please try again.']);
        }
    }
    

    private function createTable(array $data): void
    {
        $formatted = $this->formatForms($data);

        Schema::table('users_data', function (Blueprint $table) use ($formatted) {
            foreach ($formatted as $column) {
                $table->string($column)->nullable();
            }
        });

        Schema::table('applicants', function (Blueprint $table) use ($formatted) {
            foreach ($formatted as $column) {
                $table->string($column)->nullable();
            }
        });
    }

    private function formatForms(array $data): array
    {
        return array_map(function ($item) {
            $item = trim($item);
            $item = strtolower($item);
            return preg_replace('/[^a-z0-9_]/', '', str_replace(' ', '_', $item));
        }, $data);
    }
}
