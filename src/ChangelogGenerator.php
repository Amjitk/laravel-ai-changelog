<?php

namespace Amjitk\AiChangelog;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

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

        if (empty(trim($rawCommits))) {
            return null;
        }

        // 2. Build the AI Prompt
        $prompt = $this->config['ai_prompt_prefix'] . "\n\n---\n" . $rawCommits;

        // 3. Call the AI Service
        return $this->callAIService($prompt);
    }

    /**
     * Runs the git log command.
     */
    protected function getRawCommits(string $from, string $to, string $branch): string
    {
        // Uses --first-parent to ignore merged feature commits unless they are squashed.
        // Adjust the format if needed, but this is detailed for the AI.
        $command = "git log --pretty=format:'%h - %s%n%b' --no-merges {$from}..{$to} --first-parent --date-order -- {$branch}";
        
        $process = Process::fromShellCommandline($command);
        $process->run();

        if (!$process->isSuccessful()) {
            // In a real package, you'd log this error instead of returning it directly
            throw new \RuntimeException("Git command failed: " . $process->getErrorOutput());
        }

        return $process->getOutput();
    }

    /**
     * Calls the external AI API.
     */
    protected function callAIService(string $prompt): ?string
    {
        $apiConfig = $this->config['api'];

        try {
            $response = Http::withToken($apiConfig['key'])
                ->timeout(60)
                ->post($apiConfig['url'], [
                    'model' => $apiConfig['model'],
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => $apiConfig['temperature'],
                ]);

            if ($response->successful()) {
                // Ensure you have error handling for null responses if the AI fails to generate.
                return trim($response->json('choices.0.message.content'));
            }

            // Throw a specific exception on API error for better debugging
            throw new \Exception("AI API Error ({$response->status()}): " . $response->body());

        } catch (\Exception $e) {
            // In a package, you would log this exception
            return null;
        }
    }
}