<?php

return [
    'api' => [
        'key' => env('AI_CHANGELOG_API_KEY', env('OPENAI_API_KEY')),
        'url' => 'https://api.openai.com/v1/chat/completions',
        'model' => 'gpt-4o-mini', // Or another preferred LLM model (e.g., gemini-2.5-flash)
        'temperature' => 0.3, // Lower is better for factual summaries
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
        You are a highly skilled technical writer. Your task is to generate a concise, user-friendly changelog from a list of Git commit messages.
        
        Rules:
        1. Summarize the changes in professional, non-technical language for end-users.
        2. Group the changes into sections: "✨ Features", "🐛 Fixes", "🚀 Improvements", and "🧹 Maintenance" (if applicable).
        3. Ignore commits related to routine maintenance, tests, or documentation unless they result in a direct user-facing change.
        4. Do not include the commit SHA or author in the final output.
        5. The entire output must be in Markdown format.
        
        Raw Commit History:
        PROMPT,
];