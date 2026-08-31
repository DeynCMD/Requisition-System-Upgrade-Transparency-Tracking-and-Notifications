# Sequential Approval Flow Design

**Date:** 2026-08-22
**Status:** Draft, awaiting user approval
**Author:** Claude (brainstorming session)

## Problem

Today, when an Admin/Approver approves a Purchase Request (PR), the request becomes visible to **Supplier** and **Finance** at the same time. The system has no gating between supplier bidding and finance review. Buyers can also create a PO immediately after admin approval, before finance has had a chance to review the supplier's bid.

The email notification function (`Admin/PHP/send_pr_status_email.php`) already references a multi-stage flow (`approved_by_approver`, `approved_by_finance`, `rejected_by_finance`), but the database/application does not actually enforce these stages — the templates were written ahead of the gating logic.

### Existing schema (already in place)

Investigation revealed the database already has the columns needed:

- `purchase_requests.status` ENUM: `PENDING`, `rejected`, `approved`, `finance_pending`, `finance_approved`, `finance_rejected`
- `purchase_requests.finance_status` ENUM: `pending`, `approved`, `rejected`
- `purchase_requests.finance_approved_by`, `finance_approved_at` columns
- A working finance approval endpoint at `finance/PHP/finance-budget-approvals.php`

What's missing is the **wiring**:
1. Admin approval currently sets `status='approved'`. Finance queries then immediately see the PR (`status='approved' AND finance_status='pending'`). There is no gate.
2. There is no "select winning bid" step in the application layer — supplier bids exist, but no flow for a buyer/admin to mark one as the winner and release the PR to finance.
3. `Buyers/PHP/buyer_po.php` allows PO creation when `status='approved'`, with no check that finance has approved.
4. No UI exists to view supplier bids and pick a winner.

So this change is primarily **gating + UI**, not schema work.

## Goal

Enforce a sequential flow:

```
Admin/Approver approves
    → Suppliers bid
        → Buyer or Admin selects winning bid
            → Finance approves/rejects
                → Buyer creates PO
```

Until each stage completes, the next role cannot act on the request.

## Non-Goals (YAGNI)

- No real-time WebSocket/SSE notifications. Polling + email is enough for a capstone.
- No new user roles. Reusing existing `BUYER`, `ADMIN`, `SUPPLIER`, `FINANCE` roles.
- No new audit-log subsystem. Existing `log_activity.php` is reused.
- No automatic winning-bid selection. A human picks.
- No time-window gating for supplier bidding. Bidding stays open indefinitely until someone picks a winner.

## Final State Machine

Two fields collaborate: `status` (the legacy enum) and `finance_status` (separate enum).

```
PENDING  --(admin approves)-->  status=approved
                                  |
                                  | suppliers see and bid (status='approved' filter)
                                  v
                          [suppliers bid]
                                  |
                          [buyer/admin picks winner]   <-- NEW STEP
                                  v
                          status=finance_pending, finance_status=pending
                                  | finance now sees this PR
                                  v
                          [finance approves/rejects]
                                  |
                  +---------------+---------------+
                  v                               v
   status=finance_rejected,             status=finance_approved,
   finance_status=rejected              finance_status=approved
                                                  |
                                          [buyer creates PO]   <-- NEW GATE
                                                  v
                                          (PO Issued -> Received)
```

### Field-by-field semantics

| `status` value | `finance_status` value | Meaning | Visible to |
|---|---|---|---|
| `PENDING` | (any) | Awaiting admin/approver | Admin/Approver |
| `approved` | (any) | Admin approved; awaiting supplier bids and winner selection | Suppliers, Buyer, Admin. **Finance does NOT see this** (their query adds `status='approved'` but we will tighten it) |
| `finance_pending` | `pending` | Winning bid selected; awaiting finance review | Finance, Buyer, Admin |
| `finance_approved` | `approved` | Finance approved; PO can be created | Buyer, Admin |
| `finance_rejected` | `rejected` | Finance rejected; terminal | Requestor (with reason) |
| `rejected` | (any) | Admin rejected at first stage; terminal | Requestor |

The `finance_status` column is the **gate** for finance review. The `status` column tracks the broader workflow. We use both because the schema already does, and the finance-budget code already reads/writes `finance_status`.

## Component / File Changes

### Database

**No migration needed.** All columns already exist:
- `purchase_requests.status` ENUM has `finance_pending`, `finance_approved`, `finance_rejected`
- `purchase_requests.finance_status` ENUM has `pending`, `approved`, `rejected`
- `purchase_requests.finance_approved_by` INT
- `purchase_requests.finance_approved_at` DATETIME
- `purchase_requests.rejection_reason` TEXT (already used by both admin and finance rejection flows)

Optional: add `finance_rejection_reason` if we want to keep finance rejection reason separate from the existing `rejection_reason` (which is currently used for admin rejection). Decision: **reuse `rejection_reason`** for finance rejection too, since finance-budget-approvals.php currently doesn't set it (it only logs the reason to `finance_approvals.rejection_reason`). This keeps schema unchanged.

### New files

1. **`Admin/PHP/select_winning_bid.php`**
   - Shared endpoint callable by `BUYER` and `ADMIN` roles
   - `POST {pr_id, winning_bid_id}` -> atomic transition `status='approved'` -> `status='finance_pending'`, `finance_status='pending'`
   - Wraps in transaction with `SELECT ... FOR UPDATE` to prevent concurrent selection
   - Marks winning bid `status='selected'` and other bids `status='rejected'`
   - Sends email to requestor with new status case `awaiting_finance`

2. **`Buyers/HTML/buyer_select_winner.php`** (new page)
   - Buyer-facing UI to view supplier bids on a `status='approved'` PR and pick a winner
   - Reachable from the existing buyer dashboard, linked from PRs that are `status='approved'` with at least one bid

3. **`Admin/HTML/admin_select_winner.php`** (new page)
   - Admin-facing equivalent of the above

### Modified files

| File | Change |
|---|---|
| `Buyers/PHP/buyer_po.php` | Block PO creation unless `finance_status='approved'` AND `status='finance_approved'` |
| `Buyers/HTML/buyer.php` | Add a "Select Winning Bid" link/button for `status='approved'` PRs with bids |
| `Admin/HTML/Pending-approvals.php` | Add a "Select Winning Bid" link/button (or surface it via a new admin page) |
| `finance/PHP/finance-budget-approvals.php` | Tighten query: change `WHERE status='approved' AND finance_status='pending'` to `WHERE status='finance_pending' AND finance_status='pending'`. Without this change, finance still sees admin-approved-but-not-yet-bid PRs. |
| `Admin/PHP/send_pr_status_email.php` | Add `awaiting_finance` and `finance_rejected` cases. Existing `approved_by_finance`, `rejected_by_finance` are already there. |

### Files explicitly NOT changed

- `Admin/PHP/update_request_status.php` — admin approval stays the same (`status='approved'`, `finance_status='pending'` is the default already).
- `Supplier/HTML/supplier_open_requests.php` and `Supplier/PHP/supplier_api.php` — supplier sees PRs with `status='approved'` only. The bid submission guard `WHERE status='approved'` already does what we want.
- `Buyers/HTML/buyer.php` query — already filters `finance_status='approved'`, so buyer only sees finance-approved PRs. Good.

## Data Flow

### Happy path

```
1. Requestor submits PR
   POST create-request.php
     -> INSERT purchase_requests (status='PENDING', finance_status='pending', ...)
     -> sendPRStatusEmail('submitted', requestor)

2. Admin approves  [NO CHANGE]
   POST Admin/PHP/update_request_status.php {action:approve, id}
     -> UPDATE purchase_requests SET status='approved', approved_by=?, approved_at=NOW()
        (finance_status stays 'pending' as default)
     -> sendPRStatusEmail('approved_by_approver', requestor)
     -> Suppliers now see PR in Open Requests
     -> Finance does NOT see this PR (their query requires status='finance_pending' after our change)

3. Suppliers bid (existing code, unchanged)
   POST Supplier/PHP/supplier_api.php {action:submit_bid, ...}
     -> INSERT supplier_bids (status='pending')

4. Buyer or admin selects winning bid  [NEW]
   POST Admin/PHP/select_winning_bid.php {pr_id, winning_bid_id}
     -> Verify role IN {BUYER, ADMIN}
     -> Verify PR.status='approved' and bid belongs to this PR
     -> Verify at least one bid exists (and that the chosen winning_bid_id is among them)
     -> BEGIN TRANSACTION
         SELECT * FROM purchase_requests WHERE id=? FOR UPDATE
         UPDATE supplier_bids SET status='rejected' WHERE pr_id=? AND id != winning_bid_id
         UPDATE supplier_bids SET status='selected' WHERE id=winning_bid_id
         UPDATE purchase_requests SET status='finance_pending' WHERE id=? AND status='approved'
     -> COMMIT
     -> sendPRStatusEmail('awaiting_finance', requestor)

5. Finance reviews  [EXISTING endpoint, queries tightened]
   POST finance/PHP/finance-budget-approvals.php {action:finance_approve, pr_id}
     -> Existing code path. Query now filters status='finance_pending'.
     -> UPDATE purchase_requests SET finance_status='approved', status='finance_approved',
                                     finance_approved_by=?, finance_approved_at=NOW()
     -> Deducts from finance_budget, logs, sends 'approved_by_finance' email.

   OR

   POST finance/PHP/finance-budget-approvals.php {action:finance_reject, pr_id, reason}
     -> UPDATE purchase_requests SET finance_status='rejected', status='finance_rejected',
                                     rejection_reason=?, finance_approved_by=?, finance_approved_at=NOW()
     -> Sends 'rejected_by_finance' email.

6. Buyer creates PO  [GATED]
   POST Buyers/PHP/buyer_po.php {action:create_split, ...}
     -> NEW CHECK: PR.finance_status='approved' AND PR.status='finance_approved'
     -> Else return error "Finance approval required before creating a PO"
     -> INSERT purchase_orders ...
```

### Error handling

| Scenario | Behavior |
|---|---|
| Admin approves already-processed PR | Existing `WHERE status='PENDING'` returns 0 rows -> "Request not found or already processed" |
| Buyer/admin selects winner when no bids exist | Error: "No bids submitted yet" |
| Buyer/admin selects winner on PR not in `approved` | Error: "This PR is not in the bidding stage" |
| Buyer/admin selects a winning_bid_id that doesn't belong to this PR | Error: "Invalid bid for this PR" |
| Finance acts on PR not in `finance_pending` | Existing query filter returns 0 rows -> PR not in their list |
| Buyer creates PO before finance approves | NEW error: "Finance approval required before creating a PO" |
| Concurrent winner selection (two users click at once) | Transaction + `WHERE status='approved'` guard ensures only one writer wins. Second attempt returns 0 affected rows. |
| Concurrent finance approval | Same - existing `finance_status='pending'` guard |
| Email send fails | Existing behavior: log + return false; status change still commits. No change. |

## Secrets handling

All new PHP files that send email will use `getenv()` lookups instead of hardcoded credentials:

```php
$mail->Username = getenv('EMAIL_USER') ?: '';
$mail->Password = getenv('EMAIL_PASS') ?: '';
```

The existing `send_pr_status_email.php` has hardcoded credentials which should be migrated to `getenv()` as part of this work. Out of scope for the new feature, but flagged here.

A `.env` loader (`loadenv.php`) will be added if not already present.

## Testing

End-to-end manual tests, executed as a checklist after implementation:

1. **Happy path - full sequential flow.** PR -> admin approve -> 2 bids -> buyer picks winner -> finance approves -> PO created. Verify emails sent at each stage.
2. **Admin (not buyer) selects winner.** Same as #1 but admin user.
3. **PO creation blocked before finance.** After admin approves (status=`approved`), buyer tries to create PO -> 403 error.
4. **Finance rejection path.** Finance rejects with reason -> status=`finance_rejected`, reason saved, requestor notified.
5. **Concurrent winner selection.** Two browsers click simultaneously -> only one succeeds.
6. **Selecting winner with no bids.** Admin approved but no bids yet -> error.
7. **In-flight data.** Any existing PR with `status='approved'` works without migration (semantically already `awaiting_supplier_bids`).

Static checks:
- `php -l` on every modified/new PHP file
- `grep -rn "status='approved'"` to find every place that needs updating
- Confirm no test/seed script writes `status` values that bypass the gate

## Open questions / Decisions taken

- "What supplier action completes the supplier stage?" -> **Winning bid selected**
- "Who selects the winning bid?" -> **Both buyer and admin can**
- "What is finance's action?" -> **Approve/reject**
- "Should PO be blocked before finance?" -> **Yes**

## Decisions deferred

- Whether to also expose a "Reopen bidding" action for finance rejections (currently rejected is terminal). Out of scope.
- Whether to send a notification to all suppliers when a winner is picked (currently only requestor is notified). Out of scope.
