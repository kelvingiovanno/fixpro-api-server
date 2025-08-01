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
        $this->referralCodeService->generate();

        $referral_code = $this->referralCodeService->get();

        return view('home', compact('referral_code'));
    }

    public function showQrCode()
    {

        $code = $this->referralCodeService->get();

        $host = env('APP_URL') . '/api'; 

        $data = [
            "endpoint" => $host,
            "referralTrackingIdentifier" => $code,
        ];

        $jsonData = json_encode($data, JSON_UNESCAPED_SLASHES);

        $qrCode = $this->qrCodeService->generateBarcode($jsonData);

        return response($qrCode)->header('Content-Type', 'image/svg+xml');
    }
}
