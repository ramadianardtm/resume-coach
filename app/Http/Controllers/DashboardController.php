<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user    = Auth::user();
        $resumes = $user->resumes()->with(['coverLetters', 'latestSession'])->get();
        $covers  = $user->coverLetters()->get();

        return view('dashboard.index', compact('user', 'resumes', 'covers'));
    }
}