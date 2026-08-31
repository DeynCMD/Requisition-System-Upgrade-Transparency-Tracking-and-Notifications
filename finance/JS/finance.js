// finance.js - Fixed for ngrok + session auth (credentials: 'include')

let budgetData = {
  total: 0,
  spent: 0,
  remaining: 0,
};

let transactions = [];

// Initialize
document.addEventListener("DOMContentLoaded", () => {
  loadBudgetData();
  setupFormHandlers();
});

// Load budget data with credentials
async function loadBudgetData() {
  try {
    const response = await fetch("../../Admin/PHP/get_budget_data.php", {
      method: "GET",
      credentials: "include", // CRITICAL: sends PHP session cookie
      headers: {
        "ngrok-skip-browser-warning": "true",
      },
    });

    if (!response.ok) {
      if (response.status === 401 || response.status === 403) {
        throw new Error("Unauthorized - Please log in again");
      }
      throw new Error(`HTTP ${response.status} - ${response.statusText}`);
    }

    const data = await response.json();

    if (data.success) {
      budgetData = data.budget || { total: 0, spent: 0, remaining: 0 };
      transactions = data.transactions || [];

      updateBudgetDisplay();
      renderTransactions();
    } else {
      console.error("Server error:", data.message);
      showError("Failed to load budget: " + (data.message || "Unknown error"));
    }
  } catch (error) {
    console.error("Fetch error:", error);
    showError(
      "Connection error: " +
        error.message +
        "<br>Please log in again or refresh.",
    );
  }
}

function showError(msg) {
  const container = document.getElementById("transactionHistory");
  if (container) {
    container.innerHTML = `<p style="text-align:center; color:#f87171; padding:30px;">${msg}</p>`;
  }
}

// Update UI
function updateBudgetDisplay() {
  document.getElementById("totalBudget").textContent = formatCurrency(
    budgetData.total,
  );
  document.getElementById("spentBudget").textContent = formatCurrency(
    budgetData.spent,
  );
  document.getElementById("remainingBudget").textContent = formatCurrency(
    budgetData.remaining,
  );

  const spentPercent =
    budgetData.total > 0 ? (budgetData.spent / budgetData.total) * 100 : 0;
  const remainingPercent = 100 - spentPercent;

  document.getElementById("spentPercentage").textContent =
    `${spentPercent.toFixed(1)}% of total`;
  document.getElementById("remainingPercentage").textContent =
    `${remainingPercent.toFixed(1)}% available`;

  document.getElementById("spentProgress").style.width =
    `${Math.min(spentPercent, 100)}%`;
  document.getElementById("remainingProgress").style.width =
    `${Math.min(remainingPercent, 100)}%`;
}

// Render transactions
function renderTransactions() {
  const container = document.getElementById("transactionHistory");
  container.innerHTML = "";

  if (transactions.length === 0) {
    container.innerHTML =
      '<p style="text-align:center; color:#888; padding:30px;">No transactions yet</p>';
    return;
  }

  transactions.slice(0, 5).forEach((trans) => {
    const item = document.createElement("div");
    item.className = "transaction-item";
    item.innerHTML = `
      <span class="transaction-type ${trans.type}">${trans.type.toUpperCase()}</span>
      <span class="transaction-desc">
        ${trans.description} 
        <small>(${formatCurrency(trans.amount)})</small>
      </span>
      <span class="transaction-date">${formatDateTime(trans.created_at)}</span>
    `;
    container.appendChild(item);
  });
}

// Form handlers with credentials
function setupFormHandlers() {
  // Add Budget
  document
    .getElementById("addBudgetForm")
    ?.addEventListener("submit", async (e) => {
      e.preventDefault();
      const amount = parseFloat(document.getElementById("addAmount").value);
      const description = document
        .getElementById("addDescription")
        .value.trim();

      if (isNaN(amount) || amount <= 0) {
        Swal.fire({ icon:'warning', title:'Invalid Amount', text:'Please enter a valid amount greater than 0.', confirmButtonColor:'#22c55e', background:'#2a2a3a', color:'#e0e0ff' });
        return;
      }

      try {
        const response = await fetch("../../Admin/PHP/add_budget.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          credentials: "include",
          body: JSON.stringify({ amount, description }),
        });

        const result = await response.json();

        Swal.fire({
          icon: result.success ? 'success' : 'error',
          title: result.success ? 'Budget Added' : 'Failed',
          text: result.message || (result.success ? 'Budget added successfully!' : 'Failed to add budget'),
          confirmButtonColor: result.success ? '#22c55e' : '#ef4444',
          background: '#2a2a3a', color: '#e0e0ff'
        });

        if (result.success) {
          closeModal("addBudgetModal");
          loadBudgetData();
          document.getElementById("addBudgetForm").reset();
        }
      } catch (err) {
        Swal.fire({ icon:'error', title:'Error', text:'Error adding budget: ' + err.message, confirmButtonColor:'#ef4444', background:'#2a2a3a', color:'#e0e0ff' });
      }
    });

  // Adjust Budget
  document
    .getElementById("adjustForm")
    ?.addEventListener("submit", async (e) => {
      e.preventDefault();
      const type = document.getElementById("adjustType").value;
      const amount = parseFloat(document.getElementById("adjustAmount").value);
      const reason = document.getElementById("adjustReason").value.trim();

      if (isNaN(amount) || amount <= 0) {
        Swal.fire({ icon:'warning', title:'Invalid Amount', text:'Please enter a valid amount greater than 0.', confirmButtonColor:'#22c55e', background:'#2a2a3a', color:'#e0e0ff' });
        return;
      }

      try {
        const response = await fetch("../../Admin/PHP/adjust_budget.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          credentials: "include",
          body: JSON.stringify({ type, amount, reason }),
        });

        const result = await response.json();

        Swal.fire({
          icon: result.success ? 'success' : 'error',
          title: result.success ? 'Budget Adjusted' : 'Failed',
          text: result.message || (result.success ? 'Budget adjusted!' : 'Failed to adjust budget'),
          confirmButtonColor: result.success ? '#22c55e' : '#ef4444',
          background: '#2a2a3a', color: '#e0e0ff'
        });

        if (result.success) {
          closeModal("adjustModal");
          loadBudgetData();
          document.getElementById("adjustForm").reset();
        }
      } catch (err) {
        Swal.fire({ icon:'error', title:'Error', text:'Error adjusting budget: ' + err.message, confirmButtonColor:'#ef4444', background:'#2a2a3a', color:'#e0e0ff' });
      }
    });
}

// Modal helpers
function openAddBudgetModal() {
  document.getElementById("addBudgetModal").style.display = "flex";
}

function openAdjustModal() {
  document.getElementById("adjustModal").style.display = "flex";
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) modal.style.display = "none";
}

// Close modals on outside click / Escape
window.onclick = (e) => {
  if (e.target.classList.contains("modal")) {
    e.target.style.display = "none";
  }
};

document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") {
    document
      .querySelectorAll(".modal")
      .forEach((m) => (m.style.display = "none"));
  }
});

// Format helpers
function formatCurrency(amount) {
  return (
    "₱" +
    Number(amount).toLocaleString("en-PH", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })
  );
}

function formatDateTime(dateStr) {
  if (!dateStr) return "N/A";
  return new Date(dateStr).toLocaleString("en-PH", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

// Auto-refresh every 30 seconds
setInterval(loadBudgetData, 30000);
