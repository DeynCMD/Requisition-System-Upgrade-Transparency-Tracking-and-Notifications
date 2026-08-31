<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create Request — Procurement System</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../CSS/create-request.css" />
  </head>
  <body>
    <div class="container">
      <!-- SIDEBAR -->
      <aside class="sidebar">
        <div class="profile">
          <img src="../Assets/Avatar.jpg" alt="Requestor profile picture" />
          <span class="role">REQUESTOR</span>
        </div>
        <nav class="nav-menu">
          <ul>
            <li>
              <a href="requestor-dashboard.php"
                ><i class="fas fa-tachometer-alt"></i> Dashboard</a
              >
            </li>
            <li>
              <a href="create-request.html" class="active"
                ><i class="fas fa-plus-circle"></i> Create Request</a
              >
            </li>
            <li>
              <a href="history.html"><i class="fas fa-clock"></i> History</a>
            </li>
            <li>
              <a href="my-requests.php"
                ><i class="fas fa-list-check"></i> My Requests</a
              >
            </li>
          </ul>
        </nav>
        <a href="../../Admin/PHP/logout.php" class="logout-btn">LOGOUT</a>
      </aside>

      <!-- MAIN CONTENT -->
      <main class="main-content">
        <div class="page-header">
          <h1>Create New Request</h1>
        </div>

        <div class="form-card">
          <form
            id="createRequestForm"
            method="POST"
            action="submit_request.php"
          >
            <div class="form-grid">
              <!-- PART SEARCH -->
              <div
                class="form-group full-width"
                id="itemSearchWrapper"
                style="position: relative"
              >
                <label for="itemSearch">Search Part (MPN or Description) <span class="required">*</span></label>
                <input
                  type="text"
                  id="itemSearch"
                  placeholder="e.g., STM32F103, BAV99 — or type item name directly"
                  autocomplete="off"
                />
                <div id="suggestions" class="suggestions-dropdown"></div>
                <small style="color:#9a9ab5;font-size:.78rem;margin-top:4px;display:block">
                  Select from suggestions, or type the item name directly if search is unavailable.
                </small>
              </div>

              <!-- HIDDEN FIELDS -->
              <input type="hidden" id="itemName" name="itemName" />
              <input type="hidden" id="manufacturerInput" name="manufacturer" />

              <!-- Distributor injected by JS -->
              <div id="distributorWrapper"></div>

              <!-- SMART PRICE ADVISOR (injected after part selection) -->
              <div id="priceAdvisor" class="form-group full-width">
                <div class="advisor-header">
                  <div class="advisor-icon"><i class="fas fa-lightbulb"></i></div>
                  <div>
                    <h3>Smart Price Advisor</h3>
                    <div class="advisor-sub">Most practical price based on supplier bids &amp; order history</div>
                  </div>
                </div>
                <div id="advisorBody">
                  <div class="advisor-loading"><i class="fas fa-spinner fa-spin"></i> Looking up prices…</div>
                </div>
              </div>

              <!-- CATEGORY -->
              <div class="form-group">
                <label for="category"
                  >Category <span class="required">*</span></label
                >
                <select id="category" name="category" required>
                  <option value="">Select category</option>
                  <option value="Maintenance">Maintenance</option>
                  <option value="Repair">Repair</option>
                  <option value="Operation">Operation</option>
                </select>
              </div>

              <!-- SUBCATEGORY – revealed after category is picked - REMOVED as per user request -->

              <!-- QUANTITY -->
              <div class="form-group">
                <label for="quantity"
                  >Quantity <span class="required">*</span></label
                >
                <input
                  type="number"
                  id="quantity"
                  name="quantity"
                  min="1"
                  step="1"
                  value="1"
                  required
                  placeholder="e.g., 100"
                />
              </div>

              <!-- UNIT PRICE -->
              <div class="form-group">
                <label for="unitPrice">Unit Price <span style="font-size:.8rem;color:#9a9ab5">(auto-filled or enter manually)</span></label>
                <input
                  type="text"
                  id="unitPrice"
                  name="unitPrice"
                  placeholder="e.g. $0.0420"
                  oninput="onManualPriceInput(this.value)"
                />
              </div>

              <!-- TOTAL PRICE -->
              <div class="form-group">
                <label for="totalPrice">Total / Estimated Price</label>
                <input
                  type="text"
                  id="totalPrice"
                  readonly
                  placeholder="Calculated automatically"
                />
              </div>

              <!-- CURRENCY -->
              <div class="form-group">
                <label for="currency">Currency</label>
                <select id="currency" name="currency">
                  <option value="USD">USD ($)</option>
                  <option value="PHP">PHP (₱)</option>
                  <option value="EUR">EUR (€)</option>
                </select>
              </div>

              <!-- REASON -->
              <div class="form-group full-width">
                <label for="reason"
                  >Reason / Justification <span class="required">*</span></label
                >
                <textarea
                  id="reason"
                  name="reason"
                  rows="4"
                  required
                  placeholder="Explain why this item is needed..."
                ></textarea>
              </div>

              <!-- NOTES -->
              <div class="form-group full-width">
                <label for="notes">Additional Notes (Optional)</label>
                <textarea
                  id="notes"
                  name="notes"
                  rows="3"
                  placeholder="Any specifications, preferred supplier, urgency, etc."
                ></textarea>
              </div>

              <!-- URGENCY -->
              <div class="form-group">
                <label for="urgency">Urgency Level</label>
                <select id="urgency" name="urgency">
                  <option value="Normal">Normal</option>
                  <option value="High">High</option>
                  <option value="Critical">Critical</option>
                </select>
              </div>

              <!-- REQUIRED DATE -->
              <div class="form-group">
                <label for="requiredDate">Required By Date</label>
                <input type="date" id="requiredDate" name="requiredDate" />
              </div>
            </div>
            <!-- /.form-grid -->

            <div class="form-actions">
              <button type="button" class="cancel-btn">Cancel</button>
              <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane"></i> Submit Request
              </button>
            </div>
          </form>
        </div>
      </main>
    </div>

    <script src="../JS/create-request.js"></script>
  </body>
</html>
