// history.js - Admin History: Shows activity logs including finance activities

let currentOffset = 0;
const limit = 20;
let currentFilter = "all";
let currentDate = "";

// Activity type configuration
const activityConfig = {
  user_added: {
    icon: "fa-user-plus",
    color: "#a78bfa",
    title: "User Added",
  },
  user_edited: {
    icon: "fa-user-edit",
    color: "#60a5fa",
    title: "User Edited",
  },
  user_deleted: {
    icon: "fa-user-minus",
    color: "#f87171",
    title: "User Deleted",
  },
  request_created: {
    icon: "fa-file-medical",
    color: "#fbbf24",
    title: "Request Created",
  },
  request_approved: {
    icon: "fa-check-circle",
    color: "#22c55e",
    title: "Request Approved",
  },
  request_rejected: {
    icon: "fa-times-circle",
    color: "#f87171",
    title: "Request Rejected",
  },
  request_pending: {
    icon: "fa-clock",
    color: "#60a5fa",
    title: "Request Pending",
  },
  request_cancelled: {
    icon: "fa-ban",
    color: "#9ca3af",
    title: "Request Cancelled",
  },
  budget_insufficient: {
    icon: "fa-exclamation-triangle",
    color: "#f59e0b",
    title: "Insufficient Budget",
  },
  request_finance_approved: {
    icon: "fa-check-double",
    color: "#10b981",
    title: "Finance Approved",
  },
  request_finance_rejected: {
    icon: "fa-ban",
    color: "#ef4444",
    title: "Finance Rejected",
  },
  purchase: {
    icon: "fa-shopping-cart",
    color: "#8b5cf6",
    title: "Purchase",
  },
  login: {
    icon: "fa-sign-in-alt",
    color: "#3b82f6",
    title: "Login",
  },
  logout: {
    icon: "fa-sign-out-alt",
    color: "#6b7280",
    title: "Logout",
  },
};

document.addEventListener("DOMContentLoaded", () => {
  loadActivities();
  setupFilters();
  loadStats();

  const modal = document.getElementById("activityModal");
  const closeBtn = document.getElementById("closeActivityModal");

  if (closeBtn)
    closeBtn.addEventListener("click", () => (modal.style.display = "none"));
  if (modal)
    modal.addEventListener("click", (e) => {
      if (e.target === modal) modal.style.display = "none";
    });
});

async function loadActivities(append = false) {
  const timeline = document.getElementById("timeline");
  const loadingState = document.getElementById("loadingState");
  const emptyState = document.getElementById("emptyState");
  const loadMoreContainer = document.getElementById("loadMoreContainer");

  if (!append) {
    loadingState.style.display = "block";
    timeline.innerHTML = "";
    timeline.appendChild(loadingState);
    currentOffset = 0;
  }

  try {
    const url = `../../Admin/PHP/fetch_history.php?type=${currentFilter}&date=${currentDate}&limit=${limit}&offset=${currentOffset}`;
    const response = await fetch(url);
    const data = await response.json();

    loadingState.style.display = "none";

    if (!data.success) {
      emptyState.style.display = "block";
      return;
    }

    let activities = data.activities || [];

    // Filter to only show known activity types
    activities = activities.filter((activity) =>
      activityConfig.hasOwnProperty(activity.activity_type),
    );

    if (activities.length === 0 && !append) {
      emptyState.style.display = "block";
      loadMoreContainer.style.display = "none";
      return;
    }

    emptyState.style.display = "none";

    activities.forEach((activity) => {
      timeline.appendChild(createTimelineItem(activity));
    });

    loadMoreContainer.style.display =
      activities.length === limit ? "block" : "none";

    currentOffset += activities.length;

    // Scrollable area styling
    timeline.style.maxHeight = "680px";
    timeline.style.overflowY = "auto";
    timeline.style.overflowX = "hidden";
    timeline.style.padding = "12px 20px";
    timeline.style.borderRadius = "10px";
    timeline.style.background = "#1e1e2e";
    timeline.style.border = "1px solid #444";
    timeline.style.scrollbarWidth = "thin";
    timeline.style.scrollbarColor = "#555 #333";

    const style = document.createElement("style");
    style.textContent = `
      #timeline::-webkit-scrollbar { width: 8px; }
      #timeline::-webkit-scrollbar-track { background: #2a2a3a; border-radius: 10px; }
      #timeline::-webkit-scrollbar-thumb { background: #555; border-radius: 10px; }
      #timeline::-webkit-scrollbar-thumb:hover { background: #777; }
    `;
    document.head.appendChild(style);
  } catch (error) {
    console.error("Error loading activities:", error);
    loadingState.style.display = "none";
    emptyState.style.display = "block";
  }
}

function createTimelineItem(activity) {
  const config = activityConfig[activity.activity_type];

  const item = document.createElement("div");
  item.className = "timeline-item";

  const timeAgo = formatTimeAgo(activity.created_at);
  const formattedTime = formatDateTime(activity.created_at);

  const isClickable = !!activity.details || !!activity.pr_number;
  const clickableClass = isClickable ? "clickable" : "";

  item.innerHTML = `
    <div class="timeline-icon" style="background: ${config.color}">
      <i class="fas ${config.icon}"></i>
    </div>
    <div class="timeline-content ${clickableClass}" data-activity-id="${activity.id}">
      <div class="timeline-header">
        <h3>${getActivityTitle(activity)}</h3>
        <span class="time" title="${formattedTime}">${timeAgo}</span>
      </div>
      <p>${activity.description}</p>
      ${activity.details ? `<small>${activity.details.substring(0, 150)}${activity.details.length > 150 ? "..." : ""}</small>` : ""}
      ${isClickable ? '<small class="view-details"><i class="fas fa-eye"></i> View details</small>' : ""}
    </div>
  `;

  if (isClickable) {
    const content = item.querySelector(".timeline-content");
    content.style.cursor = "pointer";
    content.addEventListener("click", () => showActivityDetails(activity));
  }

  return item;
}

function getActivityTitle(activity) {
  const config = activityConfig[activity.activity_type];
  return activity.pr_number
    ? `${activity.pr_number} - ${config.title}`
    : config.title;
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
  if (seconds < 604800) {
    const days = Math.floor(seconds / 86400);
    return days === 1 ? "Yesterday" : `${days} days ago`;
  }

  return formatDateTime(dateString);
}

function setupFilters() {
  const typeFilter = document.getElementById("activityTypeFilter");
  const dateFilter = document.getElementById("dateFilter");
  const clearBtn = document.getElementById("clearFilters");
  const loadMoreBtn = document.getElementById("loadMoreBtn");

  typeFilter.addEventListener("change", () => {
    currentFilter = typeFilter.value;
    loadActivities(false);
    loadStats();
  });

  dateFilter.addEventListener("change", () => {
    currentDate = dateFilter.value;
    loadActivities(false);
  });

  clearBtn.addEventListener("click", () => {
    typeFilter.value = "all";
    dateFilter.value = "";
    currentFilter = "all";
    currentDate = "";
    loadActivities(false);
    loadStats();
  });

  loadMoreBtn.addEventListener("click", () => loadActivities(true));
}

async function loadStats() {
  try {
    const response = await fetch(
      "../../Admin/PHP/fetch_history.php?type=all&limit=1000",
    );
    const data = await response.json();

    if (!data.success) return;

    const activities = data.activities || [];

    // User activities
    const userAdded = activities.filter(
      (a) => a.activity_type === "user_added",
    ).length;
    const userEdited = activities.filter(
      (a) => a.activity_type === "user_edited",
    ).length;
    const userDeleted = activities.filter(
      (a) => a.activity_type === "user_deleted",
    ).length;
    const totalUserChanges = userAdded + userEdited + userDeleted;

    // Request activities
    const approved = activities.filter(
      (a) =>
        a.activity_type === "request_approved" ||
        a.activity_type === "request_finance_approved",
    ).length;
    const rejected = activities.filter(
      (a) =>
        a.activity_type === "request_rejected" ||
        a.activity_type === "request_finance_rejected",
    ).length;

    // Budget issues
    const budgetIssues = activities.filter(
      (a) => a.activity_type === "budget_insufficient",
    ).length;

    document.getElementById("totalActivities").textContent =
      activities.length || 0;
    document.getElementById("totalApproved").textContent = approved;
    document.getElementById("totalRejected").textContent = rejected;
    document.getElementById("totalBudgetIssues").textContent = budgetIssues;
  } catch (error) {
    console.error("Error loading stats:", error);
  }
}

function showActivityDetails(activity) {
  const modal = document.getElementById("activityModal");
  const modalTitle = document.getElementById("modalTitle");
  const modalBody = document.getElementById("modalBody");

  const config = activityConfig[activity.activity_type];

  modalTitle.textContent = getActivityTitle(activity);

  let detailsHTML = `
    <div class="modal-detail-wrapper">
      <div class="modal-status-header" style="background: ${config.color}15; border-left: 5px solid ${config.color};">
        <div class="status-icon" style="background: ${config.color};">
          <i class="fas ${config.icon}"></i>
        </div>
        <div class="status-info">
          <h3>${config.title}</h3>
          <span class="status-time">${formatDateTime(activity.created_at)}</span>
        </div>
      </div>

      <div class="modal-info-grid">
        <div class="modal-info-item">
          <span class="modal-label">Performed By</span>
          <span class="modal-value">${activity.performed_by || "System"}</span>
        </div>

        ${
          activity.pr_number
            ? `
          <div class="modal-info-item">
            <span class="modal-label">PR Number</span>
            <span class="modal-value pr-number">${activity.pr_number}</span>
          </div>
        `
            : ""
        }

        ${
          activity.target_user
            ? `
          <div class="modal-info-item">
            <span class="modal-label">Target User</span>
            <span class="modal-value">${activity.target_user}</span>
          </div>
        `
            : ""
        }
      </div>

      <div class="modal-section">
        <h4>Description</h4>
        <p class="modal-description">${activity.description}</p>
      </div>

      ${
        activity.details
          ? `
        <div class="modal-section">
          <h4>Details</h4>
          <ul class="modal-detail-list">
            ${formatDetailsHTML(activity.details)}
          </ul>
        </div>
      `
          : ""
      }
    </div>
  `;

  modalBody.innerHTML = detailsHTML;
  modal.style.display = "flex";
}

function formatDetailsHTML(details) {
  return details
    .split(",")
    .map((item) => item.trim())
    .filter((item) => item)
    .map((item) => `<li>${item}</li>`)
    .join("");
}
