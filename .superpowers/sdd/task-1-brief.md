# Task 1 Brief: Migrate email credentials to env and add new email cases

**Project:** THESIS capstone (D:\XAMPP\htdocs\THESIS) — PHP procurement system.
**Branch:** main
**Plan:** `docs/superpowers/plans/2026-08-22-sequential-approval-flow.md`

## Why this is first

Every subsequent task that sends email (`select_winning_bid.php`, `update_request_status.php`) will call `sendPRStatusEmail()`. If credentials are still hardcoded, the next commit embeds secrets again. We also need two new status cases (`awaiting_finance`, `finance_rejected`) added before Task 3 can use them.

## Files

- Create: `D:\XAMPP\htdocs\THESIS\Admin\PHP\env_loader.php`
- Modify: `D:\XAMPP\htdocs\THESIS\Admin\PHP\send_pr_status_email.php`
- Read but do NOT modify: `D:\XAMPP\htdocs\THESIS\.env` (already exists at the project root; contains `EMAIL_USER`, `EMAIL_PASS`, `EMAIL_HOST`, `EMAIL_PORT`, `EMAIL_FROM`)

## Step-by-step instructions

### Step 1.1: Create the env loader

Create `Admin/PHP/env_loader.php` with EXACTLY this content:

```php
<?php
// env_loader.php — minimal .env loader for the procurement system.
// Reads KEY=VALUE lines from a .env file at the project root and
// populates getenv(). Already-set environment variables are NOT
// overwritten (so prod / XAMPP env takes precedence over .env).
//
// Usage:
//   require_once __DIR__ . '/env_loader.php';
//   loadEnv(__DIR__ . '/../.env');

function loadEnv(string $path): void
{
    if (!is_readable($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        // Strip surrounding quotes if present
        if (strlen($value) >= 2
            && (($value[0] === '"' && substr($value, -1) === '"')
             || ($value[0] === "'" && substr($value, -1) === "'"))) {
            $value = substr($value, 1, -1);
        }
        if (getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}
```

### Step 1.2: Wire env loader into send_pr_status_email.php

Open `Admin/PHP/send_pr_status_email.php`. At the top, after the `use PHPMailer\...` lines (around line 11) and before the `function sendPRStatusEmail` definition, add these two lines:

```php
require_once __DIR__ . '/env_loader.php';
loadEnv(__DIR__ . '/../.env');
```

Then inside the function (currently lines 26-33), replace the SMTP block:

FROM:
```php
        // SMTP settings - CHANGE THESE TO YOUR REAL VALUES
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'dane.rohan1112@gmail.com';          // ← Your Gmail address
        $mail->Password = 'qtaeffszjnlqexhc';                  // ← Your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
```

TO:
```php
        // SMTP settings - loaded from .env via env_loader.php
        $mail->isSMTP();
        $mail->Host = getenv('EMAIL_HOST') ?: 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = getenv('EMAIL_USER') ?: '';
        $mail->Password = getenv('EMAIL_PASS') ?: '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int)(getenv('EMAIL_PORT') ?: 587);
```

Then find the line `$mail->setFrom('no-reply@procurement-system.com', 'Procurement System');` and change it to:

```php
        $mail->setFrom(getenv('EMAIL_FROM') ?: 'no-reply@procurement-system.com', 'Procurement System');
```

### Step 1.3: Add two new email cases

In the `switch ($status)` block of `sendPRStatusEmail`, BEFORE the `default:` case (currently around line 111), add these two cases:

```php
            case 'awaiting_finance':
                $subject = "Your Purchase Request $pr_number is now in Finance review";
                $body = "
                    <h2>Finance Review Started</h2>
                    <p>Dear {$requestor_name},</p>
                    <p>A winning supplier bid has been selected for your purchase request <strong>{$pr_number}</strong>.</p>
                    <p>It is now with the Finance team for final review and budget approval.</p>
                    <p>You will be notified when Finance acts on it.</p>
                    <p>Thank you,<br>Procurement Team</p>
                ";
                break;

            case 'finance_rejected':
                $subject = "❌ Your Purchase Request $pr_number was REJECTED by Finance";
                $body = "
                    <h2 style='color: #f87171;'>❌ Finance Rejection Notice</h2>
                    <p>Dear {$requestor_name},</p>
                    <p>We regret to inform you that your purchase request <strong>{$pr_number}</strong> has been <strong>rejected by Finance</strong> after supplier bidding.</p>
                    <p>Please contact the Finance department for more details about the rejection reason.</p>
                    <p>You may submit a new request if needed.</p>
                    <p>Thank you,<br>Procurement Team</p>
                ";
                break;
```

The existing `rejected_by_finance` case stays — finance-budget-approvals.php uses it.

## Manual verification (REQUIRED before commit)

Run these commands and check the expected output:

1. `php -l Admin/PHP/send_pr_status_email.php` → expected: `No syntax errors detected`
2. `php -l Admin/PHP/env_loader.php` → expected: `No syntax errors detected`
3. `grep -n "dane.rohan\|qtaeffsz" Admin/PHP/send_pr_status_email.php` → expected: NO output (no hardcoded creds)
4. `grep -n "loadEnv\|env_loader" Admin/PHP/send_pr_status_email.php` → expected: 2 matches (the require and the call)
5. `grep -n "awaiting_finance\|finance_rejected" Admin/PHP/send_pr_status_email.php` → expected: 4+ matches (2 cases × 2 references each, plus the case labels)
6. `php -r "require 'Admin/PHP/env_loader.php'; loadEnv(__DIR__ . '/.env'); echo getenv('EMAIL_USER'), PHP_EOL;"` (run from project root) → expected: `dane.rohan1112@gmail.com`

If any check fails, fix the file and re-verify before committing.

## Commit

```bash
git add Admin/PHP/env_loader.php Admin/PHP/send_pr_status_email.php
git commit -m "feat(email): migrate creds to env, add awaiting_finance + finance_rejected cases"
```

Do NOT add `.env` to git (it's gitignored). Do NOT add any other files.

## Report

Write your final report to `D:\XAMPP\htdocs\THESIS\.superpowers\sdd\task-1-report.md`. Include:
- Status (DONE / DONE_WITH_CONCERNS / NEEDS_CONTEXT / BLOCKED)
- The full commit hash (`git rev-parse HEAD` after the commit)
- One-line summary of what you did
- Test evidence: the exact commands you ran and their output (paste verbatim from your terminal)
- Any concerns

Then return to me: status, commit hash, and a one-line test summary.
