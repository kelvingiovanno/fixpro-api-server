<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Services\ApiResponseService;
use App\Services\QrCodeService;
use App\Services\ReferralCodeService;

use Illuminate\Support\Facades\Log;

use Throwable;

class JoinBarcodeController extends Controller
{
    public function __construct(
        protected ApiResponseService $apiResponseService,
        protected ReferralCodeService $referralCodeService,
        protected QrCodeService $qrCodeService,
    ) { }

    public function barcode()
    {
        try 
        {
            $endpoint = env('APP_URL') . '/api';
            $refferal = $this->referralCodeService->get();

            $data = [
                "endpoint" => $endpoint,
                "referralTrackingIdentifier" => $refferal,
            ];

            $jsonData = json_encode($data, JSON_UNESCAPED_SLASHES);

            $qrCode = $this->qrCodeService->generateBarcode($jsonData);

            return response($qrCode)->header('Content-Type', 'image/svg+xml');
        } 
        catch (Throwable $e) 
        {
            Log::error('An error occurred while generating the join barcode', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    } 

    public function renew()
    {
        try 
        {
            $this->referralCodeService->delete();
            
            return $this->apiResponseService->ok('Join barcode renewed successfully.');
        } 
        catch (Throwable $e) 
        {
            Log::error('An error occurred while renewing the join barcode', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
           
            return $this->apiResponseService->internalServerError('Something went wrong, please try again later.');
        }
    }
}
