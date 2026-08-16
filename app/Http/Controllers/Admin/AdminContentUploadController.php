<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreContentUploadRequest;
use Illuminate\Http\JsonResponse;

class AdminContentUploadController extends Controller
{
    public function store(StoreContentUploadRequest $request): JsonResponse
    {
        $path = $request->file('image')->store('content/images', 'public');

        return response()->json([
            'url' => asset('storage/'.$path),
        ]);
    }
}
