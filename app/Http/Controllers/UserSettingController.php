<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class UserSettingController extends Controller
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
        $data = $request->json()->all();

        $tableCreated = $this->createUsersTable($data);
    
        return response()->json([
            'success' => $tableCreated,
            'email' => $data['email'] ?? false,
            'phone' => $data['phone'] ?? false,
            'custom' => $data['custom'] ?? []
        ]);
    }

    private function createUsersTable($data)
    {
        if (!Schema::hasTable('users_data')) {
            Schema::create('users_data', function (Blueprint $table) use ($data) {
                
                $table->id();

                if($data['email']) {
                    $table->string('email')->nullable();
                }

                if($data['phone']) {
                    $table->string('phone')->nullable();
                }
                
                foreach ($data['custom'] as $key => $customField) {
                    if (isset($customField['value']) && is_string($customField['value'])) {
                        $columnName = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower(trim($customField['value'])));
                        $table->string($columnName)->nullable();
                    }
                }

                $table->timestamps();
            });

            return true; 
        }

        return false; 
    }
}
