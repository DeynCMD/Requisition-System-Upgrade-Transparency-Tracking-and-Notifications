# Sequential Approval Flow Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Enforce sequential approval flow on Purchase Requests: Admin approves → Suppliers bid → Buyer/Admin picks winning bid → Finance approves → Buyer creates PO. Stop supplier-approve/finance-approve from happening at the same time.

**Architecture:** Use the existing `purchase_requests.status` ENUM (which already includes `finance_pending`, `finance_approved`, `finance_rejected`) and the existing `finance_status` ENUM as a finance-review gate. Add a new `select_winning_bid.php` endpoint as the missing step between supplier bidding and finance review. Tighten existing queries so finance no longer sees PRs that haven't had a winning bid selected. Gate PO creation in `buyer_po.php`.

**Tech Stack:** PHP 8.x, MySQL/MariaDB (mysqli), PHPMailer (already used), vanilla JS, SweetAlert2 (already loaded on dashboard pages).

**Spec:** `docs/superpowers/specs/2026-08-22-sequential-approval-flow-design.md`

## Global Constraints

- **No hardcoded credentials.** All email/secret lookups use `getenv('EMAIL_USER')`, `getenv('EMAIL_PASS')`, etc. The existing `send_pr_status_email.php` has a hardcoded Gmail app password — we will migrate it to `getenv()` in Task 1 so all subsequent tasks benefit.
- **Existing role names:** `BUYER`, `ADMIN`, `SUPPLIER`, `FINANCE` — no new roles.
- **Existing schema columns used:** `status` (enum), `finance_status` (enum), `finance_approved_by`, `finance_approved_at`, `rejection_reason`, `approved_by`, `approved_at`.
- **No new schema migrations.** Schema already supports the flow.
- **Frequent commits.** Each task ends with a commit.
- **Test before commit.** Each task has a manual verification step.

## File Structure

### New files
- `Admin/PHP/select_winning_bid.php` — shared API endpoint, callable by buyer or admin
- `Admin/PHP/env_loader.php` — small helper that loads `.env` into `getenv()`
- `Buyers/HTML/buyer_select_winner.php` — buyer UI to pick a winner
- `Admin/HTML/admin_select_winner.php` — admin UI to pick a winner
- `Admin/PHP/get_bidding_requests.php` — JSON endpoint powering the admin dashboard list

### Modified files
- `Admin/PHP/send_pr_status_email.php` — migrate credentials to `getenv()` + add `awaiting_finance` and `finance_rejected` email cases
- `Admin/PHP/update_request_status.php` — set `finance_status='pending'` explicitly on admin approve
- `finance/PHP/finance-budget-approvals.php` — tighten query: filter `status='finance_pending'` instead of `status='approved'`
- `Buyers/PHP/buyer_po.php` — gate PO creation on `finance_status='approved'` AND `status='finance_approved'`
- `Buyers/HTML/buyer.php` — add a "Bidding" section for `status='approved'` PRs with bids
- `Admin/HTML/Pending-approvals.html` — add a "Bidding" section listing bidding-stage PRs

### Files NOT touched
- `Supplier/PHP/supplier_api.php` — already filters `status='approved'` for bid eligibility
- `Supplier/HTML/supplier_open_requests.php` — already filters `status='approved'`
- `Supplier/HTML/supplier_my_bids.php` — supplier sees own bids regardless of stage

---

## Task 1: Migrate email credentials to env and add new email cases

**Files:**
- Modify: `Admin/PHP/send_pr_status_email.php`
- Create: `Admin/PHP/env_loader.php`
- Test: manual — check function still sends email when SMTP creds are unset (should fail safely)

**Why this is first:** Every subsequent task that sends email (`select_winning_bid.php`, `update_request_status.php`) will call this function. If creds are still hardcoded, the next commit has embedded secrets again. We also need the two new status cases (`awaiting_finance`, `finance_rejected`) added before Task 3 can use them.

### Step 1.1: Create the env loader

Create `D:\XAMPP\htdocs\THESIS\Admin\PHP\env_loader.php`:

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

Open `Admin/PHP/send_pr_status_email.php`. At the top, after the `use PHPMailer\...` lines and before the function definition, add:

```php
require_once __DIR__ . '/env_loader.php';
loadEnv(__DIR__ . '/../.env');
```

Then inside the function, change the SMTP block from hardcoded to:

```php
$mail->Host = getenv('EMAIL_HOST') ?: 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = getenv('EMAIL_USER') ?: '';
$mail->Password = getenv('EMAIL_PASS') ?: '';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = (int)(getenv('EMAIL_PORT') ?: 587);

$mail->setFrom(getenv('EMAIL_FROM') ?: 'no-reply@procurement-system.com', 'Procurement System');
```

Replace the lines currently at the top of the function:

```php
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'dane.rohan1112@gmail.com';
$mail->Password = 'qtaeffszjnlqexhc';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
// ...
$mail->setFrom('no-reply@procurement-system.com', 'Procurement System');
```

### Step 1.3: Add two new email cases

In the `switch ($status)` block of `sendPRStatusEmail`, before the `default:` case, add:

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

Note: there's already a `case 'rejected_by_finance':` that sends a similar email. We add `finance_rejected` (single word) as a clean key for the new flow. The `rejected_by_finance` key stays because finance-budget-approvals.php already uses it. Either key works.

### Step 1.4: Manual verification

- [ ] Run `php -l Admin/PHP/send_pr_status_email.php` — expected: `No syntax errors detected`
- [ ] Run `php -l Admin/PHP/env_loader.php` — expected: `No syntax errors detected`
- [ ] Verify the hardcoded username/password strings no longer appear anywhere in the file. Use `grep -n "dane.rohan\|qtaeffsz" Admin/PHP/send_pr_status_email.php` — expected: no output.
- [ ] Confirm `.env` exists at the project root with `EMAIL_USER` and `EMAIL_PASS`. Use `ls -la .env` — expected: file exists. If it does not exist, create one (do not commit it).

### Step 1.5: Commit

```bash
git add Admin/PHP/env_loader.php Admin/PHP/send_pr_status_email.php
git commit -m "feat(email): migrate creds to env, add awaiting_finance + finance_rejected cases"
```

---

## Task 2: Tighten finance query to require status='finance_pending'

**Files:**
- Modify: `finance/PHP/finance-budget-approvals.php` line 67-68 (the `get_approved_requests` query)

**Why this is second:** Once admin approves a PR today, finance sees it immediately. This task closes that loophole so finance only sees PRs that have gone through the supplier-bid stage (status transitioned to `finance_pending`). This is the smallest change with the biggest impact.

### Step 2.1: Edit the query

In `finance/PHP/finance-budget-approvals.php`, find:

```php
$sql = "
    SELECT id, pr_number, requestor_name, category, quantity, unit_price, currency, reason,
           (quantity * unit_price) AS total_amount, created_at
    FROM purchase_requests
    WHERE status = 'approved'
      AND (finance_status IS NULL OR finance_status = 'pending')
    ORDER BY created_at DESC
";
```

Replace with:

```php
$sql = "
    SELECT id, pr_number, requestor_name, category, quantity, unit_price, currency, reason,
           (quantity * unit_price) AS total_amount, created_at
    FROM purchase_requests
    WHERE status = 'finance_pending'
      AND finance_status = 'pending'
    ORDER BY created_at DESC
";
```

### Step 2.2: Tighten the approve guard

In the same file, the `finance_approve` handler at line ~98 currently reads:

```php
WHERE pr.id = ? AND pr.status = 'approved'
```

Change to:

```php
WHERE pr.id = ? AND pr.status = 'finance_pending' AND pr.finance_status = 'pending'
```

If the PR is not in the right state, the existing `throw new Exception("Request not found or not approved");` will fire — but the message will be misleading. Update the error message in the same `try` block (a few lines below) from:

```php
if (!$pr)
    throw new Exception("Request not found or not approved");
```

to:

```php
if (!$pr)
    throw new Exception("Request not found or not yet ready for finance review (supplier must have a winning bid selected first)");
```

### Step 2.3: Tighten the reject guard too

In the `finance_reject` block (line ~270), the SELECT reads:

```php
WHERE pr.id = ?
```

This is too permissive — it lets finance reject a PR that's still in the bidding stage. Tighten it to:

```php
WHERE pr.id = ? AND pr.status = 'finance_pending' AND pr.finance_status = 'pending'
```

And update the error message in the same block from:

```php
throw new Exception('Request not found');
```

to:

```php
throw new Exception('Request not found or not yet ready for finance review (supplier must have a winning bid selected first)');
```

### Step 2.4: Manual verification

- [ ] Run `php -l finance/PHP/finance-budget-approvals.php` — expected: no syntax errors
- [ ] Confirm only the intended lines changed. Use `grep -n "status = 'approved'" finance/PHP/finance-budget-approvals.php` — expected: no output.
- [ ] Confirm the new query exists. Use `grep -n "finance_pending" finance/PHP/finance-budget-approvals.php` — expected: 3+ matches.

### Step 2.5: Commit

```bash
git add finance/PHP/finance-budget-approvals.php
git commit -m "feat(finance): only review PRs with a selected winning bid"
```

---

## Task 3: Create the select_winning_bid.php endpoint

**Files:**
- Create: `Admin/PHP/select_winning_bid.php`
- Test: manual via `curl` or browser

**Why third:** This is the new step in the flow. After admin approves + suppliers bid, this endpoint atomically picks a winner and transitions the PR into the finance-reviewable state.

### Step 3.1: Create the file

Create `D:\XAMPP\htdocs\THESIS\Admin\PHP\select_winning_bid.php`:

```php
<?php
// select_winning_bid.php
// Atomic transition: status='approved' -> status='finance_pending'.
// Callable by BUYER and ADMIN roles. Marks the chosen supplier bid as
// 'selected' and rejects the rest.

ob_start();
header('Content-Type: application/json; charset=utf-8');
while (ob_get_level() > 0) ob_end_clean();
ini_set('display_errors', '0');
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired']);
    exit;
}

$role = $_SESSION['role'];
if ($role !== 'BUYER' && $role !== 'ADMIN') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Only Buyer or Admin can select a winning bid.']);
    exit;
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/log_activity.php';
require_once __DIR__ . '/send_pr_status_email.php';

$prId         = (int)($_POST['pr_id'] ?? 0);
$winningBidId = (int)($_POST['winning_bid_id'] ?? 0);

if ($prId <= 0 || $winningBidId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing pr_id or winning_bid_id']);
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn->begin_transaction();

    // Lock the PR row to prevent two concurrent selections.
    $stmt = $conn->prepare("
        SELECT id, pr_number, status, finance_status, requestor_name, user_id
        FROM purchase_requests
        WHERE id = ?
        FOR UPDATE
    ");
    $stmt->bind_param('i', $prId);
    $stmt->execute();
    $pr = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$pr) {
        throw new Exception('Purchase request not found');
    }
    if ($pr['status'] !== 'approved') {
        throw new Exception('This PR is not in the bidding stage (current status: ' . $pr['status'] . ')');
    }

    // Validate the chosen bid exists and belongs to this PR, and is still pending.
    $stmt = $conn->prepare("
        SELECT id, supplier_id, status FROM supplier_bids
        WHERE id = ? AND pr_id = ? AND status = 'pending'
    ");
    $stmt->bind_param('ii', $winningBidId, $prId);
    $stmt->execute();
    $bid = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$bid) {
        throw new Exception('Invalid bid for this PR or bid is not pending');
    }

    // Reject all other bids on this PR.
    $stmt = $conn->prepare("
        UPDATE supplier_bids
        SET status = 'rejected', updated_at = NOW()
        WHERE pr_id = ? AND id != ? AND status = 'pending'
    ");
    $stmt->bind_param('ii', $prId, $winningBidId);
    $stmt->execute();
    $stmt->close();

    // Mark winning bid as selected.
    $stmt = $conn->prepare("
        UPDATE supplier_bids
        SET status = 'selected', updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param('i', $winningBidId);
    $stmt->execute();
    $stmt->close();

    // Transition PR status. The WHERE guard ensures we don't double-transition.
    $stmt = $conn->prepare("
        UPDATE purchase_requests
        SET status = 'finance_pending', finance_status = 'pending', updated_at = NOW()
        WHERE id = ? AND status = 'approved'
    ");
    $stmt->bind_param('i', $prId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected === 0) {
        // Another caller won the race.
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'This PR was already processed by someone else']);
        exit;
    }

    $conn->commit();

    // Log activity.
    if (function_exists('logActivity')) {
        $actor = $_SESSION['fullname'] ?? $_SESSION['username'] ?? $role;
        logActivity(
            $conn,
            'winning_bid_selected',
            $actor,
            "Winning bid selected for PR {$pr['pr_number']} (bid #$winningBidId)",
            "Bid #$winningBidId marked selected; PR moved to finance review",
            null,
            $pr['pr_number']
        );
    }

    // Send notification email to requestor.
    if (!empty($pr['user_id'])) {
        $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->bind_param('i', $pr['user_id']);
        $stmt->execute();
        $emailRow = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $requestorEmail = $emailRow['email'] ?? '';
        if ($requestorEmail && function_exists('sendPRStatusEmail')) {
            @sendPRStatusEmail(
                $conn,
                $pr['pr_number'],
                'awaiting_finance',
                $requestorEmail,
                $pr['requestor_name']
            );
        }
    }

    echo json_encode([
        'success'     => true,
        'message'     => 'Winning bid selected. PR is now awaiting finance review.',
        'pr_number'   => $pr['pr_number'],
        'winning_bid' => $winningBidId,
    ]);
} catch (Exception $e) {
    if ($conn->in_transaction) {
        $conn->rollback();
    }
    error_log("select_winning_bid error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
```

### Step 3.2: Manual verification

- [ ] Run `php -l Admin/PHP/select_winning_bid.php` — expected: no syntax errors
- [ ] Verify it loads: open `http://localhost/THESIS/Admin/PHP/select_winning_bid.php` in a browser without session — expected: `{"success":false,"message":"Session expired"}`

### Step 3.3: Commit

```bash
git add Admin/PHP/select_winning_bid.php
git commit -m "feat: add select_winning_bid endpoint for buyer/admin to release PR to finance"
```

---

## Task 4: Gate buyer_po.php on finance approval

**Files:**
- Modify: `Buyers/PHP/buyer_po.php` line 44

**Why fourth:** Once finance can no longer see un-bid PRs (Task 2) and the winning-bid step exists (Task 3), the last leak is PO creation. Currently `buyer_po.php` accepts `status='approved'` PRs. Tighten it.

### Step 4.1: Tighten the PR lookup

In `Buyers/PHP/buyer_po.php`, find the line:

```php
$pr = $conn->query("SELECT * FROM purchase_requests WHERE id=$prId AND status='approved'")->fetch_assoc();
```

Replace with:

```php
$pr = $conn->query("
    SELECT * FROM purchase_requests
    WHERE id=$prId
      AND status='finance_approved'
      AND finance_status='approved'
")->fetch_assoc();
```

### Step 4.2: Update the error message

Right below it, change:

```php
if (!$pr) {
    echo json_encode(['success' => false, 'message' => 'PR not found or not approved']); exit;
}
```

to:

```php
if (!$pr) {
    echo json_encode([
        'success' => false,
        'message' => 'PR not found or not yet approved by Finance. A winning bid must be selected and Finance must approve before creating a PO.'
    ]);
    exit;
}
```

### Step 4.3: Manual verification

- [ ] Run `php -l Buyers/PHP/buyer_po.php` — expected: no syntax errors
- [ ] Confirm the new check exists: `grep -n "finance_approved" Buyers/PHP/buyer_po.php` — expected: 1 match.

### Step 4.4: Commit

```bash
git add Buyers/PHP/buyer_po.php
git commit -m "feat(buyer): block PO creation until finance has approved"
```

---

## Task 5: Add a "Select Winning Bid" UI for buyers

**Files:**
- Create: `Buyers/HTML/buyer_select_winner.php`

**Why fifth:** Tasks 2-4 wire up the backend gates. The buyer needs a UI to invoke the new endpoint.

### Step 5.1: Create the file

Create `D:\XAMPP\htdocs\THESIS\Buyers\HTML\buyer_select_winner.php`:

```php
<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'BUYER') {
    header('Location: ../../Admin/HTML/ZE-Electronics.php');
    exit;
}
require_once '../../Admin/PHP/db.php';

$prId = (int)($_GET['pr_id'] ?? 0);
if ($prId <= 0) {
    header('Location: buyer.php');
    exit;
}

// Load the PR and verify it is in the bidding stage.
$pr = $conn->query("
    SELECT id, pr_number, mpn, manufacturer, category, subcategory, quantity, currency, reason, urgency, required_by, status
    FROM purchase_requests
    WHERE id = $prId AND status = 'approved'
")->fetch_assoc();

if (!$pr) {
    $_SESSION['flash_error'] = 'This PR is not in the bidding stage.';
    header('Location: buyer.php');
    exit;
}

// Load bids for this PR.
$bids = $conn->query("
    SELECT sb.id, sb.unit_price, sb.delivery_date, sb.notes, sb.status, sb.created_at,
           s.id AS supplier_id, s.name AS supplier_name, s.email AS supplier_email
    FROM supplier_bids sb
    JOIN suppliers s ON s.id = sb.supplier_id
    WHERE sb.pr_id = $prId
    ORDER BY sb.unit_price ASC, sb.created_at ASC
")->fetch_all(MYSQLI_ASSOC);

$hasBid = !empty($bids);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <title>Select Winning Bid — <?= htmlspecialchars($pr['pr_number']) ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { font-family: "Segoe UI", sans-serif; background: #12121a; color: #e0e0ff; margin: 0; padding: 24px; }
    .container { max-width: 1100px; margin: 0 auto; }
    h1 { color: #22c55e; font-size: 1.6rem; margin-bottom: 8px; }
    .pr-strip { background: #1e1e2e; border: 1px solid #2d2d44; border-radius: 12px; padding: 18px 22px; margin-bottom: 22px; }
    .pr-strip .row { display: flex; flex-wrap: wrap; gap: 16px 32px; margin-top: 8px; }
    .pr-strip .field { font-size: .85rem; }
    .pr-strip .field label { display: block; color: #9a9ab5; font-size: .72rem; text-transform: uppercase; margin-bottom: 2px; }
    .pr-strip .field .val { color: #e0e0ff; font-weight: 600; }
    table { width: 100%; border-collapse: collapse; background: #1e1e2e; border-radius: 12px; overflow: hidden; }
    th, td { padding: 14px 16px; text-align: left; border-bottom: 1px solid #2d2d44; font-size: .88rem; }
    th { background: #1a1a2e; color: #9a9ab5; font-size: .72rem; text-transform: uppercase; letter-spacing: .4px; }
    tr.selected-winner td { background: rgba(34, 197, 94, .08); }
    .actions { display: flex; gap: 10px; }
    .btn { padding: 9px 16px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: .85rem; }
    .btn-primary { background: #22c55e; color: #fff; }
    .btn-primary:hover { background: #16a34a; }
    .btn-primary:disabled { background: #444; color: #888; cursor: not-allowed; }
    .btn-back { background: #2d2d44; color: #e0e0ff; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; padding: 9px 16px; border-radius: 8px; font-weight: 600; font-size: .85rem; }
    .empty-state { background: #1e1e2e; border: 1px dashed #2d2d44; border-radius: 12px; padding: 48px; text-align: center; color: #9a9ab5; }
    .empty-state i { font-size: 3rem; color: #444; margin-bottom: 14px; display: block; }
    .reason-box { background: #1e1e2e; border-left: 3px solid #4a4a6a; padding: 10px 14px; margin-bottom: 18px; font-size: .85rem; color: #9a9ab5; font-style: italic; }
  </style>
</head>
<body>
<div class="container">
  <a class="btn-back" href="buyer.php" style="margin-bottom: 16px;"><i class="fas fa-arrow-left"></i> Back to Buyer Dashboard</a>
  <h1><i class="fas fa-gavel"></i> Select Winning Bid</h1>
  <p style="color:#9a9ab5;margin-top:4px;">Choose the winning supplier bid. After selection, this PR moves to Finance for review.</p>

  <div class="pr-strip">
    <div style="font-size:.7rem;color:#9a9ab5;text-transform:uppercase;">PR Number</div>
    <div style="font-size:1.15rem;font-weight:800;color:#22c55e;"><?= htmlspecialchars($pr['pr_number']) ?></div>
    <div class="row">
      <div class="field"><label>MPN</label><div class="val"><?= htmlspecialchars($pr['mpn'] ?? '—') ?></div></div>
      <div class="field"><label>Manufacturer</label><div class="val"><?= htmlspecialchars($pr['manufacturer'] ?? '—') ?></div></div>
      <div class="field"><label>Category</label><div class="val"><?= htmlspecialchars($pr['category'] ?? '—') ?></div></div>
      <div class="field"><label>Quantity</label><div class="val"><?= (int)$pr['quantity'] ?> units</div></div>
      <div class="field"><label>Currency</label><div class="val"><?= htmlspecialchars($pr['currency']) ?></div></div>
      <div class="field"><label>Status</label><div class="val" style="color:#fbbf24;">Awaiting Supplier Bids</div></div>
    </div>
  </div>

  <?php if ($pr['reason']): ?>
    <div class="reason-box"><i class="fas fa-quote-left" style="color:#555;margin-right:6px"></i><?= htmlspecialchars($pr['reason']) ?></div>
  <?php endif; ?>

  <?php if (!$hasBid): ?>
    <div class="empty-state">
      <i class="fas fa-inbox"></i>
      <p>No supplier bids have been submitted yet.</p>
      <p style="font-size:.82rem;">Suppliers must submit at least one bid before a winner can be selected.</p>
    </div>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Supplier</th>
          <th>Unit Price</th>
          <th>Delivery Date</th>
          <th>Notes</th>
          <th>Bid Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($bids as $bid): ?>
        <tr>
          <td><strong><?= htmlspecialchars($bid['supplier_name']) ?></strong><br><span style="font-size:.74rem;color:#9a9ab5;"><?= htmlspecialchars($bid['supplier_email']) ?></span></td>
          <td><strong><?= htmlspecialchars($pr['currency']) ?> <?= number_format((float)$bid['unit_price'], 4) ?></strong></td>
          <td><?= htmlspecialchars($bid['delivery_date']) ?></td>
          <td style="font-size:.82rem;color:#9a9ab5;"><?= htmlspecialchars($bid['notes'] ?? '—') ?></td>
          <td>
            <?php
              $sc = ['pending' => '#fbbf24', 'selected' => '#4ade80', 'rejected' => '#f87171'][$bid['status']] ?? '#9a9ab5';
            ?>
            <span style="background:<?= $sc ?>22;color:<?= $sc ?>;border:1px solid <?= $sc ?>44;font-size:.72rem;font-weight:700;padding:2px 9px;border-radius:20px;text-transform:uppercase;">
              <?= htmlspecialchars($bid['status']) ?>
            </span>
          </td>
          <td>
            <?php if ($bid['status'] === 'pending'): ?>
              <button class="btn btn-primary" onclick="selectWinner(<?= (int)$bid['id'] ?>, '<?= htmlspecialchars($bid['supplier_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($pr['pr_number'], ENT_QUOTES) ?>')">
                <i class="fas fa-check"></i> Select as Winner
              </button>
            <?php else: ?>
              <span style="color:#9a9ab5;font-size:.82rem;">—</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<script>
async function selectWinner(bidId, supplierName, prNumber) {
  const result = await Swal.fire({
    title: 'Select this bid as winner?',
    html: `
      <p>You are about to select <strong>${supplierName}</strong> as the winning bid for <strong>${prNumber}</strong>.</p>
      <p style="font-size:.85rem;color:#9a9ab5;margin-top:10px;">This will:
        <br>• mark all other bids as rejected
        <br>• move this PR to Finance for review
        <br>• notify the requestor by email</p>
    `,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#22c55e',
    cancelButtonColor: '#444',
    confirmButtonText: 'Yes, select winner',
    cancelButtonText: 'Cancel',
  });
  if (!result.isConfirmed) return;

  const fd = new URLSearchParams({ pr_id: <?= (int)$prId ?>, winning_bid_id: bidId });
  try {
    const res = await fetch('../../Admin/PHP/select_winning_bid.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: fd,
    });
    const data = await res.json();
    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Winner selected',
        text: data.message,
        confirmButtonColor: '#22c55e',
      }).then(() => { window.location.href = 'buyer.php'; });
    } else {
      Swal.fire({ icon: 'error', title: 'Failed', text: data.message, confirmButtonColor: '#ef4444' });
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Network error', text: err.message, confirmButtonColor: '#ef4444' });
  }
}
</script>
</body>
</html>
```

### Step 5.2: Manual verification

- [ ] Run `php -l Buyers/HTML/buyer_select_winner.php` — expected: no syntax errors
- [ ] Open `http://localhost/THESIS/Buyers/HTML/buyer_select_winner.php?pr_id=1` (with a real PR id from your DB) while logged in as BUYER — expected: shows PR details + bid table or empty state
- [ ] Click "Select as Winner" on a pending bid — expected: confirmation modal, then success, then redirect to `buyer.php`

### Step 5.3: Commit

```bash
git add Buyers/HTML/buyer_select_winner.php
git commit -m "feat(buyer): add UI to select winning supplier bid"
```

---

## Task 6: Add a "Select Winning Bid" UI for admins

**Files:**
- Create: `Admin/HTML/admin_select_winner.php`

**Why sixth:** Buyers get the same capability but admins should also be able to pick a winner — that's what the spec says ("buyer or admin can both").

### Step 6.1: Create the file

Create `D:\XAMPP\htdocs\THESIS\Admin\HTML\admin_select_winner.php`. The contents are **identical** to `buyer_select_winner.php` except for these five substitutions:

1. Role check: `$_SESSION['role'] !== 'ADMIN'`
2. Auth-fail redirect: `header('Location: ZE-Electronics.php')`
3. Missing-PR redirect: `header('Location: Pending-approvals.php')`
4. Back-link `href` and label text
5. Title stays the same

Apply each change to the file from Task 5.

### Step 6.2: Manual verification

- [ ] Run `php -l Admin/HTML/admin_select_winner.php` — expected: no syntax errors
- [ ] Confirm `grep -n "role.*ADMIN" Admin/HTML/admin_select_winner.php` returns the auth check
- [ ] Confirm `grep -n "Pending-approvals" Admin/HTML/admin_select_winner.php` returns the back-link target
- [ ] Open the page while logged in as ADMIN — expected: works identically to the buyer version

### Step 6.3: Commit

```bash
git add Admin/HTML/admin_select_winner.php
git commit -m "feat(admin): add UI to select winning supplier bid"
```

---

## Task 7: Wire "Select Winning Bid" links into buyer and admin dashboards

**Files:**
- Modify: `Buyers/HTML/buyer.php`
- Modify: `Admin/HTML/Pending-approvals.html`
- Create: `Admin/PHP/get_bidding_requests.php`

**Why seventh:** The new pages exist but nobody navigates to them yet. Add entry points.

### Step 7.1: Add a bidding section to buyer.php

In `Buyers/HTML/buyer.php`, after the existing `$requests = ...` query at the top, add a second query for bidding-stage PRs:

```php
// PRs in the bidding stage (admin approved, awaiting winner selection)
$bidding_requests = $conn->query("
    SELECT
        pr.id, pr.pr_number, pr.mpn, pr.quantity, pr.currency,
        (SELECT COUNT(*) FROM supplier_bids sb WHERE sb.pr_id = pr.id) AS total_bids
    FROM purchase_requests pr
    WHERE pr.status = 'approved'
      AND pr.finance_status = 'pending'
    ORDER BY pr.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
```

Then in the HTML body (near the page header), add a section listing `$bidding_requests` with a "Select Winner" button per row pointing to `buyer_select_winner.php?pr_id=<?= $row['id'] ?>`. Disable the button when `total_bids === 0` and label it "No bids yet".

### Step 7.2: Create get_bidding_requests.php

Create `D:\XAMPP\htdocs\THESIS\Admin\PHP\get_bidding_requests.php`:

```php
<?php
ob_start();
header('Content-Type: application/json; charset=utf-8');
while (ob_get_level() > 0) ob_end_clean();
ini_set('display_errors', '0');
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'ze_electronic');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB error']);
    exit;
}
$conn->set_charset('utf8mb4');

$rows = $conn->query("
    SELECT
        pr.id, pr.pr_number, pr.mpn, pr.manufacturer, pr.category, pr.subcategory,
        pr.quantity, pr.currency, pr.urgency, pr.requestor_name, pr.created_at,
        (SELECT COUNT(*) FROM supplier_bids sb WHERE sb.pr_id = pr.id) AS total_bids
    FROM purchase_requests pr
    WHERE pr.status = 'approved'
      AND pr.finance_status = 'pending'
    ORDER BY pr.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

echo json_encode(['success' => true, 'requests' => $rows]);
$conn->close();
```

### Step 7.3: Add a bidding section to Pending-approvals.html

The existing `Pending-approvals.html` (note: `.html`) renders its pending-approvals table client-side via `pending_approval.js` calling `get_pending_requests.php`. Mirror the same pattern for the bidding-stage table:

1. In `Pending-approvals.html`, add a new `<section class="table-card">` after the existing pending-approvals table, with id `bidding-section`. Include a `<table id="biddingTable">` and an empty state.
2. In `Admin/JS/pending_approval.js`, add a `loadBiddingRequests()` function that fetches `../PHP/get_bidding_requests.php` and renders rows into `#biddingTable` with an action cell containing `<a class="select-winner-btn" href="admin_select_winner.php?pr_id=${req.id}">Select Winner</a>`.
3. Call `loadBiddingRequests()` from the existing `DOMContentLoaded` handler.

If the JS refactor is too involved for a clean commit, the simpler alternative is to add a single `<a href="admin_bidding_list.php">View PRs awaiting winner selection</a>` link in the sidebar that points to a small list page. For demo impact, the inline section is preferred.

### Step 7.4: Manual verification

- [ ] Run `php -l` on every modified/new file
- [ ] As BUYER: log in, see the new "Bidding" section in `buyer.php`, click "Select Winner" — lands on `buyer_select_winner.php`
- [ ] As ADMIN: log in, see the new section in `Pending-approvals.php`, click "Select Winner" — lands on `admin_select_winner.php`

### Step 7.5: Commit

```bash
git add Buyers/HTML/buyer.php Admin/HTML/Pending-approvals.html Admin/HTML/Pending-approvals.php Admin/PHP/get_bidding_requests.php Admin/JS/pending_approval.js
git commit -m "feat(dashboards): surface bidding-stage PRs to buyer and admin"
```

(Include only the files that actually changed in your environment.)

---

## Task 8: Ensure admin approval sets finance_status='pending' explicitly

**Files:**
- Modify: `Admin/PHP/update_request_status.php` lines 64-72

**Why last:** Already the default per the schema, but `update_request_status.php` doesn't explicitly set `finance_status`. Belt and suspenders: write it explicitly so future devs reading the code understand the intent.

### Step 8.1: Update the approve SQL

Find the existing query:

```php
$stmt = $conn->prepare("
    UPDATE purchase_requests 
    SET status = 'approved', 
        approved_by = ?, 
        approved_at = NOW()
    WHERE id = ? AND status = 'PENDING'
");
```

Replace with:

```php
$stmt = $conn->prepare("
    UPDATE purchase_requests
    SET status = 'approved',
        finance_status = 'pending',
        approved_by = ?,
        approved_at = NOW()
    WHERE id = ? AND status = 'PENDING'
");
```

### Step 8.2: Manual verification

- [ ] Run `php -l Admin/PHP/update_request_status.php` — expected: no syntax errors
- [ ] `grep -n "finance_status" Admin/PHP/update_request_status.php` — expected: 1+ matches

### Step 8.3: Commit

```bash
git add Admin/PHP/update_request_status.php
git commit -m "feat(admin): explicitly set finance_status=pending on admin approve"
```

---

## Task 9: End-to-end manual test

**No code changes.** Verify the entire flow with a real PR.

### Test 1: Happy path

- [ ] Requestor logs in, creates a PR with status `PENDING`
- [ ] Admin opens `Pending-approvals.php`, clicks Approve. Expected: PR moves to `status='approved'`. Email `approved_by_approver` sent.
- [ ] As FINANCE: open `finance-budget-approvals.php` (or its HTML page). **Expected: this PR does NOT appear in the list** (gated by Task 2).
- [ ] As SUPPLIER: log in, see the PR in Open Requests, submit a bid.
- [ ] As another SUPPLIER: log in, submit another bid (different price).
- [ ] As BUYER: log in, open `buyer.php`, click "Select Winner" on this PR. Expected: lands on `buyer_select_winner.php`, shows two bids.
- [ ] Click "Select as Winner" on one bid. Confirm. Expected: success modal, redirect to `buyer.php`.
- [ ] Email `awaiting_finance` sent to requestor.
- [ ] As FINANCE: refresh the page. **Expected: this PR now appears** (status is `finance_pending`).
- [ ] Click Approve. Confirm. Expected: success, budget deducted, PR moves to `finance_approved`.
- [ ] As BUYER: `buyer.php` now shows this PR in the create-PO list.
- [ ] Create the PO. Expected: success.

### Test 2: Admin selects the winner instead of buyer

- [ ] Repeat Test 1 but in the "Select Winner" step, log in as ADMIN instead of BUYER. Expected: same outcome.

### Test 3: PO creation blocked before finance approves

- [ ] Admin approves a fresh PR, supplier bids, buyer/admin selects winner (status now `finance_pending`).
- [ ] As BUYER: directly POST to `buyer_po.php` with `pr_id` and a split-lines payload.
- [ ] **Expected: error message "PR not found or not yet approved by Finance..."** (Task 4 gate working)

### Test 4: Finance rejection

- [ ] Reach status `finance_pending`, finance clicks Reject, provides reason ≥ 5 chars.
- [ ] Expected: status becomes `finance_rejected`, `finance_status='rejected'`, `rejection_reason` saved, email `rejected_by_finance` sent.

### Test 5: Selecting winner with no bids

- [ ] Admin approves a PR, no supplier has bid yet.
- [ ] As BUYER: navigate to `buyer_select_winner.php?pr_id=<id>`.
- [ ] Expected: empty state, no buttons to click.
- [ ] As ADMIN: navigate to `admin_select_winner.php?pr_id=<id>`.
- [ ] Same expected behavior.

### Test 6: Concurrent winner selection (manual approximation)

- [ ] Open `buyer_select_winner.php?pr_id=X` in two browsers as two different buyers.
- [ ] Both click "Select Winner" on the same bid within seconds.
- [ ] Expected: only one succeeds. The other gets "This PR was already processed by someone else" (Task 3 transaction guard).

### Test 7: Static checks

- [ ] Run `php -l` on every PHP file touched. Expected: all pass.
- [ ] Run `grep -rn "dane.rohan\|qtaeffsz" Admin/PHP/ Buyers/PHP/ finance/PHP/` — expected: no output (secrets no longer hardcoded).
- [ ] Run `grep -rn "status = 'approved'" finance/PHP/` — expected: no matches in finance queries (Task 2).
- [ ] Run `grep -n "status='approved'" Buyers/PHP/buyer_po.php` — expected: no matches (Task 4).

### Step 9.1: Final commit (no code changes unless fixes were needed)

If any test step revealed a bug, fix it as a fixup commit:

```bash
git add -A
git commit -m "fix: end-to-end test fixes"
```

---

## Spec coverage check

| Spec requirement | Covered by |
|---|---|
| Admin approves → suppliers see PR | Task 8 (already working, made explicit) |
| Finance does NOT see PR until winner selected | Task 2 (tightened query) |
| Buyer or admin selects winning bid | Tasks 3, 5, 6 (endpoint + UIs) |
| Finance approves/rejects with email | Already working (existing endpoint, no changes) |
| Buyer cannot create PO before finance | Task 4 (gate) |
| Email cases `awaiting_finance`, `finance_rejected` | Task 1 (added to `send_pr_status_email.php`) |
| No hardcoded credentials | Task 1 (migrated to `getenv()`) |
| Concurrent winner selection safe | Task 3 (transaction + WHERE guard) |

All spec requirements are covered.

## Self-review

**Placeholder scan:** No "TBD" or "TODO" placeholders. All code blocks contain real code.

**Type consistency:** The endpoint path `Admin/PHP/select_winning_bid.php` is referenced consistently across Tasks 3, 5, 6, 7. The endpoint signature `POST {pr_id, winning_bid_id}` is identical in the PHP endpoint and the JS callers.

**Spec coverage:** All 7 spec requirements traced to specific tasks above.

**Open question not addressed:** Should the rejected bidders receive a notification email? Currently the spec says no. Not addressed in plan; flagged as deferred in spec.

Plan complete.
