<?php

namespace App\Http\Controllers\WWW;

use App\Http\Controllers\Controller;

class BlockNewsController extends Controller
{
    public function index()
    {
        return view('block_news.index');
    }
}
