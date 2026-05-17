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

class GenerateDocumentationFromPR implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes

    public function __construct(
        public Repository $repository,
        public string $prNumber
    ) {}

    public function handle(AiService $aiService): void
    {
        $user = $this->repository->user;
        $githubToken = $user->github_token ?? $user->github_access_token;

        if (!$githubToken) {
            throw new Exception("No GitHub token found for user ID: {$user->id}. Cannot fetch PR diff.");
        }

        $repoFullName = $this->repository->full_name;
        $url = "https://api.github.com/repos/{$repoFullName}/pulls/{$this->prNumber}";

        // Get PR diff
        $response = Http::withToken($githubToken)
            ->withHeaders(['Accept' => 'application/vnd.github.v3.diff'])
            ->get($url);

        if ($response->failed()) {
            throw new Exception("Failed to fetch PR #{$this->prNumber} diff for {$repoFullName}. " . $response->body());
        }

        $diffContent = substr($response->body(), 0, 10000); // Limit diff size

        // Get existing documentation
        $documentation = Documentation::where('repository_id', $this->repository->id)->first();
        $existingContent = $documentation ? $documentation->content : "No existing documentation.";

        $prompt = "You are a technical writer. The user just merged a Pull Request. Here is the existing documentation:\n\n---\n{$existingContent}\n---\n\nAnd here is the git diff of the new changes:\n\n---\n{$diffContent}\n---\n\nPlease update the existing documentation to reflect these new changes. Append new features or modify existing descriptions. Return ONLY the new full documentation content in Markdown format.";

        $updatedResult = $aiService->generateContent($prompt, "Please update the documentation based on the diff.");

        // Update or create documentation
        Documentation::updateOrCreate(
            ['repository_id' => $this->repository->id],
            [
                'title' => "Documentation for " . $repoFullName,
                'content' => $updatedResult,
                'status' => 'draft', // keep it draft
            ]
        );
    }
}
