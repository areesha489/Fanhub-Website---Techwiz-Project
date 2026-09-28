<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Static information pages: contact, privacy and terms.
 *
 * GET /contact · GET /privacy · GET /terms
 */
class PageController extends Controller
{
    /**
     * Contact card with the support address and community links.
     */
    public function contact(): View
    {
        return view('contact');
    }

    /**
     * Privacy policy.
     */
    public function privacy(): View
    {
        return view('privacy');
    }

    /**
     * Terms of service.
     */
    public function terms(): View
    {
        return view('terms');
    }
}
