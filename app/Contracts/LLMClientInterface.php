<?php
namespace App\Contracts;
interface LLMClientInterface {
    public function generateDocumentation(string $systemPrompt, string $diffContent): string;
    public function analyzeProject(string $systemPrompt, array $contextChunks): string;
    public function generateEmbedding(string $text): array;
}
