# 1_FORGE - Rek.ai TYPO3 Extension

This is the official TYPO3 extension for [Rek.ai](https://rek.ai) (developed in close cooperation with Rek.ai).
Rek.ai is a content recommendation service that allows to easily improve your website with personalised content. Integration in your TYPO3 website can be done by every content editor that was given access to the extensions Backend Module and Content Elements.

## Requirements

| Dependency | Version            |
|---|--------------------|
| PHP | `^8.1`             |
| TYPO3 CMS | `^12.4` or `^13.4` |

## Features

- Automatically injects the Rek.ai script into all frontend pages via a `PageRenderer` hook
- Optional **Non CSS Version** mode — disables Rek.ai's inline CSS via `data-allowinlinecss="false"`
- **Autocomplete** integration — three modes: disabled, default selector-based, or fully custom script
- **`rekai_recommendations`** content element: renders a `<div class="rek-prediction">` widget with configurable display options and recommendation source scoping (including a specific page selector)
- **`rekai_qna`** content element: renders a Q&A widget (`data-entitytype="rekai-qna"`) with configurable branch mode, tag filters, and link behaviour
- Backend configuration module under **Site > Rek.ai Configuration**

## Installation

```bash
composer require one-forge/rek-ai
```

After installation, activate the extension in the TYPO3 Extension Manager or via CLI:

```bash
vendor/bin/typo3 extension:activate one_forge_rekai
```

## Configuration

Navigate to **Web > Rek.ai Configuration** in the TYPO3 backend.

### Script Integration

| Setting | Description |
|---|---|
| **Load Scripts** | Enable to inject the Rek.ai script tag on all frontend pages |
| **Non CSS Version** | When enabled, adds `data-allowinlinecss="false"` to the script tag to disable Rek.ai's inline CSS |
| **Script URL** | Full URL to your Rek.ai script (e.g. `https://static.rekai.se/xyz.js`) |

The script tag is only rendered when both **Load Scripts** is on and a valid Script URL is provided.

### Autocomplete

| Setting | Description |
|---|---|
| **Autocomplete Mode** | `Disabled` / `Enabled with selector` / `Enabled with custom script` |
| **Autocomplete Selector** | CSS selector for the search input (e.g. `#searchform-input`), used in mode 1 |
| **Open on click** | Navigate to the selected autocomplete result immediately on click |
| **Use current language** | Filter autocomplete results by the current page language |
| **Number of results** | Number of autocomplete suggestions to show (default: 5) |
| **Custom autocomplete script** | Full custom JavaScript block, used in mode 2 |

## Content Element: Rek.ai Recommendations

Add the **Rek.ai Recommendations** content element (`rekai_recommendations`) to any page. It renders a `<div class="rek-prediction">` that the Rek.ai script hydrates at runtime.

### Content Element Fields

**Display tab**

| Field | Description |
|---|---|
| Show header | Toggle to show/hide the recommendations header |
| Header text | Heading displayed above recommendations (default: `Discover more`) |
| Title max length | Maximum character length for recommendation titles (1–99, default: 20) |
| Number of hits | How many recommendations to show (1–20, default: 5) |
| Render style | `Pills` / `List` / `Advanced` (default: `Pills`) |
| List columns | Number of columns when render style is `List` (1–6, default: 2) |

**Source tab**

| Field | Description |
|---|---|
| Root path mode | Restrict recommendations scope: `none` / `subpages` / `level` |
| Root path level | Ancestor level to use as root (only when mode is `level`, 1–10) |
| Specific pages | Page selector for a custom subtree (only when mode is `level`) |
| Exclude child nodes | Exclude child pages from recommendation scope |

**Advanced tab**

| Field | Description |
|---|---|
| Extra attributes | Additional HTML data attributes added verbatim to the widget `<div>` |

## Content Element: Rek.ai Questions and Answers

Add the **Rek.ai Questions and Answers** content element (`rekai_qna`) to any page. It renders a `<div class="rek-prediction" data-entitytype="rekai-qna">` that the Rek.ai script populates with Q&A results.

### Content Element Fields

| Field | Description |
|---|---|
| Number of hits | How many Q&A items to show (0 = no limit) |
| Branch mode | Scope: `none` / `current branch` / `specific pages` / `current page only` |
| Specific pages | Page selector for the Q&A source subtree (only when branch mode is `specific pages`) |
| Tags | Comma-separated tag filter for Q&A items |
| Hide link if same page | Do not render a link when the answer page is the current page |
| Hide link to answer page | Always hide the link to the answer page |
| Disable highlighting | Disable Rek.ai's keyword highlighting in answers |

## Extension Key & Composer Package

- **Extension key**: `one_forge_rekai`
- **Composer package**: `one-forge/rek-ai`
- **PHP namespace**: `OneForge\RekAi\*`

## License

[GNU General Public License v3.0 (GPLv3) ](LICENSE.txt)
