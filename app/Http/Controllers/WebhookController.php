<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Jobs\GenerateDocumentationFromPR;
use App\Jobs\GenerateProjectAnalysis;
use App\Models\Repository;
class WebhookController extends Controller {
    public function handle(Request \$request) {
        \$event = \$request->header('X-GitHub-Event'); \$payload = \$request->all();
        if (\$event === 'check_suite' && (\$payload['action'] ?? '') === 'completed') {
            \$repo = Repository::where('full_name', \$payload['repository']['full_name'] ?? '')->first();
            if (\$repo) { GenerateDocumentationFromPR::dispatch(\$repo, (string)(\$payload['check_suite']['pull_requests'][0]['number'] ?? '1')); }
        } elseif (\$event === 'pull_request' && (\$payload['action'] ?? '') === 'closed' && (\$payload['pull_request']['merged'] ?? false)) {
            \$repo = Repository::where('full_name', \$payload['repository']['full_name'] ?? '')->first();
            if (\$repo) { GenerateProjectAnalysis::dispatch(\$repo); }
        }
        return response()->json(['message' => 'Handled']);
    }
}
