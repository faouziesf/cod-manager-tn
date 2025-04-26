<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $admins = Admin::where('is_super_admin', false)->count();
        $activeAdmins = Admin::where('is_super_admin', false)->where('active', true)->count();
        
        return view('superadmin.dashboard', compact('admins', 'activeAdmins'));
    }
}