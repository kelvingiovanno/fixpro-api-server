<?php

namespace App\Http\Controllers;

use App\Services\QrCodeService;
use App\Services\ReferralCodeService;

class QrCodePageController extends Controller
{

    protected QrCodeService $qrCodeService;
    protected ReferralCodeService $referralCodeService;

    public function __construct(QrCodeService $_qrCodeService, ReferralCodeService $_referralCodeService)
    {
        $this->qrCodeService = $_qrCodeService;
        $this->referralCodeService = $_referralCodeService;
    }

    public function index()
    {
        return view('home');
    }

    public function showQrCode()
    {

        $code = $this->referralCodeService->generateReferral();

        $host = env('APP_URL') . '/api'; 

        $data = [
            "endpoint" => $host,
            "referralTrackingIdentifier" => $code,
        ];

        $jsonData = json_encode($data, JSON_UNESCAPED_SLASHES);



        $qrCode = $this->qrCodeService->generateBarcode($jsonData);

        return response($qrCode)->header('Content-Type', 'image/png');
    }

    public function refreshQrCode()
    {
        $this->referralCodeService->generateReferral();
        return redirect()->route('qrcode.');
    }
}
