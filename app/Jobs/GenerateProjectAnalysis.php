<?php

namespace App\Jobs;

use App\Models\Repository;
use App\Models\Documentation;
use App\Services\AiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Exception;

class GenerateProjectAnalysis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes

    public function __construct(
        public Repository $repository
    ) {}

    public function handle(AiService $aiService): void
    {
        $user = $this->repository->user;
        $githubToken = $user->github_token ?? $user->github_access_token; // check both depending on how it was saved

        if (!$githubToken) {
            throw new Exception("No GitHub token found for user ID: {$user->id}. Cannot fetch repo files.");
        }

        // Fetch repo details
        $repoFullName = $this->repository->full_name;
        $url = "https://api.github.com/repos/{$repoFullName}/contents";

        $response = Http::withToken($githubToken)
            ->withHeaders(['Accept' => 'application/vnd.github.v3+json'])
            ->get($url);

        if ($response->failed()) {
            throw new Exception("Failed to fetch repository contents from GitHub for {$repoFullName}. " . $response->body());
        }

        $files = collect($response->json())->filter(function ($item) {
            return $item['type'] === 'file' && in_array(pathinfo($item['name'], PATHINFO_EXTENSION), ['php', 'js', 'ts', 'vue', 'py', 'java', 'md', 'json', 'yml', 'yaml', 'txt', 'html', 'css']);
        });

        $codebaseContent = "";

        // Let's only take top 5 files to not overload context for this prototype
        foreach ($files->take(5) as $file) {
            $fileResponse = Http::withToken($githubToken)
                ->withHeaders(['Accept' => 'application/vnd.github.v3.raw'])
                ->get($file['download_url']);

            if ($fileResponse->successful()) {
                $codebaseContent .= "### File: {$file['path']}\n```\n" . substr($fileResponse->body(), 0, 3000) . "\n```\n\n";
            }
        }

        if (empty($codebaseContent)) {
            $codebaseContent = "No supported code files found to analyze.";
        }

        $prompt = "You are a senior technical writer and software architect. Please analyze the provided codebase content and generate a comprehensive documentation for the project. Include an overview, architecture summary, and main features. Use Markdown formatting.";

        $analysisResult = $aiService->generateContent($prompt, $codebaseContent);

        // Save to documentations table as 'ongoing' or 'draft'
        Documentation::updateOrCreate(
            ['repository_id' => $this->repository->id],
            [
                'title' => "Documentation for " . $repoFullName,
                'content' => $analysisResult,
                'status' => 'draft', // keep it draft so it can be appended later
            ]
        );
    }
}
