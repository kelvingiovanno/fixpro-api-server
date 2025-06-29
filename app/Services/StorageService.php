<?php

namespace App\Services;

use App\Enums\StorageTypeEnum;

use App\Exceptions\InvalidStorageTypeException;

use App\Models\SystemSetting;

use Google\Cloud\Storage\StorageClient;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageService
{

    public function __construct(
        protected AreaService $areaService,
    ) { }

    public function storeTicketDocument(string $base64Content, string $originalName, string $ticketId): string
    {
        $filePath = "ticket-documents/{$ticketId}/" . $this->generateFileName($originalName);
        return $this->store($base64Content, $filePath);
    }

    public function storeLogTicketDocument(string $base64Content, string $originalName, string $logTicketId): string
    {
        $filePath = "ticket-log-documents/{$logTicketId}/" . $this->generateFileName($originalName);
        return $this->store($base64Content, $filePath);
    }

    public function storeWoDocument(string $base64Content, string $originalName, string $ticketIssueId): string
    {
        $filePath = "work-order-documents/{$ticketIssueId}/" . $this->generateFileName($originalName);
        return $this->store($base64Content, $filePath);
    }

    public function storeUserProfileImage(string $base64Content, string $originalName, string $userId): string
    {
        $filePath = "users/{$userId}/profile-images/" . $this->generateFileName($originalName);
        return $this->store($base64Content, $filePath);
    }

    protected function store(string $base64Content, string $filePath): string
    {
        $storageType = $this->areaService->get_storage_type();
        

        $fileContent = base64_decode($base64Content);

        if ($storageType === StorageTypeEnum::GOOGLE_CLOUD->value) {
            return $this->storeToCloud($fileContent, $filePath);
        } 
        else if ($storageType === StorageTypeEnum::LOCAL->value)
        {
            return  $this->storeToLocal($fileContent, $filePath);
        }

        throw new InvalidStorageTypeException();
    }

    protected function storeToLocal(string $fileContent, string $filePath): string
    {
        Storage::disk('public')->put($filePath, $fileContent);

        $publicUrl  =  public_path('storage/' . $filePath);
            
        return $publicUrl ;
    }

    protected function storeToCloud(string $fileContent, string $filePath): string
    {
        $bucketName = SystemSetting::get('google_cloud_bucket_name');
       
        $gcsClient = new StorageClient([
            'keyFilePath' => storage_path('app/private/gcs-key.json'),
        ]);

        $bucket = $gcsClient->bucket($bucketName);

        $object = $bucket->upload($fileContent, [
            'name' => $filePath,
        ]);

        $object->update(['acl' => []], ['predefinedAcl' => 'PUBLICREAD']);

        return "https://storage.googleapis.com/{$bucketName}/{$filePath}";
    }

    protected function generateFileName(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return Str::uuid() . '.' . $extension;
    }
}
