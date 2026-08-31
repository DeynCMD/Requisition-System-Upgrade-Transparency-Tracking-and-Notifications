// pending_approval.js

let allRequests = [];

function attachEventListeners() {
  const table = document.getElementById("pendingTable");
  if (!table) {
    console.error("Table #pendingTable not found");
    return;
  }

  table.addEventListener("click", (e) => {
    // Find the closest button — this handles clicks on <i> icons inside buttons too
    const button = e.target.closest("button");
    if (!button) return;

    const row = button.closest("tr");
    if (!row || !row.dataset.id) {
      console.warn("Clicked button but no valid row or data-id found");
      return;
    }

    const id = row.dataset.id;

    if (button.classList.contains("view-btn")) {
      viewRequest(id);
    } else if (button.classList.contains("approve-btn")) {
      approveRequest(id);
    } else if (button.classList.contains("reject-btn")) {
      rejectRequest(id);
    }
  });
}

async function loadPendingRequests() {
  try {
    const response = await fetch("../PHP/get_pending_requests.php");
    if (!response.ok) {
      throw new Error(`HTTP error ${response.status}`);
    }

    const result = await response.json();

    if (!result.success) {
      console.warn("Server returned unsuccessful response:", result);
      showEmptyState();
      return;
    }

    allRequests = result.requests || [];
    displayRequests(allRequests);
  } catch (err) {
    console.error("Failed to load pending requests:", err);
    showEmptyState();
    Swal.fire({
      icon: "error",
      title: "Connection Error",
      text: "Could not load pending requests. Please check your connection.",
      confirmButtonColor: "#ef4444",
    });
  }
}

function displayRequests(requests) {
  const tbody = document.getElementById("tableBody");
  const emptyState = document.getElementById("emptyState");
  const countEl = document.getElementById("pendingCount");

  if (!tbody) {
    console.error("tbody #tableBody not found");
    return;
  }

  tbody.innerHTML = "";

  if (!requests || requests.length === 0) {
    showEmptyState();
    if (countEl) countEl.textContent = "0";
    return;
  }

  if (emptyState) emptyState.style.display = "none";
  if (countEl) countEl.textContent = requests.length;

  requests.forEach((req) => {
    const tr = document.createElement("tr");
    tr.dataset.id = req.id;

    const date = new Date(req.request_date);
    const formattedDate = isNaN(date.getTime())
      ? "—"
      : date.toLocaleDateString("en-PH", {
          year: "numeric",
          month: "short",
          day: "numeric",
          hour: "2-digit",
          minute: "2-digit",
        });

    // Category color-coding
    const catColors = {
      'Maintenance': 'background:#fef9c3;color:#854d0e;',
      'Repair':      'background:#fee2e2;color:#991b1b;',
      'Operations':  'background:#ede9fe;color:#6d28d9;',
      'Operation':   'background:#ede9fe;color:#6d28d9;',
    };
    const catStyle = catColors[req.category] || 'background:#f1f5f9;color:#374151;';
    const catBadge = req.category
      ? `<span style="display:inline-flex;padding:3px 9px;border-radius:20px;font-size:.72rem;font-weight:700;${catStyle}">${req.category}</span>`
      : '—';

    // Urgency badge using shared Urgency helper (currency.js)
    const urgBadge = typeof Urgency !== 'undefined' ? Urgency.badge(req.urgency) : (req.urgency || '—');
    const urgRowCls = typeof Urgency !== 'undefined' ? Urgency.rowCls(req.urgency) : '';
    if(urgRowCls) tr.classList.add(urgRowCls);

    tr.innerHTML = `
            <td><strong>${req.pr_number || "—"}</strong></td>
            <td>${req.requestor_name || "—"}</td>
            <td>${formattedDate}</td>
            <td>${catBadge}</td>
            <td style="font-size:.82rem;color:#64748b">${req.subcategory || '—'}</td>
            <td>${req.quantity || "—"}</td>
            <td>${req.mpn || req.selected_distributor_text || req.distributor || "—"}</td>
            <td>${urgBadge}</td>
            <td class="actions">
                <button class="view-btn" title="View details">
                    <i class="fas fa-eye"></i> View
                </button>
                <button class="approve-btn" title="Approve">
                    <i class="fas fa-check"></i> Approve
                </button>
                <button class="reject-btn" title="Reject">
                    <i class="fas fa-times"></i> Reject
                </button>
            </td>
        `;
    tbody.appendChild(tr);
  });
}

function filterRequests(query) {
  if (!query?.trim()) {
    displayRequests(allRequests);
    return;
  }

  const q = query.toLowerCase().trim();
  const filtered = allRequests.filter((req) =>
    [
      req.pr_number,
      req.requestor_name,
      req.category,
      req.subcategory,
      req.mpn,
      req.selected_distributor_text || req.distributor,
      String(req.quantity),
    ].some((field) => field?.toLowerCase().includes(q)),
  );

  displayRequests(filtered);
}

function showEmptyState() {
  const emptyState = document.getElementById("emptyState");
  if (emptyState) emptyState.style.display = "block";
}

// ──────────────────────────────────────────────
// VIEW REQUEST
// ──────────────────────────────────────────────
async function viewRequest(id) {
  try {
    const response = await fetch(
      `../../Admin/PHP/get_request_detail.php?id=${id}`,
    );
    if (!response.ok) throw new Error(`HTTP ${response.status}`);

    const html = await response.text();
    const modalContent = document.getElementById("modalContent");
    const detailModal = document.getElementById("detailModal");

    if (modalContent && detailModal) {
      modalContent.innerHTML = html;
      detailModal.style.display = "flex";
    }
  } catch (err) {
    console.error("View request failed:", err);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Could not load request details.",
      confirmButtonColor: "#ef4444",
    });
  }
}

// ──────────────────────────────────────────────
// APPROVE REQUEST
// ──────────────────────────────────────────────
async function approveRequest(id) {
  const result = await Swal.fire({
    title: "Approve this request?",
    text: "This will mark the request as approved and notify the requestor.",
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#10b981",
    cancelButtonColor: "#6b7280",
    confirmButtonText: "Yes, approve",
    cancelButtonText: "Cancel",
    reverseButtons: true,
  });

  if (!result.isConfirmed) return;

  try {
    const formData = new URLSearchParams({ id, action: "approve" });
    const res = await fetch("../../Admin/PHP/update_request_status.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: formData,
    });

    const data = await res.json();

    if (data.success) {
      Swal.fire({
        icon: "success",
        title: "Approved",
        text: "Request approved successfully.",
        confirmButtonColor: "#10b981",
      });
      loadPendingRequests();
    } else {
      Swal.fire({
        icon: "error",
        title: "Failed",
        text: data.message || "Could not approve request.",
        confirmButtonColor: "#ef4444",
      });
    }
  } catch (err) {
    console.error("Approve failed:", err);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Network or server error while approving.",
      confirmButtonColor: "#ef4444",
    });
  }
}

// ──────────────────────────────────────────────
// REJECT REQUEST
// ──────────────────────────────────────────────
async function rejectRequest(id) {
  const { value: reason, isConfirmed } = await Swal.fire({
    title: "Reject this request?",
    input: "textarea",
    inputLabel: "Rejection reason",
    inputPlaceholder: "Please provide a reason (minimum 5 characters)",
    inputAttributes: {
      minlength: 5,
      required: true,
    },
    showCancelButton: true,
    confirmButtonColor: "#ef4444",
    cancelButtonColor: "#6b7280",
    confirmButtonText: "Yes, reject",
    cancelButtonText: "Cancel",
    reverseButtons: true,
    preConfirm: (value) => {
      if (!value || value.trim().length < 5) {
        Swal.showValidationMessage("Reason must be at least 5 characters long");
      }
      return value?.trim();
    },
  });

  if (!isConfirmed || !reason) return;

  try {
    const formData = new URLSearchParams({
      id,
      action: "reject",
      reason,
    });

    const res = await fetch("../../Admin/PHP/update_request_status.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: formData,
    });

    const data = await res.json();

    if (data.success) {
      Swal.fire({
        icon: "success",
        title: "Rejected",
        text: "Request has been rejected.",
        confirmButtonColor: "#ef4444",
      });
      loadPendingRequests();
    } else {
      Swal.fire({
        icon: "error",
        title: "Failed",
        text: data.message || "Could not reject request.",
        confirmButtonColor: "#ef4444",
      });
    }
  } catch (err) {
    console.error("Reject failed:", err);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Network or server error while rejecting.",
      confirmButtonColor: "#ef4444",
    });
  }
}

function initModals() {
  const modal = document.getElementById("detailModal");
  const closeBtn = document.getElementById("closeModal");

  if (!modal || !closeBtn) return;

  closeBtn.onclick = () => {
    modal.style.display = "none";
  };

  modal.onclick = (e) => {
    if (e.target === modal) {
      modal.style.display = "none";
    }
  };
}

// ──────────────────────────────────────────────
// Initialization
// ──────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
  // Make sure SweetAlert2 is loaded before anything else
  if (typeof Swal === "undefined") {
    console.error(
      "SweetAlert2 (Swal) not loaded. Check your HTML <script> tag.",
    );
  }

  loadPendingRequests();
  loadBiddingRequests();
  attachEventListeners();
  attachBiddingEventListeners();
  initModals();

  const searchInput = document.getElementById("searchInput");
  if (searchInput) {
    let timeout;
    searchInput.addEventListener("input", () => {
      clearTimeout(timeout);
      timeout = setTimeout(() => filterRequests(searchInput.value), 300);
    });
  }
});

// ──────────────────────────────────────────────
// BIDDING STAGE — admin-approved PRs awaiting winning bid
// ──────────────────────────────────────────────
let allBiddingRequests = [];

function attachBiddingEventListeners() {
  const table = document.getElementById("biddingTable");
  if (!table) return;
  table.addEventListener("click", (e) => {
    const link = e.target.closest("a.select-winner-btn");
    if (!link) return;
    // Anchor href navigation handles itself; nothing to do here.
  });
}

async function loadBiddingRequests() {
  const tbody = document.getElementById("biddingTableBody");
  if (!tbody) return; // page doesn't render the bidding table

  try {
    const response = await fetch("../PHP/get_bidding_requests.php");
    if (!response.ok) throw new Error(`HTTP error ${response.status}`);
    const result = await response.json();
    if (!result.success) {
      console.warn("Bidding requests: server returned unsuccessful response", result);
      renderBiddingRequests([]);
      return;
    }
    allBiddingRequests = result.requests || [];
    renderBiddingRequests(allBiddingRequests);
  } catch (err) {
    console.error("Failed to load bidding requests:", err);
    renderBiddingRequests([]);
  }
}

function renderBiddingRequests(requests) {
  const tbody = document.getElementById("biddingTableBody");
  const emptyState = document.getElementById("biddingEmptyState");
  const countEl = document.getElementById("biddingCount");
  if (!tbody) return;

  tbody.innerHTML = "";
  if (!requests || requests.length === 0) {
    if (emptyState) emptyState.style.display = "block";
    if (countEl) countEl.textContent = "0";
    return;
  }
  if (emptyState) emptyState.style.display = "none";
  if (countEl) countEl.textContent = requests.length;

  requests.forEach((req) => {
    const totalBids = parseInt(req.total_bids, 10) || 0;
    const pendingBids = parseInt(req.pending_bids, 10) || 0;
    const tr = document.createElement("tr");
    tr.dataset.id = req.id;
    tr.innerHTML = `
      <td><strong>${req.pr_number || "—"}</strong></td>
      <td>${req.requestor_name || "—"}</td>
      <td style="font-size:.82rem;color:#64748b">${req.mpn || "—"}</td>
      <td>${req.category || "—"}</td>
      <td>${req.quantity || "—"}</td>
      <td>
        <span style="background:#1e1e2e;color:#e0e0ff;border:1px solid #444;padding:2px 9px;border-radius:20px;font-size:.72rem;font-weight:700;">
          ${totalBids} bid${totalBids !== 1 ? "s" : ""}${pendingBids > 0 ? ` (${pendingBids} pending)` : ""}
        </span>
      </td>
      <td class="actions">
        <a class="select-winner-btn" href="admin_select_winner.php?pr_id=${req.id}"
           style="background:#22c55e;color:#fff;text-decoration:none;padding:7px 14px;border-radius:8px;font-size:.8rem;font-weight:700;display:inline-flex;align-items:center;gap:6px;">
          <i class="fas fa-gavel"></i> ${totalBids > 0 ? "Select Winner" : "View"}
        </a>
      </td>
    `;
    tbody.appendChild(tr);
  });
}
