<?php

namespace App\Http\Controllers;

use App\Models\UserData;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class UserFormPageController extends Controller
{

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
            'phone' => isset($data['phone']),
            'custom' => $data['custom'] ?? []
        ]);
    }

    private function createUsersTable($data)
    {
        if (!Schema::hasTable('users_data') && !Schema::hasTable('users_pending')) {
            
            Schema::create('users_data', function (Blueprint $table) use ($data) {
                
                $table->id();
                
                $table->uuid('user_id')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

                $table->string('full_name');
                $table->string('title');

                if (!empty($data['email'])) {
                    $table->string('email')->nullable();
                }

                if (!empty($data['phone'])) {
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
            });

            Schema::create('applicants', function (Blueprint $table) use ($data) {
                
                $table->uuid('id')->primary();
                $table->boolean('is_accepted')->default(false);
    
                $table->string('full_name');
                $table->string('title');

                if (!empty($data['email'])) {
                    $table->string('email')->nullable();
                }

                if (!empty($data['phone'])) {
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
            });

            return true; 
        }

        return false; 
    }
}
