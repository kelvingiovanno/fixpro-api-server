<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\QrCodeService;
use App\Services\EncryptionService;
use App\Services\ReferralCodeService;
use App\Models\ReferralCode;

class QrCodeController extends Controller
{

    protected $qrCodeService;
    protected $encryptionService;
    protected $referralCodeService;


    public function __construct(QrCodeService $_qrCodeService, EncryptionService $_encryptionService, ReferralCodeService $_referralCodeService)
    {
        $this->qrCodeService = $_qrCodeService;
        $this->encryptionService = $_encryptionService;
        $this->referralCodeService = $_referralCodeService;
    }

    public function index()
    {
        $this->referralCodeService->deleteReferralCode();
        return view('qrcode');
    }

    public function showQrCode()
    {

        $key = $this->encryptionService->generateKey();
        $code = $this->referralCodeService->generateCode();

        $host = env('APP_URL');

        ReferralCode::create(['code' => $code, 'key' => $key]);

        $data = [
            'code' => $code,
            'host' => $host,
        ];

        $jsonData = json_encode($data);

        $qrCode = $this->qrCodeService->generateBarcode($jsonData);

        return response($qrCode)->header('Content-Type', 'image/png');
    }

    public function refreshQrCode()
    {
        $this->referralCodeService->deleteReferralCode();
        return redirect()->route('qrcode.');
    }
}
