// user-management.js - Full User Management with SweetAlert2 for Edit Success

let currentUserId = null;
let allUsers = [];

// Password validation rules
const passwordRules = {
  length: /.{8,}/,
  capital: /[A-Z]/,
  number: /[0-9].*[0-9].*[0-9]/,
  special: /[!@#$%^&*(),.?":{}|<>]/,
};

// ──────────────────────────────────────────────
//  TOAST NOTIFICATION (used for errors, add, etc.)
// ──────────────────────────────────────────────
function showNotification(message, type = "info") {
  const noti = document.getElementById("notification");
  const msgEl = document.getElementById("notificationMessage");

  if (!noti || !msgEl) return;

  msgEl.textContent = message;
  noti.className = `notification ${type} show`;

  const timeout = setTimeout(() => {
    noti.classList.remove("show");
    setTimeout(() => (noti.className = "notification hidden"), 400);
  }, 4500);

  const closeBtn = noti.querySelector(".notification-close");
  const closeHandler = () => {
    clearTimeout(timeout);
    noti.classList.remove("show");
    setTimeout(() => (noti.className = "notification hidden"), 400);
    closeBtn.removeEventListener("click", closeHandler);
  };
  closeBtn.addEventListener("click", closeHandler);
}

// ──────────────────────────────────────────────
//  DOM READY - Load users + attach events
// ──────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
  console.log("User Management Loaded – Fetching users...");
  loadUsers();

  // Search input
  const searchInput = document.getElementById("searchInput");
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      filterUsers(this.value.trim().toLowerCase());
    });
  }

  // Password validation for add form
  const passwordInput = document.getElementById("addPassword");
  const confirmInput = document.getElementById("addConfirmPassword");
  const submitBtn = document.getElementById("addSubmitBtn");

  if (passwordInput && confirmInput && submitBtn) {
    const validatePassword = () => {
      const pass = passwordInput.value;
      const confirm = confirmInput.value;

      document
        .getElementById("length")
        ?.classList.toggle("valid", passwordRules.length.test(pass));
      document
        .getElementById("capital")
        ?.classList.toggle("valid", passwordRules.capital.test(pass));
      document
        .getElementById("number")
        ?.classList.toggle("valid", passwordRules.number.test(pass));
      document
        .getElementById("special")
        ?.classList.toggle("valid", passwordRules.special.test(pass));
      document
        .getElementById("match")
        ?.classList.toggle("valid", pass === confirm && pass !== "");

      const allValid =
        passwordRules.length.test(pass) &&
        passwordRules.capital.test(pass) &&
        passwordRules.number.test(pass) &&
        passwordRules.special.test(pass) &&
        pass === confirm &&
        pass !== "";

      submitBtn.disabled = !allValid;
    };

    passwordInput.addEventListener("input", validatePassword);
    confirmInput.addEventListener("input", validatePassword);
  }

  // Add form submission
  document
    .getElementById("addForm")
    ?.addEventListener("submit", async function (e) {
      e.preventDefault();

      const password = document.getElementById("addPassword").value;
      const confirm = document.getElementById("addConfirmPassword").value;

      if (password !== confirm) {
        showNotification("Passwords do not match!", "error");
        return;
      }

      if (
        !passwordRules.length.test(password) ||
        !passwordRules.capital.test(password) ||
        !passwordRules.number.test(password) ||
        !passwordRules.special.test(password)
      ) {
        showNotification(
          "Password does not meet all security requirements!",
          "error",
        );
        return;
      }

      const data = {
        firstname: document.getElementById("addFirstName").value.trim(),
        lastname: document.getElementById("addLastName").value.trim(),
        middlename: document.getElementById("addMiddleName").value.trim(),
        username: document.getElementById("addUsername").value.trim(),
        email: document.getElementById("addEmail").value.trim(),
        password: password,
        gender: document.getElementById("addGender").value,
        role: document.getElementById("addRole").value,
      };

      try {
        const response = await fetch("../PHP/add-user.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(data),
        });

        const result = await response.json();

        showNotification(
          result.message || "Operation completed",
          result.success ? "success" : "error",
        );

        if (result.success) {
          closeAddModal();
          document.getElementById("addForm").reset();
          document
            .querySelectorAll("#passwordRequirements li")
            .forEach((li) => li.classList.remove("valid"));
          submitBtn.disabled = true;
          loadUsers();
        }
      } catch (err) {
        showNotification("Error adding user: " + err.message, "error");
      }
    });

  // Edit form submission - SUCCESS now uses SweetAlert2 popup
  document
    .getElementById("editForm")
    ?.addEventListener("submit", async function (e) {
      e.preventDefault();

      const data = {
        id: currentUserId,
        firstname: document.getElementById("editFirstName").value.trim(),
        lastname: document.getElementById("editLastName").value.trim(),
        middlename: document.getElementById("editMiddleName").value.trim(),
        username: document.getElementById("editUsername").value.trim(),
        email: document.getElementById("editEmail").value.trim(),
        gender: document.getElementById("editGender").value,
        role: document.getElementById("editRole").value,
      };

      try {
        const response = await fetch("../PHP/edit-user.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(data),
        });

        const result = await response.json();

        if (result.success) {
          // Show SweetAlert2 success popup
          await Swal.fire({
            title: "Success!",
            text: "User edited successfully",
            icon: "success",
            confirmButtonColor: "#22c55e",
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false,
          });

          closeEditModal();
          loadUsers(); // Refresh table
        } else {
          showNotification(result.message || "Failed to edit user", "error");
        }
      } catch (err) {
        showNotification("Error editing user: " + err.message, "error");
      }
    });
});

// ──────────────────────────────────────────────
//  LOAD & DISPLAY USERS
// ──────────────────────────────────────────────
async function loadUsers() {
  try {
    const response = await fetch("../PHP/fetch-users.php");
    if (!response.ok) throw new Error(`HTTP ${response.status}`);

    const users = await response.json();
    allUsers = users;
    displayUsers(users);
  } catch (err) {
    document.getElementById("employeeTableBody").innerHTML = `
      <tr>
        <td colspan="9" style="text-align:center; padding:40px; color:#f87171;">
          Error loading users: ${err.message}
        </td>
      </tr>`;
  }
}

function displayUsers(users) {
  const tbody = document.getElementById("employeeTableBody");
  tbody.innerHTML = "";

  if (users.length === 0) {
    tbody.innerHTML = `<tr><td colspan="9" class="no-data">No users found</td></tr>`;
    return;
  }

  users.forEach((u) => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${u.id || "—"}</td>
      <td>${u.username || "—"}</td>
      <td>${u.lastname || "—"}</td>
      <td>${u.firstname || "—"}</td>
      <td>${u.middlename || "—"}</td>
      <td>${u.role || "—"}</td>
      <td>${u.gender || "—"}</td>
      <td>${u.email || "—"}</td>
      <td class="actions">
        <button class="btn-edit" onclick="openEditModal(
          ${u.id || 0},
          '${(u.firstname || "").replace(/'/g, "\\'")}',
          '${(u.lastname || "").replace(/'/g, "\\'")}',
          '${(u.middlename || "").replace(/'/g, "\\'")}',
          '${(u.username || "").replace(/'/g, "\\'")}',
          '${(u.email || "").replace(/'/g, "\\'")}',
          '${(u.gender || "").replace(/'/g, "\\'")}',
          '${(u.role || "").replace(/'/g, "\\'")}'
        )">
          <i class="fas fa-edit"></i>
        </button>
        <button class="btn-delete" onclick="deleteUser(${u.id || 0})">
          <i class="fas fa-trash-alt"></i>
        </button>
      </td>
    `;
    tbody.appendChild(row);
  });
}

function filterUsers(query) {
  if (!query) {
    displayUsers(allUsers);
    return;
  }

  const filtered = allUsers.filter((user) => {
    return (
      String(user.id).toLowerCase().includes(query) ||
      (user.username || "").toLowerCase().includes(query) ||
      (user.firstname || "").toLowerCase().includes(query) ||
      (user.lastname || "").toLowerCase().includes(query) ||
      (user.middlename || "").toLowerCase().includes(query) ||
      (user.email || "").toLowerCase().includes(query) ||
      (user.role || "").toLowerCase().includes(query) ||
      (user.gender || "").toLowerCase().includes(query)
    );
  });

  displayUsers(filtered);
}

// Alias for HTML onkeyup="searchTable()"
function searchTable() {
  const searchInput = document.getElementById("searchInput");
  if (searchInput) filterUsers(searchInput.value.trim().toLowerCase());
}

// ──────────────────────────────────────────────
//  DELETE USER – SWEETALERT2 CONFIRMATION
// ──────────────────────────────────────────────
async function deleteUser(id) {
  const swalResult = await Swal.fire({
    title: "Delete this user?",
    text: "This action cannot be undone. The user will be permanently removed.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#6b7280",
    confirmButtonText: "Yes, delete",
    cancelButtonText: "Cancel",
    reverseButtons: true,
  });

  if (!swalResult.isConfirmed) return;

  try {
    const response = await fetch("../PHP/delete-user.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ id: id }),
    });

    const result = await response.json();

    showNotification(
      result.message ||
        (result.success ? "User deleted successfully" : "Operation failed"),
      result.success ? "success" : "error",
    );

    if (result.success) {
      loadUsers();
    }
  } catch (err) {
    showNotification("Error deleting user: " + err.message, "error");
  }
}

// ──────────────────────────────────────────────
//  MODAL CONTROLS
// ──────────────────────────────────────────────
function openAddModal() {
  const modal = document.getElementById("addModal");
  if (modal) {
    modal.style.display = "flex";
    document.getElementById("addForm").reset();
    document.getElementById("addSubmitBtn").disabled = true;
    document
      .querySelectorAll("#passwordRequirements li")
      .forEach((li) => li.classList.remove("valid"));
  }
}

function closeAddModal() {
  const modal = document.getElementById("addModal");
  if (modal) modal.style.display = "none";
}

function openEditModal(
  id,
  firstname,
  lastname,
  middlename,
  username,
  email,
  gender,
  role,
) {
  currentUserId = id;
  document.getElementById("editFirstName").value = firstname;
  document.getElementById("editLastName").value = lastname;
  document.getElementById("editMiddleName").value = middlename;
  document.getElementById("editUsername").value = username || "";
  document.getElementById("editEmail").value = email;
  document.getElementById("editGender").value = gender;
  document.getElementById("editRole").value = role;
  document.getElementById("editModal").style.display = "flex";
}

function closeEditModal() {
  document.getElementById("editModal").style.display = "none";
}

// Close modals when clicking outside
window.onclick = function (event) {
  if (event.target.classList.contains("modal")) {
    event.target.style.display = "none";
  }
};

// Close modals with Escape key
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") {
    closeAddModal();
    closeEditModal();
  }
});
