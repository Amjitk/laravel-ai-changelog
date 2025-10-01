<?php

namespace Amjitk\AiChangelog;

use HosseinHezami\LaravelGemini\Facades\Gemini;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;

class ChangelogGenerator
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Executes the main changelog generation process.
     * * @param string $fromCommit The starting point (tag/SHA) for the git range.
     * @param string $toCommit The ending point (HEAD/SHA).
     * @param string $branch The branch being analyzed.
     * @return string|null The generated markdown changelog or null on failure.
     */
    public function generate(string $fromCommit, string $toCommit, string $branch): ?string
    {
        // 1. Get raw commit messages
        $rawCommits = $this->getRawCommits($fromCommit, $toCommit, $branch);
        $lines = explode("\n", trim($rawCommits));
        $formattedCommits = "";

        foreach ($lines as $line) {
            if (preg_match('/^(feat|fix|chore|docs|refactor|test|style):\s*(.+)$/i', $line, $matches)) {
                $type = $matches[1];
                $message = $matches[2];
                $formattedCommits .= "# " . date('Y-m-d') . " - {$type}: {$message}\n";
            } else {
                $formattedCommits .= "# " . date('Y-m-d') . " - commit: {$line}\n";
            }
        }

        // Now pass $formattedCommits to AI for generating bullet points
        $prompt = $this->config['ai_prompt_prefix'] . "\n\n---\n" . $formattedCommits;

        // 3. Call the AI Service
        return $this->callAIService($prompt);
    }

    /**
     * Runs the git log command.
     */
    protected function getRawCommits(string $from, string $to, string $branch): string
    {
        $command = "git log --pretty=format:'%h - %s%n%b' --no-merges {$from}..{$to} --first-parent --date-order";

        $process = Process::fromShellCommandline($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException("Git command failed: " . $process->getErrorOutput());
        }

        return $process->getOutput();
    }


    /**
     * Calls the external AI API.
     */
    protected function callAIService(string $prompt): ?string
    {
        try {
            $response = Gemini::text()
            ->model($this->config['api']['model'] ?? 'gemini-2.5-flash')
            ->system('You are a helpful assistant. Your task is to summarize Git commit messages into a user-friendly changelog.')
            ->prompt($prompt)
            ->temperature($this->config['api']['temperature'] ?? 0.3)
            ->maxTokens($this->config['api']['max_tokens'] ?? 1024)
            ->generate();

            // Return the generated text
            return $response->content();

        } catch (\Exception $e) {
            $this->logAIError($e->getMessage());
            return null;
        }
    }

    /**
     * Helper to log AI errors
     */
    protected function logAIError(string $message)
    {
        Log::error("[AI Changelog] API Error: {$message}");
        echo "⚠️ AI API Error: {$message}\n"; // also show in console
    }

}