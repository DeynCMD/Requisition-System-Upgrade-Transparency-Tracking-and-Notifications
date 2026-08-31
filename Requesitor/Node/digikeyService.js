import axios from "axios";

let token = null;
let tokenExpiry = 0;

async function getToken() {
  if (token && Date.now() < tokenExpiry) return token;

  try {
    const res = await axios.post(
      "https://api.digikey.com/v1/oauth2/token",
      new URLSearchParams({
        grant_type: "client_credentials",
        client_id: process.env.DIGIKEY_CLIENT_ID,
        client_secret: process.env.DIGIKEY_CLIENT_SECRET,
      }),
      {
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
      },
    );

    token = res.data.access_token;
    tokenExpiry = Date.now() + res.data.expires_in * 1000;
    console.log("Digi-Key token refreshed");
    return token;
  } catch (err) {
    console.error("Digi-Key token error:", err.message);
    throw err;
  }
}

export async function searchParts(keyword, quantity = 1) {
  try {
    let accessToken = await getToken();
    let res;

    const requestBody = {
      Keywords: keyword,
      Limit: 10,
      Offset: 0,
      RecordCount: 10,
      RecordStartPosition: 0,
      Filters: {},
      Sort: {
        SortOption: "SortByUnitPrice",
        Direction: "Ascending",
        SortParameterId: 0,
      },
      RequestedQuantity: quantity,
    };

    const sendSearch = () =>
      axios.post(
        "https://api.digikey.com/products/v4/search/keyword",
        requestBody,
        {
          headers: {
            Authorization: `Bearer ${accessToken}`,
            "X-DIGIKEY-Client-Id": process.env.DIGIKEY_CLIENT_ID,
            "X-DIGIKEY-Locale-Site": "US",
            "X-DIGIKEY-Locale-Language": "en",
            "X-DIGIKEY-Locale-Currency": "USD",
            "Content-Type": "application/json",
            Accept: "application/json",
          },
        },
      );

    res = await sendSearch();

    // If the cached token was rejected, force a refresh once and retry —
    // Digi-Key tokens expire after 10 minutes; a long-running Node process
    // can easily outlive that window between requests.
    if (res.status === 401 || res.status === 403) {
      console.warn("Digi-Key rejected the cached token — refreshing and retrying");
      token = null;
      tokenExpiry = 0;
      accessToken = await getToken();
      res = await sendSearch();
    }

    if (!res.data?.Products?.length) {
      console.log("No products found for:", keyword);
      return [
        {
          mpn: "N/A",
          manufacturer: "N/A",
          shortDescription: "No results found",
          distributors: [
            {
              name: "Digi-Key",
              price: 0,
              quantity: 1,
              currency: "USD",
            },
          ],
        },
      ];
    }

    return res.data.Products.map((p) => {
      const mpn = p.ManufacturerProductNumber || "N/A";
      const manufacturer = p.Manufacturer?.Name || "N/A";
      const description =
        p.Description?.ProductDescription ||
        p.Description?.DetailedDescription ||
        p.Description?.ShortDescription ||
        "No description";

      let price = 0;
      let priceQty = quantity;

      // ── FIX: robust price extraction ──────────────────────────
      if (p.ProductVariations?.length > 0) {
        for (const variation of p.ProductVariations) {
          const pricing = variation.StandardPricing;

          if (!pricing?.length) continue;

          console.log(`StandardPricing for ${mpn}:`, JSON.stringify(pricing));

          // Sort a COPY ascending by break quantity (smallest first)
          const sorted = [...pricing].sort(
            (a, b) => (a.BreakQuantity ?? 0) - (b.BreakQuantity ?? 0),
          );

          // Find the highest break tier that is still <= requested quantity
          // This gives the best (lowest) price the buyer qualifies for
          let bestTier = sorted[0]; // fallback: cheapest single-unit tier
          for (const tier of sorted) {
            if ((tier.BreakQuantity ?? 0) <= quantity) {
              bestTier = tier;
            }
          }

          // FIX: DigiKey v4 uses "UnitPrice" — handle all known key variants
          const rawPrice =
            bestTier?.UnitPrice ??
            bestTier?.unitPrice ??
            bestTier?.unit_price ??
            bestTier?.Price ??
            bestTier?.price ??
            null;

          if (rawPrice !== null && rawPrice !== undefined) {
            price = parseFloat(rawPrice);
            priceQty = bestTier?.BreakQuantity ?? bestTier?.breakQuantity ?? 1;
            console.log(
              `Best tier for ${mpn} @ qty ${quantity}: price=${price}, breakQty=${priceQty}`,
            );
            break; // use first variation that has valid pricing
          }
        }
      }

      // FIX: fallback to UnitPrice at product level if variations had no price
      if (price === 0 && p.UnitPrice != null) {
        price = parseFloat(p.UnitPrice);
        console.log(`Fallback to product-level UnitPrice for ${mpn}: ${price}`);
      }

      console.log(`Part ${mpn}: final price = ${price}`);

      return {
        mpn,
        manufacturer,
        shortDescription: description,
        distributors: [
          {
            name: "Digi-Key",
            price, // raw number e.g. 0.0884
            quantity: priceQty,
            currency: "USD",
          },
        ],
      };
    });
  } catch (err) {
    console.error("Digi-Key API Error:", {
      status: err.response?.status,
      message: err.message,
      response: err.response?.data,
    });

    // Return a structured error object (NOT a fake result row). The front-end
    // distinguishes this by checking `error` rather than rendering a part with
    // "API Error" text in its description, which previously made the page look
    // broken. The HTTP status of the response is also raised so the proxy can
    // surface it to the client.
    return {
      error: true,
      status: err.response?.status ?? null,
      message: err.message,
    };
  }
}
