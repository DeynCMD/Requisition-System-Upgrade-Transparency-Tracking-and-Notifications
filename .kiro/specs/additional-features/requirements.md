# Requirements — Additional Features (ZE Electronics)

## Overview

This spec covers five capability additions across four roles. Several pieces are
partially built; the gaps are called out explicitly in each requirement.

---

## REQ-1 — Admin: Supplier Management

**Status of existing code:** `Admin/HTML/suppliers.php` Tab 1 and `Admin/PHP/suppliers.php`
already implement add / toggle / delete. This requirement is **fully satisfied** by
the existing implementation.

### Requirements

1. The Admin shall be able to add a new supplier by providing: name (required),
   contact person, email, phone, and address.
2. The Admin shall be able to toggle a supplier between Active and Inactive status.
3. The Admin shall be able to delete a supplier from the master list.
4. Only active suppliers shall appear in the bidding dropdown.
5. All supplier changes shall be visible immediately without a page reload.

---

## REQ-2 — Admin: Bidding & Lead Time Encoding

**Status of existing code:** `Admin/HTML/suppliers.php` Tab 2 and `Admin/PHP/supplier_bids.php`
already implement bid entry, allocation, and PO generation. This requirement is
**fully satisfied** by the existing implementation.

### Requirements

1. The Admin shall be able to select any purchase request with `status = approved`
   to open the bidding panel.
2. The Admin shall be able to add one bid per supplier per PR, specifying:
   unit price and committed delivery date.
3. The Admin shall be able to allocate a quantity to each bid. The sum of
   allocated quantities must not exceed the total requested quantity before
   PO generation is allowed.
4. The Admin shall be able to generate split Purchase Orders from the allocated
   bids, creating one PO record per supplier allocation.
5. Generated POs shall be visible in the Purchase Orders tab with status `Issued`.

---

## REQ-3 — Buyer: Purchase Order Splitting

**Status of existing code:** The Buyer role has **no PO management page**. The
Admin currently owns PO generation. This requirement introduces a dedicated
Buyer-side PO management view.

### Requirements

1. The Buyer dashboard sidebar shall include a new **"Purchase Orders"** navigation
   link.
2. The Buyer shall be able to view all generated POs (from Admin bidding) in a
   table showing: PO number, source PR number, supplier, MPN, quantity, unit
   price, total, delivery date, and status.
3. The Buyer shall be able to manually split an approved PR into multiple POs
   directly from the Buyer interface, without requiring Admin to run the bidding
   workflow first. This covers cases where the Buyer has sourced suppliers
   independently.
4. To create a manual split, the Buyer shall select an approved PR and add one
   or more PO lines, each specifying: supplier (from master list), quantity,
   unit price, and expected delivery date.
5. The total quantity across all PO lines for a PR must not exceed the original
   requested quantity.
6. The Buyer shall be able to update the status of a PO from `Issued` to
   `Received` or `Cancelled`.
7. PO MRO category (inherited from the source PR) shall be visible on each PO
   row to aid budget tracking.

---

## REQ-4 — Requestor: MRO Categorization on Create Request

**Status of existing code:** The `Create Request` form already has a `category`
dropdown (Maintenance / Repair / Operation) and a `subcategory` dropdown that
populates based on category. The `purchase_requests` table has `category` and
`subcategory` columns. This requirement is **fully satisfied** by the existing
implementation.

### Requirements

1. The Create Request form shall include a **Category** dropdown with the three
   MRO options: Maintenance, Repair, Operations.
2. After selecting a Category, a **Subcategory** dropdown shall appear and
   populate with relevant options specific to the chosen category.
3. Both Category and Subcategory shall be required fields before form submission.
4. The selected Category value shall be stored in `purchase_requests.category`
   and used downstream by Finance for MRO budget deduction.

---

## REQ-5 — Requestor: Initiate Withdrawal (Pre-PO)

**Status of existing code:** `Requesitor/HTML/my-requests.php` and
`Requesitor/PHP/request_withdrawal.php` already support withdrawal, but only
when `buyer_status = purchased`. The requirement is to also allow withdrawal
**before** a PO is generated (i.e., pre-purchase cancellation).

### Requirements

1. The Requestor shall be able to initiate a withdrawal on any of their own PRs
   that meets one of the following conditions:
   - `status = approved` AND `buyer_status = pending_payment` (pre-PO / pre-purchase), OR
   - `buyer_status = purchased` (post-purchase refund — existing behaviour).
2. The withdrawal button shall be labelled contextually:
   - "Cancel Request" for pre-PO withdrawals.
   - "Request Withdrawal / Refund" for post-purchase withdrawals.
3. The Requestor shall provide a reason (minimum 5 characters) when submitting
   either type.
4. A PR that already has a pending withdrawal shall show a "Pending Review"
   badge and the button shall be disabled.
5. On submission, `purchase_requests.withdrawal_status` shall be set to
   `requested` and a record inserted into `pr_withdrawals`.
6. The Requestor shall not be able to submit a second withdrawal if one is
   already pending or approved.

---

## REQ-6 — Admin: Withdrawal Oversight

**Status of existing code:** Finance owns withdrawal review (`withdrawals.php`).
The Admin currently has **no withdrawal visibility**. This requirement adds a
read-only audit view on the Admin side.

### Requirements

1. The Admin shall have a **Withdrawal Log** page (read-only) showing all
   withdrawal requests across all requestors with columns: PR number, requestor,
   MPN, category, amount, reason, submission date, status, and reviewed by.
2. The withdrawal log shall be accessible from the Admin sidebar.
3. All status changes (requested → approved / rejected) shall be recorded in
   `activity_logs` with the appropriate activity type.
4. The Admin shall not be able to approve or reject withdrawals — that authority
   belongs to Finance.

---

## REQ-7 — Finance: MRO Budget Tracking

**Status of existing code:** `Admin/HTML/budget_categories.php` shows MRO budget
cards and allocation form, but this belongs to the Admin role. Finance has no
equivalent page. The `department_budgets` table and allocation backend
(`Admin/PHP/dept_budget.php`) already exist.

### Requirements

1. The Finance sidebar shall include a new **"MRO Budget"** navigation link.
2. The Finance MRO Budget page shall display three budget cards — Maintenance,
   Repair, Operations — each showing: allocated amount, spent amount, remaining
   amount, and a utilization progress bar.
3. Finance shall be able to allocate budget to any MRO category from this page,
   using the existing `dept_budget.php` backend.
4. When a purchase request is finance-approved, the system shall automatically
   deduct the PR's `total_amount` from the corresponding `department_budgets`
   row that matches `purchase_requests.category`.
5. The Finance MRO Budget page shall show a recent allocation/spend transaction
   log filtered to MRO-type entries.

---

## REQ-8 — Finance: Refund Processing on Withdrawal Approval

**Status of existing code:** `finance/PHP/review_withdrawal.php` already refunds
to `finance_budget` (flat total) when a withdrawal is approved. It does **not**
credit back the MRO category budget (`department_budgets`). This requirement
closes that gap.

### Requirements

1. When Finance approves a withdrawal, the system shall re-credit the
   `department_budgets` row matching the PR's `category` by the withdrawal
   amount (increase `remaining_amount`, decrease `spent_amount`).
2. The refund shall also continue to update `finance_budget.remaining_budget`
   (existing behaviour preserved).
3. A `budget_transactions` record of type `refund` with the correct `department`
   value shall be inserted on every approved withdrawal.
4. If Finance rejects a withdrawal, no budget changes shall occur.
5. The Finance Withdrawals page shall display a summary count of pending,
   approved, and rejected withdrawals at the top of the page.

---

## Database Changes Required

The following schema additions are needed to support the gaps above.

| Table | Change | Reason |
|---|---|---|
| `pr_withdrawals` | Add `withdrawal_type ENUM('pre_po','post_purchase') DEFAULT 'post_purchase'` | REQ-5: distinguish cancellation type |
| `purchase_orders` | Already exists — no change needed | REQ-3 reuses existing table |
| `activity_logs` | `activity_type` enum must include `'po_created'`, `'po_updated'`, `'withdrawal_admin_view'` | REQ-3, REQ-6 |
| `department_budgets` | No structural change — REQ-7 & REQ-8 use existing columns | — |

---

## Role-Capability Summary

| Capability | Admin | Requestor | Finance | Buyer |
|---|:---:|:---:|:---:|:---:|
| Add / manage suppliers | ✅ (existing) | — | — | — |
| Enter bids & delivery dates | ✅ (existing) | — | — | — |
| Generate split POs (from bidding) | ✅ (existing) | — | — | — |
| View & manage POs directly | — | — | — | ✅ **new** |
| Manual PO splitting by Buyer | — | — | — | ✅ **new** |
| MRO category on create request | — | ✅ (existing) | — | — |
| Initiate withdrawal (pre-PO) | — | ✅ **new** | — | — |
| Initiate withdrawal (post-purchase) | — | ✅ (existing) | — | — |
| Withdrawal audit log (read-only) | ✅ **new** | — | — | — |
| Approve / reject withdrawals | — | — | ✅ (existing) | — |
| MRO budget allocation & tracking | ✅ (existing) | — | ✅ **new page** | — |
| Refund to MRO category on withdrawal | — | — | ✅ **new** | — |
