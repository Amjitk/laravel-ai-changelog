<?php

namespace Amjitk\AiChangelog\Console;

use Illuminate\Console\Command;
use Amjitk\AiChangelog\ChangelogGenerator;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Exception;

class AiChangelogCommand extends Command
{
    protected $signature = 'changelog:ai:generate
                            {--from= : The starting Git commit SHA or tag. If omitted, it compares against the --compare branch or last tag.}
                            {--compare= : The base branch to compare against (e.g., staging). Defaults to config value.}
                            {--cversion= : Manually set the version number for the changelog heading.}
                            {--branch= : The branch being analyzed. Defaults to the current Git branch.}';

    protected $description = 'Generates an AI-summarized changelog based on git commits.';

    protected ChangelogGenerator $generator;

    public function __construct(ChangelogGenerator $generator)
    {
        parent::__construct();

        $this->generator = $generator;
    }

    public function handle()
    {
        $config = config('ai-changelog');
        $from = $this->option('from');
        $compare = $this->option('compare') ?? $config['git']['default_compare_branch'];
        $branch = $this->option('branch') ?? $this->getCurrentBranch();

        // Determine the 'from' point for the git log command
        try {
            $fromCommit = $from ?: $this->getComparisonPoint($compare);
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return Command::FAILURE;
        }

        $this->info("Analyzing branch '{$branch}' from '{$fromCommit}' to 'HEAD'...");

        // 1. Generate the changelog markdown
        $changelogMarkdown = $this->generator->generate($fromCommit, 'HEAD', $branch);

        if (!$changelogMarkdown) {
            $this->comment('⚠️ No new commits found or AI summarization failed. Check logs for details.');
            return Command::SUCCESS;
        }

        // 2. Write the Changelog
        $this->writeChangelog(
            $config['output']['file'],
            $changelogMarkdown,
            $this->option('cversion')
        );

        $this->info('✅ Changelog generated successfully!');
        $this->comment("Output written to: {$config['output']['file']}");

        return Command::SUCCESS;
    }

    /**
     * Finds the base commit (last tag or the SHA of the comparison branch).
     */
    protected function getComparisonPoint(string $compareBranch): string
    {
        // Try merge-base
        $process = Process::fromShellCommandline("git merge-base {$compareBranch} HEAD");
        $process->run();
        $mergeBase = trim($process->getOutput());

        if ($process->isSuccessful() && $mergeBase && $mergeBase !== trim(shell_exec('git rev-parse HEAD'))) {
            $this->comment("Using merge base with '{$compareBranch}' as the starting point.");
            return $mergeBase;
        }

        // Fallback: last tag
        $process = Process::fromShellCommandline("git describe --tags --abbrev=0");
        $process->run();
        if ($process->isSuccessful() && trim($process->getOutput())) {
            $this->comment("Using latest tag as the starting point.");
            return trim($process->getOutput());
        }

        throw new Exception("Could not determine a starting commit. Please specify it using --from=SHA.");
    }


    /**
     * Gets the current Git branch name.
     */
    protected function getCurrentBranch(): string
    {
        $process = Process::fromShellCommandline('git rev-parse --abbrev-ref HEAD');
        $process->run();

        if (!$process->isSuccessful()) {
            return 'HEAD'; // Fallback
        }

        return trim($process->getOutput());
    }

    /**
     * Prepends the new changelog content to the existing file.
     */
    protected function writeChangelog(string $outputFile, string $newContent, ?string $version)
    {
        $version = $version ?? 'UNRELEASED';
        $sepperator = "\n --- \n\n";
        $newContent = trim($newContent) . $sepperator;

        if (File::exists($outputFile)) {
            $existingContent = File::get($outputFile);

            // Find the position after the main title (# Changelog)
            $content = preg_replace('/(#\s*Changelog)/i', "$1\n" . $newContent, $existingContent, 1, $count);

            if ($count === 0) {
                // If "# Changelog" title not found, just prepend
                $content = "# Changelog\n" . $newContent . $existingContent;
            }
        } else {
            // Create a new file
            $content = "# Changelog\n" . $newContent;
        }

        File::put($outputFile, $content);
    }
}
