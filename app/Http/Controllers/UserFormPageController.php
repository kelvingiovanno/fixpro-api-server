<?php

namespace App\Http\Controllers;

use App\Services\AreaConfigService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class UserFormPageController extends Controller
{

    private AreaConfigService $areaConfigService;

    public function __construct(AreaConfigService $_areaConfigService)
    {
        $this->areaConfigService = $_areaConfigService;
    }

    public function index()
    {
        if (Schema::hasTable('users_data')) {
            return redirect('/');
        }

        return view('user-setting');
    }

    public function handleSubmit(Request $request)
    {
        $data = $request->all(); 

        $tableCreated = $this->createUsersTable($data);

        return response()->json([
            'success' => $tableCreated,
            'email' => isset($data['email']), 
            'phone_number' => isset($data['phone_number']),
            'custom' => $data['custom'] ?? []
        ]);
    }

    private function createUsersTable($data)
    {
        if (!Schema::hasTable('users_data') && !Schema::hasTable('users_pending')) {
            
            Schema::create('users_data', function (Blueprint $table) use ($data) {
                
                $table->id();
                
                $table->uuid('user_id')->nullable();
            
                if (!empty($data['email'])) {
                    $table->string('email')->nullable();
                }

                if (!empty($data['phone_number'])) {
                    $table->string('phone_number')->nullable();
                }

                if (!empty($data['custom']) && is_array($data['custom'])) {
                    foreach ($data['custom'] as $customField) {
                        if (!empty($customField) && is_string($customField)) {
                            $columnName = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower(trim($customField)));
                            $table->string($columnName)->nullable();
                        }
                    }
                }

                $table->softDeletes();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });

  
            Schema::table('applicants', function (Blueprint $table) use ($data) {
                
                if (!empty($data['email'])) {   
                    $table->string('email')->nullable();
                }

                if (!empty($data['phone_number'])) {
                    $table->string('phone_number')->nullable();
                }
                
                if (!empty($data['custom']) && is_array($data['custom'])) {
                    foreach ($data['custom'] as $customField) {
                        if (!empty($customField) && is_string($customField)) {
                            $columnName = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower(trim($customField)));
            
                            if (!Schema::hasColumn('applicants', $columnName)) {
                                $table->string($columnName)->nullable();
                            }
                        }
                    }
                }

                $table->softDeletes();
            });

            
            $formFields = [];

            foreach ($data as $key => $value) {
                if ($key === '_token') {
                    continue; 
                }
                if ($value === "1") {
                    $formFields[] = $key;
                }
            
                if ($key === "custom" && is_array($value)) {
                    foreach ($value as $customField) {
                        if (!empty($customField) && is_string($customField)) {
                            $sanitizedField = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower(trim($customField)));
                            $formFields[] = $sanitizedField;
                        }
                    }
                }
            }
            
            $formFields[] = 'name';
            
            $this->areaConfigService->updateJoinForm($formFields);

            return true; 
        }

        return false; 
    }
}
