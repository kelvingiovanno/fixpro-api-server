<?php

namespace App\Http\Controllers;

use App\Services\QrCodeService;
use App\Services\EntryService;

class QrCodePageController extends Controller
{

    protected $qrCodeService;
    protected $entryService;

    public function __construct(QrCodeService $_qrCodeService, EntryService $_entryService)
    {
        $this->qrCodeService = $_qrCodeService;
        $this->entryService = $_entryService;
    }

    public function index()
    {
        return view('qrcode');
    }

    public function showQrCode()
    {

        $code = $this->entryService->generateReferral();

        $host = env('APP_URL');

        $data = [
            'endpoint' => $host,
            'referral_tracking_identifier' => $code,
        ];

        $jsonData = json_encode($data);

        $qrCode = $this->qrCodeService->generateBarcode($jsonData);

        return response($qrCode)->header('Content-Type', 'image/png');
    }

    public function refreshQrCode()
    {
        $this->entryService->generateReferral();
        return redirect()->route('qrcode.');
    }
}
