<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IssueReport;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    // Show the form
    public function create()
    {
        return view('reports.create');
    }

    // Save the report
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        IssueReport::create([
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        // Redirect back to their specific dashboard
        $route = Auth::user()->isLecturer() ? 'lecturer.dashboard' : 'student.dashboard';

        return redirect()->route($route)->with('success', 'Issue reported successfully. Admin will review it.');
    }
}