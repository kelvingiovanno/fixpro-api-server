<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JoinBarcodeController extends Controller
{
    public function barcode()
    {
        try 
        {
            $endpoint = env('APP_URL') . '/api';
            $refferal = $this->referralCodeService->getReferral();

            $data = [
                "endpoint" => $endpoint,
                "referral_tracking_identifier" => $refferal,
            ];

            $jsonData = json_encode($data, JSON_UNESCAPED_SLASHES);

            $qrCode = $this->qrCodeService->generateBarcode($jsonData);

            return response($qrCode)->header('Content-Type', 'image/png');
        } 
        catch (Throwable $e) 
        {
            Log::error('Failed to retrieve join code', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Failed to retrieve join code');
        }
    } 

    public function refresh()
    {
        try 
        {
            $this->referralCodeService->deleteReferral();
            
            return $this->apiResponseService->ok('Referral code successfully deleted.');
        } 
        catch (Throwable $e) 
        {
            Log::error('Failed to delete join code', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
           
            return $this->apiResponseService->internalServerError('Failed to delete join code');
        }
    }
}
