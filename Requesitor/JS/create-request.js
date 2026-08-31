// create-request.js

document.addEventListener("DOMContentLoaded", () => {

  // ──────────────────────────────────────────────────────────────
  //  SUBCATEGORY MAP
  // ──────────────────────────────────────────────────────────────
  const subcategoryMap = {
    Maintenance: [
      // Lubrication
      "Engine Oil",
      "Gear Oil",
      "Hydraulic Oil",
      "Grease & Lubricating Compounds",
      "Cutting & Coolant Fluids",
      // Filters
      "Air Filters",
      "Oil Filters",
      "Hydraulic Filters",
      "Fuel Filters",
      "Dust Collector Filters",
      // Power Transmission
      "V-Belts",
      "Timing Belts",
      "Drive Chains",
      "Sprockets & Pulleys",
      "Couplings & Clutches",
      // Bearings & Seals
      "Ball Bearings",
      "Roller Bearings",
      "Sleeve / Bushing Bearings",
      "O-Rings",
      "Mechanical Seals",
      "Gaskets & Packing",
      // Electrical Maintenance
      "Fuses & Circuit Breakers",
      "Contactors & Relays",
      "Indicator Lights & Buzzers",
      "Battery & UPS Supplies",
      // Cleaning
      "Industrial Degreasers",
      "Rust Inhibitors & Penetrants",
      "Wipes & Absorbents",
      "Pressure Wash Supplies",
      // PM Kits & Tools
      "Preventive Maintenance Kits",
      "Calibration Supplies",
      "Inspection & Measuring Tools",
      "Other Maintenance Item",
    ],

    Repair: [
      // Mechanical Parts
      "Shafts & Axles",
      "Gears & Gear Sets",
      "Pistons & Cylinders",
      "Valves & Actuators",
      "Pumps & Pump Parts",
      "Motors & Motor Parts",
      // Fasteners & Hardware
      "Bolts & Hex Screws",
      "Nuts & Washers",
      "Machine Screws & Set Screws",
      "Anchor Bolts & Studs",
      "Rivets & Pins",
      "Retaining Rings & Clips",
      // Joining & Bonding
      "Welding Rods & Wire",
      "Welding Gas & Consumables",
      "Structural Adhesives",
      "Thread Sealants & Loctite",
      "Brazing & Soldering Supplies",
      // Electrical Repair
      "Wire & Cable",
      "Connectors & Terminals",
      "Switches & Sensors",
      "PCB & Electronic Components",
      "Insulation Tape & Heat Shrink",
      // Fluid Systems
      "Hydraulic Hoses & Fittings",
      "Pneumatic Tubes & Fittings",
      "Hydraulic Cylinders",
      "Pneumatic Cylinders",
      "Pressure Gauges & Regulators",
      // Structural Repair
      "Steel Plates & Angle Bars",
      "Pipes & Pipe Fittings",
      "Sheet Metal & Panels",
      "Repair Clamps & Patches",
      "Other Repair Item",
    ],

    Operation: [
      // PPE
      "Hard Hats & Helmets",
      "Safety Glasses & Face Shields",
      "Gloves - General Purpose",
      "Gloves - Cut-Resistant",
      "Gloves - Chemical Resistant",
      "Ear Protection (Plugs & Muffs)",
      "Safety Shoes & Boots",
      "High-Visibility Vests",
      "Respirators & Dust Masks",
      "Fall Protection (Harnesses & Lanyards)",
      "Coveralls & Protective Clothing",
      // Consumables
      "Disposable Gloves (Nitrile / Latex)",
      "Rags & Wiping Cloths",
      "Cable Ties & Zip Ties",
      "Masking & Duct Tape",
      "Markers, Labels & Tags",
      // Packaging & Logistics
      "Cardboard Boxes & Cartons",
      "Stretch Wrap & Shrink Film",
      "Bubble Wrap & Foam Padding",
      "Pallets & Skids",
      "Strapping & Banding",
      // Safety & Compliance
      "Safety Signs & Barricade Tape",
      "First Aid Kits & Supplies",
      "Fire Extinguishers & Accessories",
      "Spill Kits & Containment",
      "Lockout / Tagout (LOTO) Kits",
      // Janitorial & Facility
      "Brooms, Mops & Dustpans",
      "Trash Bags & Bins",
      "Cleaning Chemicals & Disinfectants",
      "Paper Towels & Toilet Tissue",
      "Hand Soap & Sanitizers",
      // Office & Admin
      "Pens, Pencils & Markers",
      "Notebooks & Logbooks",
      "Printer Paper & Toner",
      "Staplers, Clips & Binders",
      "Other Operation Item",
    ],
  };

  // ──────────────────────────────────────────────────────────────
  //  DOM ELEMENTS
  // ──────────────────────────────────────────────────────────────
  const categorySelect = document.getElementById("category");
  const subcategoryWrapper = document.getElementById("subcategoryWrapper");
  const subcategorySelect = document.getElementById("subcategory");

  const itemSearch = document.getElementById("itemSearch");
  const suggestions = document.getElementById("suggestions");
  const itemNameInput = document.getElementById("itemName");
  const manufacturerInput = document.getElementById("manufacturerInput");
  const unitPriceInput = document.getElementById("unitPrice");
  const totalPriceInput = document.getElementById("totalPrice");
  const quantityInput = document.getElementById("quantity");
  const currencySelect = document.getElementById("currency");
  const form = document.getElementById("createRequestForm");

  // Hidden inputs for backend
  const hiddenUnitPrice = document.createElement("input");
  hiddenUnitPrice.type = "hidden";
  hiddenUnitPrice.name = "unit_price";
  form.appendChild(hiddenUnitPrice);

  const hiddenDistributor = document.createElement("input");
  hiddenDistributor.type = "hidden";
  hiddenDistributor.name = "selected_distributor";
  form.appendChild(hiddenDistributor);

  // State
  let currentParts = [];
  let currentBasePrice = 0;
  let isSearchFocused = false;

  // ── Auto-detect currency from browser locale, default PHP ──
  (function initCurrency() {
    const detected = Currency.detectCurrency();
    const select   = currencySelect;
    // Try to set to detected; fall back to PHP if option doesn't exist
    const options  = Array.from(select.options).map(o => o.value);
    select.value   = options.includes(detected) ? detected : 'PHP';
    updatePhpEquiv();
  })();

  // ──────────────────────────────────────────────────────────────
  //  SUBCATEGORY LOGIC
  // ──────────────────────────────────────────────────────────────
  categorySelect.addEventListener("change", () => {
    const selected = categorySelect.value;
    const items = subcategoryMap[selected];

    // Reset
    subcategorySelect.innerHTML = '<option value="">Select subcategory</option>';
    subcategorySelect.required = false;

    if (items && items.length) {
      items.forEach((item) => {
        const opt = document.createElement("option");
        opt.value = item;
        opt.textContent = item;
        subcategorySelect.appendChild(opt);
      });
      subcategoryWrapper.classList.remove("subcategory-hidden");
      subcategoryWrapper.classList.add("subcategory-visible");
      subcategorySelect.required = true;
    } else {
      subcategoryWrapper.classList.remove("subcategory-visible");
      subcategoryWrapper.classList.add("subcategory-hidden");
    }
  });

  // ──────────────────────────────────────────────────────────────
  //  NGROK / CORS HELPER
  // ──────────────────────────────────────────────────────────────
  function ngrokFetch(url, options = {}) {
    const headers = {
      "ngrok-skip-browser-warning": "true",
      Accept: "application/json",
      ...options.headers,
    };
    return fetch(url, { ...options, headers });
  }

  // ──────────────────────────────────────────────────────────────
  //  PRICE HELPERS  (use shared Currency utility)
  // ──────────────────────────────────────────────────────────────
  function convertPrice(priceUSD, targetCurrency) {
    return Currency.fromUSD(priceUSD, targetCurrency);
  }

  function formatPrice(price, currency) {
    return Currency.format4(price, currency);
  }

  function updatePrices() {
    const currency  = currencySelect.value;
    const qty       = Math.max(1, parseInt(quantityInput.value) || 1);
    const unitPrice = convertPrice(currentBasePrice, currency);
    const total     = unitPrice * qty;

    unitPriceInput.value  = formatPrice(unitPrice, currency);
    totalPriceInput.value = formatPrice(total, currency);
    hiddenUnitPrice.value = currentBasePrice.toFixed(6);
    updatePhpEquiv(unitPrice, total, currency, qty);
  }

  function updatePhpEquiv(unitPrice, total, currency, qty) {
    const row = document.getElementById('php-equiv-row');
    const box = document.getElementById('php-equiv-display');
    if (!row || !box) return;

    // Hide if already in PHP
    if (!currency) currency = currencySelect.value;
    if (currency === 'PHP' || !unitPrice) { row.style.display = 'none'; return; }

    const phpUnit  = Currency.toPHP(unitPrice,  currency);
    const phpTotal = Currency.toPHP(total, currency);
    const phpQty   = qty || Math.max(1, parseInt(quantityInput.value) || 1);

    box.innerHTML =
      `<i class="fas fa-circle-info" style="color:#22c55e;margin-right:6px"></i>` +
      `<strong style="color:var(--text-light,#e0e0ff)">PHP Equivalent</strong><br>` +
      `Unit Price: <strong style="color:#4ade80">₱${phpUnit.toLocaleString('en-PH',{minimumFractionDigits:4,maximumFractionDigits:4})}</strong><br>` +
      `Total (${phpQty.toLocaleString()} units): <strong style="color:#4ade80">₱${phpTotal.toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2})}</strong>`;
    row.style.display = '';
  }

  // ──────────────────────────────────────────────────────────────
  //  DISTRIBUTOR SELECT FIELD
  // ──────────────────────────────────────────────────────────────
  function initializeDistributorField() {
    const wrapper = document.createElement("div");
    wrapper.className = "form-group";
    wrapper.id = "distributorSelectWrapper";
    wrapper.innerHTML = `
      <label for="distributorSelect">Select Distributor</label>
      <select id="distributorSelect" disabled>
        <option value="">Select a part first to see distributors</option>
      </select>
    `;
    document.getElementById("distributorWrapper").appendChild(wrapper);
  }

  function populateDistributors(distributors) {
    console.log("Distributors received:", distributors);

    const select = document.getElementById("distributorSelect");
    if (!select) return;

    select.disabled = distributors.length === 0;
    select.innerHTML = "";

    if (distributors.length === 0) {
      select.innerHTML = '<option value="">No distributors available</option>';
      currentBasePrice = 0;
      hiddenDistributor.value = "";
      hiddenUnitPrice.value = "0";
      updatePrices();
      return;
    }

    distributors.forEach((d, index) => {
      const price = parseFloat(d.price || d.unit_price || 0);
      const opt = document.createElement("option");
      opt.value = index;
      opt.textContent = `${d.name || "Unknown"} — $${price.toFixed(4)} @ ${d.quantity || "?"} units`;
      opt.dataset.price = price;
      opt.dataset.name = d.name || "N/A";
      select.appendChild(opt);
    });

    const firstOption = select.options[0];
    currentBasePrice = parseFloat(firstOption.dataset.price || 0);
    hiddenDistributor.value = firstOption.dataset.name || "N/A";
    hiddenUnitPrice.value = currentBasePrice.toFixed(6);
    updatePrices();

    select.addEventListener("change", () => {
      const selectedOption = select.options[select.selectedIndex];
      currentBasePrice = parseFloat(selectedOption.dataset.price || 0);
      hiddenDistributor.value = selectedOption.dataset.name || "N/A";
      hiddenUnitPrice.value = currentBasePrice.toFixed(6);
      updatePrices();
    });
  }

  // ──────────────────────────────────────────────────────────────
  //  SEARCH LOGIC
  // ──────────────────────────────────────────────────────────────
  async function searchParts(query, quantity = 1) {
    if (query.length < 3) return [];
    try {
      const url = `../PHP/search.php?q=${encodeURIComponent(query)}&quantity=${quantity}`;
      console.log("Searching:", url);
      const res = await ngrokFetch(url);
      if (!res.ok) throw new Error(`Search failed (${res.status})`);
      const data = await res.json();
      // The proxy may forward a structured error object from the Node service
      // (e.g. when DigiKey auth fails). Surface it as an empty result so the UI
      // stays usable instead of rendering "API Error: ..." as a part row.
      if (data && data.error) {
        console.warn("Search service reported error:", data);
        return { __error: true, message: data.details || data.error };
      }
      currentParts = data;
      return data;
    } catch (err) {
      console.error("Search error:", err);
      return { __error: true, message: err.message };
    }
  }

  async function handleSearch() {
    if (!isSearchFocused) return;

    const query = itemSearch.value.trim();
    suggestions.innerHTML = "";
    suggestions.style.display = "none";

    if (query.length < 3) return;

    suggestions.innerHTML = '<div class="suggestion-item">Searching...</div>';
    suggestions.style.display = "block";

    const results = await searchParts(query, parseInt(quantityInput.value) || 1);
    suggestions.innerHTML = "";

    if (results && results.__error) {
      suggestions.innerHTML = `
        <div class="suggestion-item" style="cursor:default;color:#fbbf24;">
          <i class="fas fa-exclamation-triangle"></i>
          Part search is currently unavailable — please enter the MPN, manufacturer, and price manually below.
        </div>`;
    } else if (!results || results.length === 0) {
      suggestions.innerHTML = '<div class="suggestion-item">No parts found</div>';
    } else {
      results.forEach((part) => {
        const item = document.createElement("div");
        item.className = "suggestion-item";
        item.innerHTML = `
          <strong>${part.ManufacturerProductNumber || part.mpn || "N/A"}</strong>
          — ${part.manufacturer || "Unknown"}<br>
          <small>${part.shortDescription || part.description || ""}</small>
        `;
        item.addEventListener("click", () => {
          const mpn = part.ManufacturerProductNumber || part.mpn || "";
          const manufacturer = part.manufacturer || "Unknown";
          itemSearch.value = `${mpn} (${manufacturer})`;
          itemNameInput.value = mpn;
          manufacturerInput.value = manufacturer;
          populateDistributors(part.distributors || []);
          suggestions.style.display = "none";
          isSearchFocused = false;
          // ── Smart Price Advisor trigger ──
          if (mpn) setTimeout(() => fetchPriceAdvisor(mpn), 80);
        });
        suggestions.appendChild(item);
      });
    }

    if (isSearchFocused && suggestions.children.length > 0) {
      suggestions.style.display = "block";
    }
  }

  // ──────────────────────────────────────────────────────────────
  //  EVENT LISTENERS
  // ──────────────────────────────────────────────────────────────
  itemSearch.addEventListener("focus", () => {
    isSearchFocused = true;
    handleSearch();
  });

  itemSearch.addEventListener("blur", () => {
    setTimeout(() => {
      isSearchFocused = false;
      suggestions.style.display = "none";
    }, 180);
  });

  itemSearch.addEventListener("input", handleSearch);

  quantityInput.addEventListener("input", () => {
    if (isSearchFocused) handleSearch();
    updatePrices();
  });

  currencySelect.addEventListener("change", () => {
    updatePrices();
    // advisor re-render is handled in the Smart Price Advisor section below
  });

  document.addEventListener("click", (e) => {
    if (!itemSearch.parentElement.contains(e.target)) {
      isSearchFocused = false;
      suggestions.style.display = "none";
    }
  });

  // ──────────────────────────────────────────────────────────────
  //  FORM SUBMISSION
  // ──────────────────────────────────────────────────────────────
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    if (!itemNameInput.value.trim()) {
      Swal.fire({ icon: "warning", title: "Missing part", text: "Please select a valid part from the suggestions." });
      return;
    }

    const qty = parseInt(quantityInput.value) || 1;
    if (qty < 1) {
      Swal.fire({ icon: "warning", title: "Invalid quantity", text: "Quantity must be at least 1." });
      return;
    }

    if (currentBasePrice <= 0) {
      Swal.fire({ icon: "warning", title: "Missing price", text: "Please select a distributor with a valid price." });
      return;
    }

    if (subcategoryWrapper.classList.contains("subcategory-visible") && !subcategorySelect.value) {
      Swal.fire({ icon: "warning", title: "Missing subcategory", text: "Please select a subcategory." });
      return;
    }

    const formData = new FormData(form);
    formData.append("itemName", itemNameInput.value.trim());
    formData.append("manufacturer", manufacturerInput.value.trim());
    formData.append("quantity", qty.toString());
    formData.append("currency", currencySelect.value);
    formData.append("unit_price", hiddenUnitPrice.value);
    formData.append("selected_distributor", hiddenDistributor.value || "N/A");

    console.log("Submitting:", Object.fromEntries(formData));

    try {
      Swal.fire({ title: "Submitting request...", allowOutsideClick: false, didOpen: () => Swal.showLoading() });

      const response = await ngrokFetch(form.action, { method: "POST", body: formData });
      const result = await response.json();

      Swal.close();

      if (result.success) {
        await Swal.fire({
          icon: "success",
          title: "Request Submitted",
          html: `
            <div style="font-size:1.05em; text-align:left;">
              Purchase request created successfully.<br><br>
              <strong>PR Number:</strong><br>
              <span style="font-size:1.6em; color:#16a34a; font-weight:bold;">
                ${result.pr_number || "—"}
              </span>
            </div>
          `,
          confirmButtonText: "Done",
          confirmButtonColor: "#16a34a",
        });

        form.reset();
        quantityInput.value = "1";
        currentBasePrice = 0;
        hiddenUnitPrice.value = "0";
        hiddenDistributor.value = "";
        subcategoryWrapper.classList.remove("subcategory-visible");
        subcategoryWrapper.classList.add("subcategory-hidden");
        subcategorySelect.innerHTML = '<option value="">Select subcategory</option>';
        populateDistributors([]);
        updatePrices();
      } else {
        Swal.fire({ icon: "error", title: "Submission failed", text: result.message || "An error occurred." });
      }
    } catch (err) {
      Swal.close();
      console.error("Submit error:", err);
      Swal.fire({ icon: "error", title: "Connection error", text: err.message || "Could not reach server." });
    }
  });

  // ──────────────────────────────────────────────────────────────
  //  SMART PRICE ADVISOR
  // ──────────────────────────────────────────────────────────────
  const priceAdvisor = document.getElementById('priceAdvisor');
  const advisorBody  = document.getElementById('advisorBody');

  // Use shared Currency utility — no local symbol/rate maps needed
  function fmt(priceUSD, currency) {
    return Currency.format4(Currency.fromUSD(priceUSD, currency), currency);
  }

  async function fetchPriceAdvisor(mpn) {
    if (!mpn) return;
    priceAdvisor.classList.add('visible');
    advisorBody.innerHTML = '<div class="advisor-loading"><i class="fas fa-spinner fa-spin"></i> Looking up prices…</div>';

    try {
      const res  = await fetch(`../PHP/get_supplier_price.php?mpn=${encodeURIComponent(mpn)}`);
      const data = await res.json();

      if (!data.success) {
        advisorBody.innerHTML = `<div class="no-data-msg"><i class="fas fa-circle-info"></i> ${data.message || 'No price data available.'}</div>`;
        return;
      }

      renderAdvisor(data);
    } catch (err) {
      advisorBody.innerHTML = '<div class="no-data-msg"><i class="fas fa-triangle-exclamation"></i> Could not load price data.</div>';
    }
  }

  function renderAdvisor(data) {
    const currency = currencySelect.value;
    const rec      = data.recommended_price;
    const hist     = data.historical;
    const trend    = data.trend;
    const bids     = data.supplier_bids || [];

    // Trend badge
    const trendIcons = { rising: 'fa-arrow-trend-up', falling: 'fa-arrow-trend-down', stable: 'fa-minus' };
    const trendLabels = { rising: 'Rising', falling: 'Falling', stable: 'Stable' };
    const trendBadge = `<span class="trend-badge ${trend.direction}">
      <i class="fas ${trendIcons[trend.direction]}"></i> ${trendLabels[trend.direction]}
      ${trend.percent !== 0 ? `(${trend.percent > 0 ? '+' : ''}${trend.percent}%)` : ''}
    </span>`;

    // Savings vs current Digi-Key price
    let savingsHtml = '';
    if (rec && currentBasePrice > 0 && rec < currentBasePrice) {
      const savePct = (((currentBasePrice - rec) / currentBasePrice) * 100).toFixed(1);
      savingsHtml = `<div class="rec-savings">
        <i class="fas fa-circle-check"></i> ${savePct}% cheaper than current Digi-Key price
      </div>`;
    } else if (rec && currentBasePrice > 0 && rec > currentBasePrice) {
      const diffPct = (((rec - currentBasePrice) / currentBasePrice) * 100).toFixed(1);
      savingsHtml = `<div class="rec-savings" style="color:#f87171;">
        <i class="fas fa-circle-exclamation"></i> ${diffPct}% above current Digi-Key price — Digi-Key is cheaper
      </div>`;
    }

    // Recommended banner
    const recBanner = rec
      ? `<div class="advisor-recommend">
          <div>
            <div class="rec-label"><i class="fas fa-star"></i> Recommended Price</div>
            <div class="rec-price">${fmt(rec, currency)}</div>
            <div class="rec-source">Source: ${data.recommended_source}</div>
            ${savingsHtml}
          </div>
          <button class="use-price-btn" onclick="applyAdvisedPrice(${rec})">
            <i class="fas fa-check"></i> Use This Price
          </button>
        </div>`
      : '';

    // Summary cards
    const histAvgHtml = hist.avg_price
      ? `<div class="adv-card">
          <div class="adv-card-label">Historical Avg ${trendBadge}</div>
          <div class="adv-card-val">${fmt(hist.avg_price, currency)}</div>
          <div class="adv-card-sub">${hist.order_count} order${hist.order_count !== 1 ? 's' : ''} on record</div>
        </div>`
      : '';

    const histMinHtml = hist.min_price
      ? `<div class="adv-card">
          <div class="adv-card-label">Best Price Ever</div>
          <div class="adv-card-val" style="color:#4ade80">${fmt(hist.min_price, currency)}</div>
          <div class="adv-card-sub">Lowest recorded</div>
        </div>`
      : '';

    const histMaxHtml = hist.max_price
      ? `<div class="adv-card">
          <div class="adv-card-label">Highest Price</div>
          <div class="adv-card-val" style="color:#f87171">${fmt(hist.max_price, currency)}</div>
          <div class="adv-card-sub">Highest recorded</div>
        </div>`
      : '';

    const currentDigikeyHtml = currentBasePrice > 0
      ? `<div class="adv-card">
          <div class="adv-card-label">Current Digi-Key Price</div>
          <div class="adv-card-val" style="color:#fbbf24">${fmt(currentBasePrice, currency)}</div>
          <div class="adv-card-sub">Selected distributor</div>
        </div>`
      : '';

    const cardsRow = (histAvgHtml || histMinHtml || histMaxHtml || currentDigikeyHtml)
      ? `<div class="advisor-grid">${histAvgHtml}${histMinHtml}${histMaxHtml}${currentDigikeyHtml}</div>`
      : '';

    // Supplier bids table
    let bidsHtml = '';
    if (bids.length > 0) {
      const rows = bids.map((b, i) => `
        <tr class="${i === 0 ? 'best-bid' : ''}">
          <td><span class="bid-rank ${i === 0 ? 'first' : ''}">${i + 1}</span></td>
          <td>${b.supplier_name}</td>
          <td style="color:${i === 0 ? '#4ade80' : '#e0e0ff'};font-weight:${i === 0 ? '700' : '400'}">
            ${fmt(parseFloat(b.unit_price), currency)}
          </td>
          <td>${b.delivery_date || '—'}</td>
          <td>
            <button class="use-price-btn" style="padding:6px 12px;font-size:.8rem;"
              onclick="applyAdvisedPrice(${parseFloat(b.unit_price)})">
              Use
            </button>
          </td>
        </tr>`).join('');

      bidsHtml = `
        <div class="advisor-bids-title"><i class="fas fa-building"></i> Internal Supplier Bids (sorted cheapest first)</div>
        <table class="advisor-bids-table">
          <thead><tr><th>#</th><th>Supplier</th><th>Unit Price</th><th>Delivery</th><th></th></tr></thead>
          <tbody>${rows}</tbody>
        </table>`;
    } else {
      bidsHtml = `<div class="no-data-msg" style="margin-top:8px;">
        <i class="fas fa-circle-info"></i> No internal supplier bids on record for this part yet.
        ${hist.avg_price ? 'Using historical order data for recommendation.' : ''}
      </div>`;
    }

    advisorBody.innerHTML = recBanner + cardsRow + bidsHtml;
  }

  // Called by "Use This Price" buttons — updates the form price
  window.applyAdvisedPrice = function(priceUSD) {
    currentBasePrice = priceUSD;
    hiddenUnitPrice.value = priceUSD.toFixed(6);
    updatePrices();

    // Visual feedback on the unit price field
    unitPriceInput.style.borderColor = '#22c55e';
    unitPriceInput.style.boxShadow   = '0 0 0 3px rgba(34,197,94,.3)';
    setTimeout(() => {
      unitPriceInput.style.borderColor = '';
      unitPriceInput.style.boxShadow   = '';
    }, 1800);
  };

  // Re-render advisor when currency changes (prices need to be reconverted)
  currencySelect.addEventListener('change', () => {
    const currentMPN = itemNameInput.value.trim();
    if (currentMPN && priceAdvisor.classList.contains('visible')) {
      fetchPriceAdvisor(currentMPN);
    }
    updatePrices();
  });

  // ──────────────────────────────────────────────────────────────
  //  INITIAL SETUP
  // ──────────────────────────────────────────────────────────────
  initializeDistributorField();
  quantityInput.value = "1";
  updatePrices();
});