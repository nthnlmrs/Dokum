<?php
namespace App\Services\LLM;
use App\Contracts\LLMClientInterface;
class MockLLMClient implements LLMClientInterface {
    public function generateDocumentation(string $systemPrompt, string $diffContent): string { return "# Automated Feature Documentation\nMock generated doc."; }
    public function analyzeProject(string $systemPrompt, array $contextChunks): string { return "# Project Architecture Overview\nMock ERD outline."; }
    public function generateEmbedding(string $text): array { return array_fill(0, 1536, 0.01); }
}
