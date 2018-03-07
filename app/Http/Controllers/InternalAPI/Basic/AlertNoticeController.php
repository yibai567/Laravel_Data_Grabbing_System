<?php

namespace App\Http\Controllers\InternalAPI\Basic;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AlertNoticeController extends Controller
{
    public function send($type = 'sms', $params = [])
    {

    }

    public function sendSMS($mobile, $type, $data)
    {

    }

    public function sendEmail($email, $data)
    {

    }
}
