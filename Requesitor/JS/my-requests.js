// my-requests.js

document.addEventListener("DOMContentLoaded", () => {
  // === Modal Elements ===
  const modal = document.getElementById("requestModal");
  const closeModal = document.getElementById("closeModal");
  const modalBody = document.getElementById("modalBody");

  // === Filter Elements ===
  const statusFilter = document.getElementById("statusFilter");
  const dateFilter = document.getElementById("dateFilter");
  const clearFiltersBtn = document.getElementById("clearFilters");

  // === All timeline items ===
  const timelineItems = document.querySelectorAll(".timeline-item");

  // === Open modal when clicking a request ===
  timelineItems.forEach((item) => {
    const content = item.querySelector(".timeline-content");
    if (content) {
      content.addEventListener("click", () => {
        const requestId = item.dataset.id;

        // Show loading state
        modalBody.innerHTML = `
                    <div class="loading-state">
                        <i class="fas fa-spinner fa-spin fa-3x"></i>
                        <p>Loading request details...</p>
                    </div>
                `;
        modal.style.display = "flex";

        // Fetch details via AJAX
        fetch(`../../Admin/PHP/get_request_detail.php?id=${requestId}`)
          .then((response) => {
            if (!response.ok) {
              throw new Error("Network response was not ok");
            }
            return response.text();
          })
          .then((html) => {
            modalBody.innerHTML = html;
          })
          .catch((error) => {
            console.error("Error loading details:", error);
            modalBody.innerHTML = `
                            <p style="color: var(--red); text-align: center; padding: 20px;">
                                <i class="fas fa-exclamation-triangle"></i> 
                                Error loading request details. Please try again.
                            </p>
                        `;
          });
      });
    }
  });

  // === Close modal ===
  closeModal.addEventListener("click", () => {
    modal.style.display = "none";
  });

  // Close modal when clicking outside
  window.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.style.display = "none";
    }
  });

  // === Filter functionality ===
  function applyFilters() {
    const selectedStatus = statusFilter.value.toLowerCase();
    const selectedDate = dateFilter.value; // YYYY-MM-DD format

    timelineItems.forEach((item) => {
      const itemStatus = item.dataset.status; // e.g. 'pending', 'approved'
      const itemDate = item.dataset.date; // e.g. '2025-01-15'

      let shouldShow = true;

      // Status filter
      if (selectedStatus !== "all" && itemStatus !== selectedStatus) {
        shouldShow = false;
      }

      // Date filter (exact match on request date)
      if (selectedDate && itemDate !== selectedDate) {
        shouldShow = false;
      }

      item.style.display = shouldShow ? "flex" : "none";
    });
  }

  // Apply filters when changing status or date
  statusFilter.addEventListener("change", applyFilters);
  dateFilter.addEventListener("change", applyFilters);

  // Clear filters button
  clearFiltersBtn.addEventListener("click", () => {
    statusFilter.value = "all";
    dateFilter.value = ""; // clear date input
    applyFilters();
  });

  // Optional: Initial filter application (if you want to pre-filter on load)
  // applyFilters();
});
