document.addEventListener("DOMContentLoaded", () => {
  const urlParams = new URLSearchParams(window.location.search);
  const token = urlParams.get("token");

  const messageBox = document.getElementById("messageBox");
  const loadingState = document.getElementById("loadingState");
  const resetForm = document.getElementById("resetPasswordForm");
  const newPasswordInput = document.getElementById("newPassword");
  const confirmPasswordInput = document.getElementById("confirmPassword");
  const submitBtn = document.getElementById("submitBtn");
  const btnText = document.getElementById("btnText");
  const btnLoader = document.getElementById("btnLoader");

  // Add password strength display element
  const strengthMessage = document.createElement("small");
  strengthMessage.className = "password-strength";
  strengthMessage.style.display = "block";
  strengthMessage.style.marginTop = "8px";
  newPasswordInput.parentNode.appendChild(strengthMessage);

  // Check if token exists
  if (!token) {
    showError("Invalid reset link. Please request a new password reset.");
    loadingState.style.display = "none";
    return;
  }

  // Verify token on page load
  verifyToken();

  // Handle form submission
  resetForm.addEventListener("submit", handleSubmit);

  // Real-time password validation
  newPasswordInput.addEventListener("input", updatePasswordStrength);

  // ================= VERIFY TOKEN =================
  async function verifyToken() {
    try {
      const response = await fetch(
        "http://localhost:3000/api/auth/verify-token",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ token }),
        },
      );

      const data = await response.json();

      if (response.ok && data.valid) {
        loadingState.style.display = "none";
        resetForm.style.display = "block";
      } else {
        throw new Error(data.error || "Invalid reset link");
      }
    } catch (error) {
      console.error("Token verification failed:", error);
      loadingState.style.display = "none";
      showError(error.message || "This reset link is invalid or has expired.");
    }
  }

  // ================= HANDLE FORM SUBMISSION =================
  async function handleSubmit(e) {
    e.preventDefault();

    const newPassword = newPasswordInput.value.trim();
    const confirmPassword = confirmPasswordInput.value.trim();

    // Check password requirements
    const validation = validatePassword(newPassword);
    if (!validation.isValid) {
      showMessage(validation.message, "warning");
      return;
    }

    // Check passwords match
    if (newPassword !== confirmPassword) {
      showMessage("Passwords do not match. Please try again.", "warning");
      return;
    }

    setLoading(true);
    hideMessage();

    try {
      const response = await fetch(
        "http://localhost:3000/api/auth/reset-password",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ token, newPassword }),
        },
      );

      const data = await response.json();

      if (response.ok) {
        showMessage(
          "Password reset successful! Redirecting to login...",
          "success",
        );
        resetForm.style.display = "none";

        setTimeout(() => {
          window.location.href = "index.html"; // or your login page
        }, 2200);
      } else {
        throw new Error(data.error || "Failed to reset password");
      }
    } catch (error) {
      console.error("Reset password failed:", error);
      showMessage(error.message || "Failed to reset password.", "error");
      setLoading(false);
    }
  }

  // ================= PASSWORD VALIDATION =================
  function validatePassword(password) {
    const hasMinLength = password.length >= 8;
    const hasUppercase = /[A-Z]/.test(password);
    const hasThreeNumbers = (password.match(/\d/g) || []).length >= 3;
    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

    const errors = [];

    if (!hasMinLength) errors.push("at least 8 characters");
    if (!hasUppercase) errors.push("at least 1 uppercase letter");
    if (!hasThreeNumbers) errors.push("at least 3 numbers");
    if (!hasSpecialChar) errors.push("at least 1 special character");

    if (errors.length === 0) {
      return { isValid: true, message: "" };
    }

    return {
      isValid: false,
      message: `Password must contain: ${errors.join(", ")}.`,
    };
  }

  // ================= REAL-TIME STRENGTH FEEDBACK =================
  function updatePasswordStrength() {
    const password = newPasswordInput.value.trim();
    const result = validatePassword(password);

    if (password.length === 0) {
      strengthMessage.textContent = "";
      strengthMessage.style.color = "";
      return;
    }

    if (result.isValid) {
      strengthMessage.textContent = "Strong password ✓";
      strengthMessage.style.color = "#4ade80";
    } else {
      strengthMessage.textContent = result.message;
      strengthMessage.style.color = "#f87171";
    }
  }

  // ================= HELPER FUNCTIONS =================
  function setLoading(isLoading) {
    submitBtn.disabled = isLoading;
    btnText.style.display = isLoading ? "none" : "inline";
    btnLoader.style.display = isLoading ? "inline" : "none";
  }

  function showMessage(message, type) {
    messageBox.style.display = "block";

    const styles = {
      success: {
        background: "#d4edda",
        color: "#155724",
        border: "1px solid #c3e6cb",
        icon: "fa-check-circle",
      },
      error: {
        background: "#f8d7da",
        color: "#721c24",
        border: "1px solid #f5c6cb",
        icon: "fa-exclamation-circle",
      },
      warning: {
        background: "#fff3cd",
        color: "#856404",
        border: "1px solid #ffeaa7",
        icon: "fa-exclamation-triangle",
      },
    };

    const style = styles[type] || styles.error;

    messageBox.style.background = style.background;
    messageBox.style.color = style.color;
    messageBox.style.border = style.border;
    messageBox.innerHTML = `<i class="fas ${style.icon}"></i> ${message}`;
  }

  function showError(message) {
    showMessage(message, "error");
  }

  function hideMessage() {
    messageBox.style.display = "none";
  }
});
