<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Documentation;
class DashboardController extends Controller
{
    public function index() { return Inertia::render('Dashboard', ['documentations' => Documentation::with('repository')->latest()->get()]); }
    public function edit(Documentation \$documentation) { return Inertia::render('Editor', ['documentation' => \$documentation->load('repository')]); }
    public function update(Request \$request, Documentation \$documentation) {
        // Keep status as 'draft' so AI can keep appending to it on new PRs.
        $documentation->update(['content' => $request->validate(['content' => 'required|string'])['content'], 'status' => 'draft']);
        return redirect()->back()->with('success', 'Documentation updated successfully.');
    }
}
