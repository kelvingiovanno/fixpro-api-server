<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageService
{
    /**
     * Store a base64 document under tickets/{ticket_id}/
     *
     * @param string $base64Content
     * @param string $originalName
     * @param int $ticketId
     * @return string Relative path to the stored file
     */
    public function storeTicketDocument(string $base64Content, string $originalName, int $ticketId): string
    {
        $fileContent = base64_decode($base64Content);

        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $fileName = Str::uuid() . '.' . $extension;

        $filePath = "tickets/{$ticketId}/{$fileName}";

        Storage::disk('public')->put($filePath, $fileContent);

        return $filePath;
    }

    /**
     * Store a base64 document under tickets/{ticket_id}/
     *
     * @param string $base64Content
     * @param string $originalName
     * @param int $logTicketId
     * @return string Relative path to the stored file
     */
    public function storeLogTicketDocument(string $base64Content, string $originalName, int $logTicketId): string
    {
        $fileContent = base64_decode($base64Content);

        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $fileName = Str::uuid() . '.' . $extension;

        $filePath = "ticket-logs/{$logTicketId}/{$fileName}";

        Storage::disk('public')->put($filePath, $fileContent);

        return $filePath;
    }

    /**
     * Store a base64-encoded user profile document under users/{user_id}/
     *
     * @param string $base64Content
     * @param string $originalName
     * @param int $userId
     * @return string Relative path to the stored file
     */
    public function storeUserProfileImage(string $base64Content, string $originalName, int $userId): string
    {
        $fileContent = base64_decode($base64Content);

        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $fileName = Str::uuid() . '.' . $extension;

        $filePath = "users/{$userId}/profile-images/{$fileName}";

        Storage::disk('public')->put($filePath, $fileContent);

        return $filePath;
    }
}