<?php

namespace App\Services;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class CloudinaryService
{
    /**
     * Upload an image to Cloudinary
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder
     * @return string URL of uploaded image
     * @throws \Exception
     */
    public function uploadImage($file, $folder = 'localservices')
    {
        // Validate file size (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new \Exception('File size exceeds 5MB limit');
        }

        // Validate file type
        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \Exception('Only JPG and PNG images are allowed');
        }

        try {
            $uploadedFileUrl = Cloudinary::upload($file->getRealPath(), [
                'folder' => $folder,
                'resource_type' => 'image'
            ])->getSecurePath();

            return $uploadedFileUrl;
        } catch (\Exception $e) {
            throw new \Exception('Failed to upload image: ' . $e->getMessage());
        }
    }
}
