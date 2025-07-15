<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class SubscriptionMostController extends Controller
{
    public function index()
    {
        return Inertia::render('SubscriptionMost');
    }
}
