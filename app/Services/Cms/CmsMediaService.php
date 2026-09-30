<?php

namespace App\Services\Cms;

use App\Models\CmsMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class CmsMediaService
{
    public function store(UploadedFile $file, ?int $userId = null): CmsMedia
    {
        $year   = date('Y');
        $month  = date('m');
        $folder = "uploads/cms/{$year}/{$month}";
        $dir    = public_path($folder);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $original   = $file->getClientOriginalName();
        $ext        = strtolower($file->getClientOriginalExtension());
        $storedName = Str::uuid() . '.' . $ext;
        $path       = $folder . '/' . $storedName;
        $fullPath   = public_path($path);

        $file->move($dir, $storedName);

        $width = $height = null;
        if (str_starts_with($file->getClientMimeType(), 'image/') && function_exists('getimagesize')) {
            $size = @getimagesize($fullPath);
            if ($size) {
                [$width, $height] = $size;
            }
        }

        return CmsMedia::create([
            'filename'    => $original,
            'stored_name' => $storedName,
            'path'        => $path,
            'url'         => asset($path),
            'mime_type'   => $file->getClientMimeType(),
            'extension'   => $ext,
            'file_size'   => filesize($fullPath),
            'width'       => $width,
            'height'      => $height,
            'folder'      => $folder,
            'uploaded_by' => $userId,
        ]);
    }

    public function delete(CmsMedia $media): bool
    {
        $full = public_path($media->path);
        if (file_exists($full)) {
            @unlink($full);
        }
        return $media->delete();
    }
}
