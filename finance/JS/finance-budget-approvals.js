// finance-budget-approvals.js – FIXED: View PR now works
// Also keeps all your debug logs

console.log(
  "finance-budget-approvals.js → LOADED SUCCESSFULLY at " +
    new Date().toISOString(),
);

document.addEventListener("DOMContentLoaded", () => {
  console.log("DOM ready → starting loadApprovedRequests()");
  loadApprovedRequests();

  const tableBody = document.getElementById("approval-table-body");
  if (!tableBody) {
    console.error("CRITICAL: #approval-table-body NOT FOUND");
    return;
  }

  console.log("Event listener attached to #approval-table-body");

  tableBody.addEventListener("click", function (e) {
    console.log(
      "CLICK DETECTED! Target:",
      e.target.tagName,
      e.target.className,
    );

    const btn = e.target.closest("button");
    if (!btn) {
      console.log("No button found");
      return;
    }

    console.log("Button:", btn.outerHTML.substring(0, 100) + "...");

    const row = btn.closest("tr");
    if (!row) {
      console.log("No <tr> found");
      return;
    }

    // FIXED: Use consistent case - dataset uses camelCase
    const prId = row.dataset.prId; // becomes dataset.prId
    const prNumber = row.dataset.prNumber;

    console.log("Row data:", { prId, prNumber });

    if (btn.classList.contains("view-btn")) {
      console.log("VIEW clicked → calling viewPurchaseOrder(" + prId + ")");
      viewPurchaseOrder(prId);
    } else if (btn.classList.contains("approve-btn")) {
      console.log("APPROVE clicked → handleApprove(" + prId + ")");
      handleApprove(prId, prNumber);
    } else if (btn.classList.contains("reject-btn")) {
      console.log("REJECT clicked → handleReject(" + prId + ")");
      handleReject(prId, prNumber);
    } else {
      console.log("Unknown button class:", btn.classList.toString());
    }
  });

  // Modal close handlers
  const viewModal = document.getElementById("viewPoModal");
  const closeViewModal = document.getElementById("closeViewModal");

  if (closeViewModal) {
    closeViewModal.onclick = () => {
      console.log("Close modal clicked");
      viewModal.style.display = "none";
    };
  }

  if (viewModal) {
    viewModal.onclick = (e) => {
      if (e.target === viewModal) {
        console.log("Clicked outside modal");
        viewModal.style.display = "none";
      }
    };
  }
});

// ──────────────────────────────────────────────
// SweetAlert Approve
// ──────────────────────────────────────────────
async function handleApprove(prId, prNumber) {
  console.log("handleApprove → PR:", prId, prNumber);

  const result = await Swal.fire({
    title: "Approve this request?",
    text: `PR #${prNumber || "—"} will be approved.`,
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#10b981",
    cancelButtonColor: "#6b7280",
    confirmButtonText: "Yes, approve",
    cancelButtonText: "Cancel",
  });

  if (!result.isConfirmed) return;

  financeAction("finance_approve", prId);
}

// ──────────────────────────────────────────────
// SweetAlert Reject
// ──────────────────────────────────────────────
async function handleReject(prId, prNumber) {
  console.log("handleReject → PR:", prId, prNumber);

  const { value: reason, isConfirmed } = await Swal.fire({
    title: "Reject this request?",
    text: `PR #${prNumber || "—"} will be rejected.`,
    input: "textarea",
    inputLabel: "Reason",
    inputPlaceholder: "Enter reason (min 5 chars)",
    inputAttributes: { minlength: 5, required: true },
    showCancelButton: true,
    confirmButtonColor: "#ef4444",
    confirmButtonText: "Yes, reject",
  });

  if (!isConfirmed || !reason || reason.trim().length < 5) return;

  financeAction("finance_reject", prId, reason.trim());
}

async function loadApprovedRequests() {
  const tbody = document.getElementById("approval-table-body");
  const emptyState = document.getElementById("empty-state");

  if (!tbody) return console.error("tbody missing");

  console.log("Loading pending approvals...");

  try {
    const res = await fetch("../PHP/finance-budget-approvals.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ action: "get_approved_requests" }),
    });

    if (!res.ok) throw new Error(`HTTP ${res.status}`);

    const data = await res.json();
    console.log("Data loaded:", data);

    if (!data.success) throw new Error(data.message || "Server error");

    tbody.innerHTML = "";

    if (!data.data || data.data.length === 0) {
      if (emptyState) emptyState.style.display = "block";
      return;
    }

    if (emptyState) emptyState.style.display = "none";

    data.data.forEach((pr) => {
      const quantity = Number(pr.quantity) || 0;
      const unit_price = Number(pr.unit_price) || 0;
      const total = (quantity * unit_price).toFixed(2);
      const currency = pr.currency || "PHP";

      const row = document.createElement("tr");
      row.dataset.prId = pr.id; // camelCase for JS
      row.dataset.prNumber = pr.pr_number;

      row.innerHTML = `
        <td>${pr.pr_number || "—"}</td>
        <td>${pr.category || "N/A"}</td>
        <td>${currency} ${total}</td>
        <td>${(pr.reason || "").substring(0, 60)}${(pr.reason || "").length > 60 ? "..." : ""}</td>
        <td class="actions">
          <button class="view-btn" data-pr-id="${pr.id}">View PR</button>
          <button class="approve-btn">Approve</button>
          <button class="reject-btn">Reject</button>
        </td>
      `;
      tbody.appendChild(row);
    });
  } catch (err) {
    console.error("Load failed:", err);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Failed to load requests: " + err.message,
    });
    if (emptyState) emptyState.style.display = "block";
  }
}

async function viewPurchaseOrder(prId) {
  console.log("viewPurchaseOrder called with ID:", prId);

  try {
    const url = `../../Admin/PHP/get_request_detail.php?id=${prId}`;
    console.log("Fetching PO details from:", url);

    const res = await fetch(url);
    if (!res.ok) {
      const text = await res.text();
      throw new Error(`HTTP ${res.status} - ${text}`);
    }

    const html = await res.text();
    console.log("PO HTML received (length):", html.length);

    const modal = document.getElementById("viewPoModal");
    const content = document.getElementById("poModalContent");

    if (!modal || !content) {
      console.error("Modal elements missing!");
      throw new Error("Modal elements not found");
    }

    content.innerHTML = html;
    modal.style.display = "flex";
    console.log("Modal opened successfully");
  } catch (err) {
    console.error("View PO failed:", err);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Could not load PR details: " + err.message,
      confirmButtonColor: "#ef4444",
    });
  }
}

async function financeAction(action, prId, reason = "") {
  console.log(
    `financeAction → ${action} | prId: ${prId} | reason: ${reason || "none"}`,
  );

  try {
    const params = new URLSearchParams();
    params.append("action", action);
    params.append("pr_id", prId);
    if (reason) params.append("reason", reason);

    console.log("POST payload:", params.toString());

    const res = await fetch("../PHP/finance-budget-approvals.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: params,
    });

    console.log("Response status:", res.status);

    if (!res.ok) {
      const text = await res.text();
      throw new Error(`HTTP ${res.status} - ${text}`);
    }

    const data = await res.json();
    console.log("Backend response:", data);

    if (data.success) {
      await Swal.fire({
        icon: "success",
        title: action.includes("approve") ? "Approved" : "Rejected",
        text: data.message || "Success",
        timer: 1800,
      });
      loadApprovedRequests();
    } else {
      Swal.fire({
        icon: "error",
        title: "Failed",
        text: data.message || "Action failed",
      });
    }
  } catch (err) {
    console.error("Action error:", err);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Network/server issue: " + err.message,
    });
  }
}
