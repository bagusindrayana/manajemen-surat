<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function readNotification($id)
    {
        Notifikasi::find($id)->update(['is_read' => true]);
        return response()->json(['success' => true], 200);
    }
}
