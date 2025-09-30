# 🤖 Amjithk/Laravel AI Changelog Generator

[](https://www.google.com/search?q=https://packagist.org/packages/amjithk/laravel-ai-changelog)
[](https://www.google.com/search?q=https://packagist.org/packages/amjithk/laravel-ai-changelog)
[](LICENSE.md)

**Stop writing release notes manually\!** This Laravel package automatically reads your Git commit history and uses a Large Language Model (LLM) like OpenAI or Gemini to generate concise, categorized, and user-friendly changelog entries, placing them directly at the top of your `CHANGELOG.md` file.

## ✨ Features

  * **AI-Powered Summarization:** Turns technical commit messages and diffs into human-readable release notes.
  * **Intelligent Categorization:** Automatically groups changes into sections like **✨ Features**, **🐛 Fixes**, and **🚀 Improvements**.
  * **Flexible Ranging:** Generate logs based on a commit SHA, a specific tag (e.g., `v1.0.0`), or by comparing a feature branch against a base branch (e.g., `staging`).
  * **Workflow Ready:** Designed to be run on feature branches, staging branches, or directly in your CI/CD pipeline.
  * **Non-Destructive:** Prepends new content to your existing `CHANGELOG.md`.

-----

## 📥 Installation

You can install the package via Composer:

```bash
composer require amjithk/laravel-ai-changelog --dev
```

### Configuration

Publish the configuration file using the Artisan command:

```bash
php artisan vendor:publish --tag=ai-changelog-config
```

This will create a `config/ai-changelog.php` file, allowing you to customize API settings, prompts, and default branches.

### Environment Setup

The package requires an API key for the Large Language Model you choose to use.

Add your key to your `.env` file:

```env
# Example using OpenAI
AI_CHANGELOG_API_KEY="sk-your-openai-api-key-here"

# OR Example using Gemini
# AI_CHANGELOG_API_KEY="AIzaSyYourGeminiApiKeyHere"
```

-----

## 🚀 Usage

The package provides a single powerful Artisan command: `changelog:ai:generate`.

### Basic Usage: Generating from a Tag

This is the most common use case for a release. It generates changes between the specified tag (`v1.0.0`) and the current commit (`HEAD`).

```bash
# Generate changelog for all changes since the v1.0.0 tag
php artisan changelog:ai:generate --from=v1.0.0 --version=v1.1.0
```

### Workflow 1: Feature Branch Review

To generate the changelog for a feature branch (e.g., `feature/login`) by comparing it against your stable branch (`staging` is the default):

```bash
# While on your feature/login branch:
php artisan changelog:ai:generate --version="New Login Flow"
```

The package automatically finds the common ancestor between your current branch and `staging` to isolate only your feature's changes.

### Workflow 2: Explicit Comparison

If you want to compare against a different branch or need to specify the comparison base:

```bash
# Compare current branch against 'develop'
php artisan changelog:ai:generate --compare=develop --version="Feature Merge"

# Compare between two arbitrary SHAs (e.g., in a CI pipeline)
php artisan changelog:ai:generate --from=abc1234 --version="CI Build 5"
```

### Command Options

| Option | Description | Default |
| :--- | :--- | :--- |
| `--from` | The starting Git commit SHA or tag. (e.g., `v1.0.0`) | Calculated (last tag or merge-base) |
| `--compare` | The base branch to compare against when `--from` is omitted. | `staging` (from config) |
| `--version` | Manually set the version/heading for the changelog entry. | `UNRELEASED` |
| `--branch` | The specific branch to analyze (defaults to current branch). | Current Git Branch |

-----

## 🔧 Customization

You can fine-tune the AI's behavior by editing the published `config/ai-changelog.php` file.

| Config Key | Purpose |
| :--- | :--- |
| `api.key` | The AI API Key. |
| `api.model` | The specific LLM model to use (e.g., `gpt-4o-mini`, `gemini-2.5-flash`). |
| `api.url` | The API endpoint URL (for switching providers). |
| `ai_prompt_prefix` | **Crucial:** Customize the prompt to change the tone, language, or required output structure of the changelog. |
| `output.file` | Change the output file path (e.g., `RELEASES.md`). |

### Example: Changing the AI Model

To use Google's Gemini model, you would update the config file and your `.env`:

**`.env`:**

```env
AI_CHANGELOG_API_KEY="AIzaSyYourGeminiApiKeyHere"
```

**`config/ai-changelog.php`:**

```php
'api' => [
    'url' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent',
    'model' => 'gemini-2.5-flash',
    // ...
],
```

-----

## 🤝 Contributing

We welcome contributions\! If you have suggestions for new features, better Git parsing, or improved AI prompts, please submit a Pull Request.

### Development Steps

1.  Fork the repository.
2.  Create your feature branch (`git checkout -b feature/awesome-feature`).
3.  Commit your changes (`git commit -am 'Feat: Add awesome feature'`).
4.  Push to the branch (`git push origin feature/awesome-feature`).
5.  Create a new Pull Request.

## 📄 License

The Laravel AI Changelog Generator is open-sourced software licensed under the **[MIT license](LICENSE.md)**.