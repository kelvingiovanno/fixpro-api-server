<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageService
{
    /**
     * Store a base64 document under ticket-documents/{ticket_id}/
     *
     * @param string $base64Content
     * @param string $originalName
     * @param int $ticketId
     * @return string Relative path to the stored file
     */
    public function storeTicketDocument(string $base64Content, string $originalName, string $ticketId): string
    {
        $fileContent = base64_decode($base64Content);

        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $fileName = Str::uuid() . '.' . $extension;

        $filePath = "ticket-documents/{$ticketId}/{$fileName}";

        Storage::disk('public')->put($filePath, $fileContent);

        return $filePath;
    }

    /**
     * Store a base64 document under ticket-log-documents/{logTicketId}/
     *
     * @param string $base64Content
     * @param string $originalName
     * @param int $logTicketId
     * @return string Relative path to the stored file
     */
    public function storeLogTicketDocument(string $base64Content, string $originalName, string $logTicketId): string
    {
        $fileContent = base64_decode($base64Content);

        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $fileName = Str::uuid() . '.' . $extension;

        $filePath = "ticket-log-documents/{$logTicketId}/{$fileName}";

        Storage::disk('public')->put($filePath, $fileContent);

        return $filePath;
    }

    /**
     * Store a base64 document under work-order-documents/{ticketIssueId}/
     *
     * @param string $base64Content
     * @param string $originalName
     * @param int $ticketIssueId
     * @return string Relative path to the stored file
     */
    public function storeWoDocument(string $base64Content, string $originalName, string $ticketIssueId)
    {
        $fileContent = base64_decode($base64Content);

        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $fileName = Str::uuid() . '.' . $extension;

        $filePath = "work-order-documents/{$ticketIssueId}/{$fileName}";

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
    public function storeUserProfileImage(string $base64Content, string $originalName, string $userId): string
    {
        $fileContent = base64_decode($base64Content);

        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $fileName = Str::uuid() . '.' . $extension;

        $filePath = "users/{$userId}/profile-images/{$fileName}";

        Storage::disk('public')->put($filePath, $fileContent);

        return $filePath;
    }
}