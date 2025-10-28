<?php
// app/Http/Controllers/MapController.php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index()
    {
        $locations = Location::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('maps.index', compact('locations'));
    }
}