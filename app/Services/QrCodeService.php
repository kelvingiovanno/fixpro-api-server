<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{

    public function generateBarcode($data, $filePath = null)
    {
        $qrCode = QrCode::format('png')
            ->size(200)
            ->generate($data);


        if ($filePath) {
            file_put_contents($filePath, $qrCode);
        }

        return $qrCode; 
    }
}
