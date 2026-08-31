document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("forgotPasswordForm");
  const emailInput = document.getElementById("email");
  const submitBtn = document.getElementById("submitBtn");
  const btnText = document.getElementById("btnText");
  const btnLoader = document.getElementById("btnLoader");
  const messageBox = document.getElementById("messageBox");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const email = emailInput.value.trim();

    // Validate email
    if (!email) {
      showMessage("Please enter your email address", "error");
      return;
    }

    // Show loading state
    setLoading(true);
    hideMessage();

    try {
      const response = await fetch(
        "http://localhost:3000/api/auth/forgot-password",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ email }),
        }
      );

      const data = await response.json();

      if (response.ok) {
        // Success
        showMessage(
          `${data.message}. Please check your email inbox and spam folder.`,
          "success"
        );

        // Clear form
        emailInput.value = "";
      } else {
        // Error from server
        showMessage(data.error || "Failed to send reset email", "error");
      }
    } catch (error) {
      console.error("Request failed:", error);
      showMessage(
        "Unable to connect to the server. Please check your connection and try again.",
        "error"
      );
    } finally {
      setLoading(false);
    }
  });

  // Helper function to show loading state
  function setLoading(isLoading) {
    submitBtn.disabled = isLoading;
    btnText.style.display = isLoading ? "none" : "inline";
    btnLoader.style.display = isLoading ? "inline" : "none";
  }

  // Helper function to show messages
  function showMessage(message, type) {
    messageBox.style.display = "block";

    if (type === "success") {
      messageBox.style.background = "#d4edda";
      messageBox.style.color = "#155724";
      messageBox.style.border = "1px solid #c3e6cb";
      messageBox.innerHTML = `
        <i class="fas fa-check-circle"></i> ${message}
      `;
    } else if (type === "error") {
      messageBox.style.background = "#f8d7da";
      messageBox.style.color = "#721c24";
      messageBox.style.border = "1px solid #f5c6cb";
      messageBox.innerHTML = `
        <i class="fas fa-exclamation-circle"></i> ${message}
      `;
    } else if (type === "warning") {
      messageBox.style.background = "#fff3cd";
      messageBox.style.color = "#856404";
      messageBox.style.border = "1px solid #ffeaa7";
      messageBox.innerHTML = `
        <i class="fas fa-exclamation-triangle"></i> ${message}
      `;
    }
  }

  // Helper function to hide messages
  function hideMessage() {
    messageBox.style.display = "none";
  }
});
