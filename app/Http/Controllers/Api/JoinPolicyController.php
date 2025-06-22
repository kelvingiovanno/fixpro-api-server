<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JoinPolicyController extends Controller
{
    public function index()
    {
        try
        {
            $join_policy = SystemSetting::get('area_join_policy');

            if (!$join_policy)
            {
                return $this->apiResponseService->noContent('join_policy has not been set.');
            }

            $response_data = [
                'join_policy' => $join_policy,
            ];

            return $this->apiResponseService->ok($response_data, 'join_policy retrieved successfully.');
        }
        catch(Throwable $e)
        {
            Log::error('Failed to retrieve join policy', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Failed to retrieve join policy');
        }
    }

    public function update(Request $_request)
    {
        $input = $_request->input('data.join_policy');

        if(!$input)
        {
            return $this->apiResponseService->badRequest('The join_policy field is required.');
        }

        try
        {
            SystemSetting::put('area_join_policy', $input);

            $join_policy = SystemSetting::get('area_join_policy');

            $response_data = [
                'join_policy' => $join_policy,
            ];

            return $this->apiResponseService->ok($response_data, 'join_policy has been updated successfully.');
        }
        catch (Throwable $e)
        {
            Log::error('Failed to update join policy', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Failed to update join policy');
        }
    }
}
