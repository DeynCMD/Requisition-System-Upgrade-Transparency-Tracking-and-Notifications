import express from "express";
import dotenv from "dotenv";
import cors from "cors";
import jwt from "jsonwebtoken";
import bcrypt from "bcryptjs";
import mysql from "mysql2/promise";
import { searchParts } from "./digikeyService.js";
import { sendPasswordResetEmail, testEmailConnection } from "./emailService.js";

// Load .env from the project root (two levels up from this file) so creds are
// available regardless of the directory the Node process was started from.
// Falls back to the default cwd-relative location if the root file is missing.
import path from "path";
import { fileURLToPath } from "url";
const __dirname = path.dirname(fileURLToPath(import.meta.url));
const rootEnv = path.resolve(__dirname, "..", "..", ".env");
dotenv.config({ path: rootEnv });
dotenv.config();

// ── Startup credential check ──────────────────────
if (!process.env.DIGIKEY_CLIENT_ID || process.env.DIGIKEY_CLIENT_ID === 'your_digikey_client_id_here') {
  console.warn('⚠️  DIGIKEY_CLIENT_ID is not set in .env — part search will not work');
}
if (!process.env.DIGIKEY_CLIENT_SECRET || process.env.DIGIKEY_CLIENT_SECRET === 'your_digikey_client_secret_here') {
  console.warn('⚠️  DIGIKEY_CLIENT_SECRET is not set in .env — part search will not work');
}

const app = express();
app.use(express.json());

// Allow CORS from anywhere (good for ngrok testing)
app.use(cors({ origin: "*" }));

// Test email on startup
testEmailConnection();

// ================= DATABASE CONNECTION =================
const dbConfig = {
  host: "localhost",
  user: "root",
  password: "",
  database: "ze_electronic",
};

const pool = mysql.createPool(dbConfig);

pool
  .getConnection()
  .then((conn) => {
    console.log("✅ Database connected");
    conn.release();
  })
  .catch((err) => {
    console.error("❌ Database connection failed:", err.message);
  });

// Temporary in-memory storage for reset tokens
const resetTokens = new Map();

// ================= DIGI-KEY SEARCH ROUTE (THIS WAS MISSING!) =================
app.get("/api/digikey/search", async (req, res) => {
  try {
    const { q, quantity = 1 } = req.query;

    if (!q) {
      return res
        .status(400)
        .json({ error: "Missing search query parameter 'q'" });
    }

    console.log(`🔍 DigiKey search requested: "${q}", quantity: ${quantity}`);

    // Call your actual DigiKey service
    const parts = await searchParts(q, parseInt(quantity));

    // searchParts returns an error object (not an array) on failure —
    // surface it with the upstream status so the proxy/front-end can react.
    if (parts && parts.error) {
      return res.status(parts.status && parts.status >= 400 ? parts.status : 502).json({
        error: "DigiKey search failed",
        upstream_status: parts.status,
        details: parts.message,
      });
    }

    res.json(parts || []);
  } catch (err) {
    console.error("DigiKey search error:", err.message);
    res.status(500).json({
      error: "Failed to search DigiKey",
      details: err.message,
    });
  }
});

// ================= PASSWORD RESET ROUTES =================

// 1. Request password reset
app.post("/api/auth/forgot-password", async (req, res) => {
  try {
    const { email } = req.body;

    if (!email) {
      return res.status(400).json({ error: "Email is required" });
    }

    console.log(`📧 Password reset requested for: ${email}`);

    const [rows] = await pool.query(
      "SELECT id, email, firstname, lastname FROM users WHERE email = ? LIMIT 1",
      [email],
    );

    // Security: Don't reveal if email exists
    if (rows.length === 0) {
      console.log(`⚠️ User not found: ${email}`);
      return res.json({
        message:
          "If that email exists in our system, a reset link has been sent",
      });
    }

    const user = rows[0];

    const resetToken = jwt.sign(
      { email: user.email, userId: user.id },
      process.env.JWT_SECRET,
      { expiresIn: "1h" },
    );

    resetTokens.set(email, {
      token: resetToken,
      expires: Date.now() + 3600000, // 1 hour
    });

    console.log(`🔑 Reset token generated for: ${email}`);

    await sendPasswordResetEmail(email, resetToken);

    console.log(`✅ Reset email sent to: ${email}`);

    res.json({
      message: "If that email exists in our system, a reset link has been sent",
    });
  } catch (err) {
    console.error("❌ Forgot password error:", err);
    res.status(500).json({ error: "Failed to send reset email" });
  }
});

// 2. Verify reset token
app.post("/api/auth/verify-token", async (req, res) => {
  try {
    const { token } = req.body;

    if (!token) {
      return res.status(400).json({ error: "Token is required" });
    }

    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    const email = decoded.email;

    const storedToken = resetTokens.get(email);
    if (!storedToken || storedToken.token !== token) {
      return res.status(400).json({ error: "Invalid or expired reset link" });
    }

    if (Date.now() > storedToken.expires) {
      resetTokens.delete(email);
      return res.status(400).json({ error: "Reset link has expired" });
    }

    res.json({ valid: true, email });
  } catch (err) {
    console.error("❌ Token verification error:", err.message);
    if (err.name === "TokenExpiredError") {
      return res.status(400).json({ error: "Reset link has expired" });
    }
    res.status(400).json({ error: "Invalid reset link" });
  }
});

// 3. Reset password
app.post("/api/auth/reset-password", async (req, res) => {
  try {
    const { token, newPassword } = req.body;

    if (!token || !newPassword) {
      return res
        .status(400)
        .json({ error: "Token and new password are required" });
    }

    if (newPassword.length < 8) {
      return res
        .status(400)
        .json({ error: "Password must be at least 8 characters long" });
    }

    const decoded = jwt.verify(token, process.env.JWT_SECRET);
    const email = decoded.email;

    const storedToken = resetTokens.get(email);
    if (!storedToken || storedToken.token !== token) {
      return res.status(400).json({ error: "Invalid or expired reset link" });
    }

    if (Date.now() > storedToken.expires) {
      resetTokens.delete(email);
      return res.status(400).json({ error: "Reset link has expired" });
    }

    const hashedPassword = await bcrypt.hash(newPassword, 10);

    await pool.query("UPDATE users SET password = ? WHERE email = ?", [
      hashedPassword,
      email,
    ]);

    resetTokens.delete(email);

    console.log(`✅ Password reset successful for: ${email}`);

    res.json({ message: "Password reset successful" });
  } catch (err) {
    console.error("❌ Reset password error:", err.message);
    if (err.name === "TokenExpiredError") {
      return res.status(400).json({ error: "Reset link has expired" });
    }
    res.status(500).json({ error: "Failed to reset password" });
  }
});

// ================= HEALTH CHECK ROUTE =================
app.get("/api/health", (req, res) => {
  res.json({ status: "ok", timestamp: new Date().toISOString() });
});

// ================= START SERVER =================
const port = 3000;
app.listen(port, () => {
  console.log(`✅ Server running on http://localhost:${port}`);
  console.log("📧 Email service initialized");
  console.log("🔐 Password reset routes ready");
  console.log("💾 Database: ze_electronic");
});
