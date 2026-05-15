<?php

namespace App\Http\Controllers;

use App\Models\AiConfiguration;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Settings/Index', [
            'aiConfigurations' => AiConfiguration::all(),
            // User's github token is sent to UI if it exists, but we mask it for security or just send boolean
            'hasGithubToken' => !empty($request->user()->github_token),
        ]);
    }

    public function storeAiConfiguration(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string|in:openai,gemini,anthropic',
            'model_name' => 'required|string',
            'api_key' => 'required|string',
        ]);

        // If this is the first config, make it active
        $isFirst = AiConfiguration::count() === 0;
        $validated['is_active'] = $isFirst;

        AiConfiguration::create($validated);

        return redirect()->back()->with('success', 'AI Configuration added successfully.');
    }

    public function updateAiConfiguration(Request $request, AiConfiguration $aiConfiguration)
    {
        $validated = $request->validate([
            'provider' => 'required|string|in:openai,gemini,anthropic',
            'model_name' => 'required|string',
            'api_key' => 'nullable|string', // nullable in case they don't want to change it
        ]);

        if (empty($validated['api_key'])) {
            unset($validated['api_key']);
        }

        $aiConfiguration->update($validated);

        return redirect()->back()->with('success', 'AI Configuration updated successfully.');
    }

    public function deleteAiConfiguration(AiConfiguration $aiConfiguration)
    {
        $aiConfiguration->delete();

        // If we deleted the active one, activate another one if exists
        if ($aiConfiguration->is_active) {
            $next = AiConfiguration::first();
            if ($next) {
                $next->update(['is_active' => true]);
            }
        }

        return redirect()->back()->with('success', 'AI Configuration deleted successfully.');
    }

    public function setActiveAiConfiguration(AiConfiguration $aiConfiguration)
    {
        // Deactivate all
        AiConfiguration::query()->update(['is_active' => false]);

        // Activate the selected one
        $aiConfiguration->update(['is_active' => true]);

        return redirect()->back()->with('success', 'Active AI Configuration updated.');
    }

    public function updateGithubToken(Request $request)
    {
        $validated = $request->validate([
            'github_token' => 'required|string',
        ]);

        $request->user()->update([
            'github_token' => $validated['github_token']
        ]);

        return redirect()->back()->with('success', 'GitHub Token updated successfully.');
    }
}
