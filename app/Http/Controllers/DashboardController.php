<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $resumes = $user->resumes()->with('coverLetters')->get();
        $covers  = $user->coverLetters()->get();

        return view('dashboard.index', compact('user', 'resumes', 'covers'));
    }
}