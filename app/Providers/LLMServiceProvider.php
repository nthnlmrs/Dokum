<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Contracts\LLMClientInterface;
use App\Services\LLM\DeepSeekClient;
use App\Services\LLM\MockLLMClient;
class LLMServiceProvider extends ServiceProvider {
    public function register(): void { \$this->app->singleton(LLMClientInterface::class, function (\$app) { return new MockLLMClient(); }); }
    public function boot(): void {}
}
