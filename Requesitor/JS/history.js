// history.js - Requestor History: Only Created / Approved / Rejected

let currentOffset = 0;
const limit = 20;
let currentFilter = "all";
let currentDate = "";

const activityConfig = {
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
    const url = `../PHP/fetch_history.php?type=${currentFilter}&date=${currentDate}&limit=${limit}&offset=${currentOffset}`;
    const response = await fetch(url);
    const data = await response.json();

    loadingState.style.display = "none";

    if (!data.success) {
      emptyState.style.display = "block";
      return;
    }

    let activities = data.activities || [];

    // Filter only the three types we want
    activities = activities.filter(
      (a) =>
        a.activity_type === "request_created" ||
        a.activity_type === "request_approved" ||
        a.activity_type === "request_rejected",
    );

    if (activities.length === 0 && !append) {
      emptyState.style.display = "block";
      loadMoreContainer.style.display = "none";
      return;
    }

    emptyState.style.display = "none";

    activities.forEach((activity) =>
      timeline.appendChild(createTimelineItem(activity)),
    );

    loadMoreContainer.style.display =
      activities.length === limit ? "block" : "none";
    currentOffset += activities.length;

    // Scrollable area
    timeline.style.maxHeight = "680px";
    timeline.style.overflowY = "auto";
    timeline.style.overflowX = "hidden";
    timeline.style.padding = "12px 20px";
    timeline.style.borderRadius = "10px";
    timeline.style.background = "#1e1e2e";
    timeline.style.border = "1px solid #444";
    timeline.style.scrollbarWidth = "thin";
    timeline.style.scrollbarColor = "#555 #333";

    // Scrollbar style
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
      "../PHP/fetch_history.php?type=all&limit=1000",
    );
    const data = await response.json();

    if (!data.success) return;

    const activities = data.activities || [];

    const created = activities.filter(
      (a) => a.activity_type === "request_created",
    ).length;
    const approved = activities.filter(
      (a) => a.activity_type === "request_approved",
    ).length;
    const rejected = activities.filter(
      (a) => a.activity_type === "request_rejected",
    ).length;

    document.getElementById("totalActivities").textContent =
      activities.length || 0;
    document.getElementById("totalApproved").textContent = approved;
    document.getElementById("totalRejected").textContent = rejected;
    document.getElementById("totalUsers").textContent = created;
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
          <span class="modal-label">Requestor</span>
          <span class="modal-value">${activity.performed_by || activity.requestor_name || "Unknown"}</span>
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
            <span class="modal-label">Department</span>
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
          <h4>Purchase Request Details</h4>
          <ul class="modal-detail-list">
            ${formatDetailsHTML(activity.details)}
          </ul>

          ${
            activity.activity_type === "request_rejected" &&
            activity.details.includes("Reason:")
              ? (() => {
                  const reasonMatch = activity.details.match(/Reason: ([^,]+)/);
                  return reasonMatch
                    ? `
              <div class="modal-notice rejection">
                <strong>Rejection Reason</strong>
                <p>${reasonMatch[1].trim()}</p>
              </div>
            `
                    : "";
                })()
              : ""
          }

          ${
            activity.activity_type === "request_approved" &&
            activity.approved_by
              ? `
            <div class="modal-notice approval">
              <strong>Approved By</strong>
              <p>${activity.approved_by}</p>
            </div>
          `
              : ""
          }
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
