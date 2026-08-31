// ==============================
// GLOBAL VARIABLES
// ==============================
let purchaseRequests = [];
let selectedPR = null;

// ==============================
// INITIALIZE ON PAGE LOAD
// ==============================
document.addEventListener("DOMContentLoaded", async () => {
  console.log("Page loaded, fetching purchase requests...");
  await loadPurchaseRequests();

  // Check for shared PR in URL
  const urlParams = new URLSearchParams(window.location.search);
  const prId = urlParams.get("pr");
  if (prId) {
    setTimeout(() => selectPR(prId), 500);
  }
});

// ==============================
// LOAD PURCHASE REQUESTS
// ==============================
async function loadPurchaseRequests() {
  try {
    const response = await fetch(
      "http://localhost/G27_CAPSTONE_ZE_COMPANY/Admin/PHP/get_purchase_requests.php",
      {
        method: "GET",
        credentials: "include",
      },
    );

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    if (data.error) {
      console.error("Server error:", data.error);
      purchaseRequests = [];
      renderPRList();
      return;
    }

    purchaseRequests = data;
    renderPRList();
  } catch (error) {
    console.error("❌ Error loading PRs:", error);
    Swal.fire({ icon:'error', title:'Load Failed', text:'Failed to load purchase requests. Please refresh.', confirmButtonColor:'#ef4444', background:'#2a2a3a', color:'#e0e0ff' });
    purchaseRequests = [];
    renderPRList();
  }
}

// ==============================
// RENDER PR LIST
// ==============================
function renderPRList() {
  const prList = document.getElementById("prList");

  if (!purchaseRequests || purchaseRequests.length === 0) {
    prList.innerHTML =
      '<p style="text-align: center; color: #888; padding: 40px;">No purchase requests found.</p>';
    return;
  }

  prList.innerHTML = purchaseRequests
    .map(
      (pr) => `
      <div class="pr-card ${selectedPR?.id === pr.id ? "selected" : ""}" onclick="selectPR('${pr.id}')">
        <div class="pr-card-header">
          <div>
            <h3>${pr.id}</h3>
            <p>${pr.date || "N/A"}</p>
          </div>
          <span class="status-badge status-${pr.status.toLowerCase()}">${pr.status}</span>
        </div>
        <div class="pr-card-info">
          <p><i class="fas fa-user"></i> ${pr.requestor}</p>
          <p><i class="fas fa-building"></i> ${pr.department}</p>
          <p><i class="fas fa-box"></i> ${pr.items ? pr.items.length : 0} item(s)</p>
        </div>
      </div>
    `,
    )
    .join("");
}

// ==============================
// SELECT PR
// ==============================
function selectPR(prId) {
  selectedPR = purchaseRequests.find((pr) => pr.id === prId);

  if (!selectedPR) {
    console.error("PR not found:", prId);
    return;
  }

  console.log("Selected PR:", selectedPR);

  renderPRList();
  renderPRTemplate();

  document.getElementById("actionButtons").style.display = "flex";
  document.getElementById("emptyState").style.display = "none";
  document.getElementById("prTemplate").style.display = "block";

  // Scroll to template
  document.getElementById("prTemplate").scrollIntoView({ behavior: "smooth" });
}

// ==============================
// RENDER PR TEMPLATE
// ==============================
function renderPRTemplate() {
  if (!selectedPR) return;

  const totalAmount = selectedPR.items.reduce(
    (sum, item) => sum + item.total,
    0,
  );

  const template = `
    <div class="pr-header">
      <div class="company-name">Procurement System</div>
      <div class="company-subtitle">Procurement System</div>
      <div class="document-title">PURCHASE REQUEST</div>
      <div class="pr-id-row">
        <div class="pr-id">${selectedPR.id}</div>
        <span class="status-badge status-${selectedPR.status.toLowerCase()}">${selectedPR.status}</span>
      </div>
    </div>

    <div class="pr-info-grid">
      <div class="info-box">
        <div class="info-label">Requestor</div>
        <div class="info-value">${selectedPR.requestor}</div>
      </div>
      <div class="info-box">
        <div class="info-label">Department</div>
        <div class="info-value">${selectedPR.department}</div>
      </div>
      <div class="info-box">
        <div class="info-label">Request Date</div>
        <div class="info-value">${selectedPR.date}</div>
      </div>
      <div class="info-box">
        <div class="info-label">Required Date</div>
        <div class="info-value">${selectedPR.requiredDate || "N/A"}</div>
      </div>
      <div class="info-box">
        <div class="info-label">Urgency</div>
        <div class="info-value">${selectedPR.urgency}</div>
      </div>
      ${
        selectedPR.approver
          ? `
        <div class="info-box">
          <div class="info-label">Approved By</div>
          <div class="info-value">${selectedPR.approver} (${selectedPR.approvalDate})</div>
        </div>
      `
          : ""
      }
    </div>

    <div class="section-title">REQUESTED ITEMS</div>
    <table class="items-table">
      <thead>
        <tr>
          <th style="width: 5%">#</th>
          <th style="width: 15%">MPN</th>
          <th style="width: 15%">Manufacturer</th>
          <th style="width: 30%">Description</th>
          <th style="width: 10%; text-align: right">Qty</th>
          <th style="width: 12%; text-align: right">Unit Price</th>
          <th style="width: 13%; text-align: right">Total</th>
        </tr>
      </thead>
      <tbody>
        ${selectedPR.items
          .map(
            (item, idx) => `
          <tr>
            <td>${idx + 1}</td>
            <td>${item.mpn}</td>
            <td>${item.manufacturer}</td>
            <td>${item.description}</td>
            <td style="text-align: right">${item.qty.toLocaleString()}</td>
            <td style="text-align: right">$${item.unitPrice.toFixed(4)}</td>
            <td style="text-align: right">$${item.total.toFixed(2)}</td>
          </tr>
        `,
          )
          .join("")}
        <tr class="total-row">
          <td colspan="6" style="text-align: right; padding-right: 15px">TOTAL AMOUNT:</td>
          <td style="text-align: right" class="total-amount">$${totalAmount.toFixed(2)}</td>
        </tr>
      </tbody>
    </table>

    ${
      selectedPR.reason
        ? `
      <div class="section-title">JUSTIFICATION</div>
      <div class="text-box">
        <strong>Reason:</strong> ${selectedPR.reason}
      </div>
    `
        : ""
    }

    ${
      selectedPR.notes
        ? `
      <div class="section-title">ADDITIONAL NOTES</div>
      <div class="text-box">${selectedPR.notes}</div>
    `
        : ""
    }

    <div class="signature-section">
      <div class="signature-box">
        <div class="signature-space"></div>
        <div class="signature-label">Requested By</div>
        <div class="signature-name">${selectedPR.requestor}</div>
        <div class="signature-date">Date: ${selectedPR.date}</div>
      </div>
      <div class="signature-box">
        <div class="signature-space"></div>
        <div class="signature-label">Approved By</div>
        <div class="signature-name">${selectedPR.approver || "_________________"}</div>
        <div class="signature-date">Date: ${selectedPR.approvalDate || "_________________"}</div>
      </div>
    </div>

    <div class="pr-footer">
      <p>Procurement System</p>
      <p>This is a computer-generated document. No signature is required.</p>
      <p>Document ID: ${selectedPR.id} | Generated: ${new Date().toLocaleString()}</p>
    </div>
  `;

  document.getElementById("prTemplate").innerHTML = template;
}

// ==============================
// PRINT / DOWNLOAD
// ==============================
function downloadPDF() {
  printPR();
}

function printPR() {
  if (!selectedPR) {
    Swal.fire({ icon:'warning', title:'No Request Selected', text:'Please select a purchase request first.', confirmButtonColor:'#22c55e', background:'#2a2a3a', color:'#e0e0ff' });
    return;
  }
  window.print();
}

// ==============================
// SEND EMAIL
// ==============================
function sendEmail() {
  if (!selectedPR) {
    Swal.fire({ icon:'warning', title:'No Request Selected', text:'Please select a purchase request first.', confirmButtonColor:'#22c55e', background:'#2a2a3a', color:'#e0e0ff' });
    return;
  }

  const totalAmount = selectedPR.items.reduce(
    (sum, item) => sum + item.total,
    0,
  );
  const subject = `Purchase Request ${selectedPR.id}`;
  const body = `Please review Purchase Request ${selectedPR.id}

Requestor: ${selectedPR.requestor}
Department: ${selectedPR.department}
Date: ${selectedPR.date}
Required By: ${selectedPR.requiredDate}
Total Items: ${selectedPR.items.length}
Total Amount: $${totalAmount.toFixed(2)}
Status: ${selectedPR.status}

Reason: ${selectedPR.reason}
${selectedPR.notes ? "\nNotes: " + selectedPR.notes : ""}`;

  window.location.href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
}

// ==============================
// SHARE PR
// ==============================
function sharePR() {
  if (!selectedPR) {
    Swal.fire({ icon:'warning', title:'No Request Selected', text:'Please select a purchase request first.', confirmButtonColor:'#22c55e', background:'#2a2a3a', color:'#e0e0ff' });
    return;
  }

  const url = `${window.location.origin}${window.location.pathname}?pr=${selectedPR.id}`;

  if (navigator.share) {
    navigator.share({
        title: `Purchase Request ${selectedPR.id}`,
        text: `View Purchase Request ${selectedPR.id} - ${selectedPR.requestor}`,
        url: url,
      })
      .catch((err) => console.log("Share failed:", err));
  } else {
    navigator.clipboard
      .writeText(url)
      .then(() => {
        Swal.fire({ icon:'success', title:'Copied!', text:'Link copied to clipboard.', confirmButtonColor:'#22c55e', background:'#2a2a3a', color:'#e0e0ff', timer:2000, showConfirmButton:false });
      })
      .catch((err) => {
        Swal.fire({ icon:'info', title:'Copy Manually', text:'Unable to copy automatically. URL: ' + url, confirmButtonColor:'#22c55e', background:'#2a2a3a', color:'#e0e0ff' });
      });
  }
}
