<?php

namespace App\Http\Controllers\WWW;

use App\Http\Controllers\Controller;
use App\Models\BlockNews;
use Dompdf\Renderer\Block;
use Illuminate\Http\Request;

class BlockNewsController extends Controller
{
    public function index(Request $request, $category = null)
    {
        $data = [];
        $companies = BlockNews::where('status', '<>', BlockNews::STATUS_DELETE)->distinct('company')->pluck('company');

        foreach ($companies as $company) {
            $news = BlockNews::where('status', '<>', BlockNews::STATUS_DELETE)
                                ->where('company', $company)
                                ->where('category', $category)
                                ->take(10)
                                ->get();

            $data[$company] = $news;
        }

        $categories = BlockNews::where('status', '<>', BlockNews::STATUS_DELETE)->distinct('category')->pluck('category');

        return view('block_news.index', compact('data', 'categories'));
    }
}
