// history.js - Unified History: Budget Transactions + Purchase Request Approvals

let currentOffset = 0;
const limit = 20;
let currentFilter = "all";
let currentDate = "";

// Config for displayed events
const activityConfig = {
  // Budget transactions
  add: {
    icon: "fa-plus-circle",
    color: "#22c55e",
    title: "Budget Added",
  },
  deduct: {
    icon: "fa-minus-circle",
    color: "#f87171",
    title: "Budget Deducted",
  },
  spend: {
    icon: "fa-hand-holding-usd",
    color: "#f97316",
    title: "Budget Spent",
  },
  // Purchase request approval
  request_approved: {
    icon: "fa-check-circle",
    color: "#4ade80",
    title: "PR Approved",
  },
};

document.addEventListener("DOMContentLoaded", () => {
  loadHistory();
  setupFilters();
});

async function loadHistory(append = false) {
  const timeline = document.getElementById("timeline");
  const loadingState = document.getElementById("loadingState");
  const emptyState = document.getElementById("emptyState");
  const loadMoreContainer = document.getElementById("loadMoreContainer");

  if (!append) {
    loadingState.style.display = "block";
    timeline.innerHTML = "";
    if (loadingState) timeline.appendChild(loadingState);
    currentOffset = 0;
  }

  try {
    const url = `../PHP/fetch_history.php?type=${currentFilter}&date=${currentDate}&limit=${limit}&offset=${currentOffset}&category=all`;
    const response = await fetch(url);
    const data = await response.json();

    loadingState.style.display = "none";

    if (!data.success || !data.activities) {
      emptyState.style.display = "block";
      if (loadMoreContainer) loadMoreContainer.style.display = "none";
      return;
    }

    const activities = data.activities;

    // Filter to show only budget transactions + approved PRs
    const relevant = activities.filter((act) => {
      const type = (act.activity_type || "").toLowerCase();
      return (
        type === "add" ||
        type === "deduct" ||
        type === "spend" ||
        type === "request_approved"
      );
    });

    if (relevant.length === 0 && !append) {
      emptyState.style.display = "block";
      if (loadMoreContainer) loadMoreContainer.style.display = "none";
      return;
    }

    emptyState.style.display = "none";

    relevant.forEach((act) => {
      const item = createTimelineItem(act);
      timeline.appendChild(item);
    });

    if (loadMoreContainer) {
      loadMoreContainer.style.display =
        relevant.length === limit ? "block" : "none";
    }

    currentOffset += relevant.length;

    // Make timeline scrollable and clean
    timeline.style.maxHeight = "580px";
    timeline.style.overflowY = "auto";
    timeline.style.overflowX = "hidden";
    timeline.style.padding = "16px 20px";
    timeline.style.borderRadius = "12px";
    timeline.style.background = "#222233";
    timeline.style.border = "1px solid #3a3a4f";
    timeline.style.scrollbarWidth = "thin";
    timeline.style.scrollbarColor = "#555 #333";

    // Custom scrollbar
    const scrollStyle = document.createElement("style");
    scrollStyle.textContent = `
      #timeline::-webkit-scrollbar { width: 8px; }
      #timeline::-webkit-scrollbar-track { background: #2a2a3a; border-radius: 10px; }
      #timeline::-webkit-scrollbar-thumb { background: #555; border-radius: 10px; }
      #timeline::-webkit-scrollbar-thumb:hover { background: #777; }
    `;
    document.head.appendChild(scrollStyle);
  } catch (error) {
    console.error("Error loading history:", error);
    loadingState.style.display = "none";
    emptyState.style.display = "block";
  }
}

function createTimelineItem(act) {
  const type = (act.activity_type || "").toLowerCase();
  let config = activityConfig[type];

  let displayText = "";

  // Budget transaction
  if (type === "add" || type === "deduct" || type === "spend") {
    const amount = Number(act.amount) || 0;
    const sign = amount >= 0 ? "+" : "-";
    const absAmount = Math.abs(amount).toFixed(2);
    displayText = `${config.title} ${sign}$${absAmount}`;
  }
  // PR approval
  else if (type === "request_approved") {
    displayText = act.pr_number
      ? `${act.pr_number} - PR Approved`
      : "PR Approved";
  }
  // Fallback
  else {
    config = { icon: "fa-info-circle", color: "#60a5fa", title: "Activity" };
    displayText = act.description || "Activity";
  }

  const item = document.createElement("div");
  item.className = "timeline-item";

  const timeAgo = formatTimeAgo(act.created_at);
  const formattedTime = formatDateTime(act.created_at);

  item.innerHTML = `
    <div class="timeline-icon" style="background: ${config.color}">
      <i class="fas ${config.icon}"></i>
    </div>
    <div class="timeline-content">
      <div class="timeline-header">
        <h3 style="color: ${config.color}">
          ${displayText}
        </h3>
        <span class="time">${timeAgo}</span>
      </div>
      <p>${act.description || "No description available"}</p>
    </div>
  `;

  return item;
}

function formatDateTime(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString("en-PH", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

function formatTimeAgo(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const seconds = Math.floor((now - date) / 1000);

  if (seconds < 60) return "Just now";
  if (seconds < 3600) return `${Math.floor(seconds / 60)} min ago`;
  if (seconds < 86400) return `${Math.floor(seconds / 3600)} hr ago`;
  const days = Math.floor(seconds / 86400);
  if (days < 7) return days === 1 ? "Yesterday" : `${days} days ago`;
  return formatDateTime(dateString);
}

function setupFilters() {
  const typeFilter = document.getElementById("activityTypeFilter");
  const dateFilter = document.getElementById("dateFilter");
  const clearBtn = document.getElementById("clearFilters");
  const loadMoreBtn = document.getElementById("loadMoreBtn");

  if (typeFilter) {
    typeFilter.addEventListener("change", () => {
      currentFilter = typeFilter.value;
      loadHistory(false);
    });
  }

  if (dateFilter) {
    dateFilter.addEventListener("change", () => {
      currentDate = dateFilter.value;
      loadHistory(false);
    });
  }

  if (clearBtn) {
    clearBtn.addEventListener("click", () => {
      if (typeFilter) typeFilter.value = "all";
      if (dateFilter) dateFilter.value = "";
      currentFilter = "all";
      currentDate = "";
      loadHistory(false);
    });
  }

  if (loadMoreBtn) {
    loadMoreBtn.addEventListener("click", () => {
      loadHistory(true);
    });
  }
}
