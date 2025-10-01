<?php

return [
    'api' => [
        // Timeout in seconds
        'timeout' => 60,

        'model' => 'gemini-2.5-flash',
        'temperature' => 0.3,
    ],

    'git' => [
        // Default branch to compare against (i.e., generate changes since this branch's HEAD)
        'default_compare_branch' => 'staging', 
    ],

    'output' => [
        'file' => 'CHANGELOG.md',
        'heading' => '## [Version Placeholder]',
    ],

    'ai_prompt_prefix' => <<<PROMPT
        You are a helpful assistant. Summarize **all git commits**, including features (feat:), fixes (fix:), chores (chore:), documentation (docs:), refactors (refactor:), and tests (test:). Do not skip any commit.  

        Format the changelog in Markdown with the following style:

        # [DATE] - <Commit type>: <Commit message summary>
        - Provide a short bullet for each commit explaining what changed.
        - Keep technical details if they are relevant for developers, otherwise summarize in plain English.
        - Do not include commit SHA or author information.
        - The entire output must be in Markdown.

        Example:

        ## 2025-10-01 - feat: add login module
        - Implemented OAuth login
        - Added "forgot password" feature

        ## 2025-10-01 - chore: update dependencies
        - Updated Laravel and PHP packages
        - Removed unused composer packages

        Use this style for all commits.
        PROMPT,

];