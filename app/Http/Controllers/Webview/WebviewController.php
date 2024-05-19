<?php

namespace App\Http\Controllers\Webview;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebviewController extends Controller
{
    public function termsOfService()
    {
        return view('webview.terms-of-service');
    }

    public function privacyPolicy()
    {
        return view('webview.privacy-policy');
    }

}
