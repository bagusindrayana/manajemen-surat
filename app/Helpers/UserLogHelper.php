<?php
namespace App\Helpers;

use App\Models\UserLog;
use Illuminate\Support\Facades\Auth;

class UserLogHelper {


    public static function create($action)
    {
        return UserLog::create([
            'user_id'=>Auth::user()->id,
            'ip_address'=>request()->ip(),
            'user_agent'=>request()->header('User-Agent'),
            'action'=>$action
        ]);
    }
}