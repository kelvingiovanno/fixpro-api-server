<?php 

namespace App\Services\Id;

use App\Models\PendingApplication;
use App\Services\Id\BasedIdServices;

class ApplicationIdService extends BasedIdServices
{
    public function generate(): string
    {
        return 'APP-' . $this->generateUuid(); 
    }

    public function checkId($_id): bool 
    {
        return PendingApplication::where('application_id', $_id)->exists();
    }
}