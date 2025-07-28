<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RecordAttachment;
use Illuminate\Support\Facades\Storage;

class RecordAttachmentController extends Controller
{
    public function show(RecordAttachment $attachment)
    {
        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404);
        }

        $mime = Storage::disk('public')->mimeType($attachment->file_path);
        $file = Storage::disk('public')->get($attachment->file_path);

        return response($file, 200)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'inline; filename="' . $attachment->original_name . '"');
    }
}
