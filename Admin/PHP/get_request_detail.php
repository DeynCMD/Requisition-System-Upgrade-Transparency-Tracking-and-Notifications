<?php
require_once __DIR__ . '/currency_config.php';
require_once __DIR__ . '/urgency_helper.php';

$conn = new mysqli("localhost", "root", "", "ze_electronic");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

if (isset($_GET['id'])) {
    $id  = (int) $_GET['id'];
    $req = $conn->query("SELECT * FROM purchase_requests WHERE id = $id")->fetch_assoc();

    if ($req) {
        $quantity  = (float) $req['quantity'];
        $unitPrice = (float) $req['unit_price'];
        $currency  = strtoupper($req['currency'] ?? 'PHP');
        $totalValue = $quantity * $unitPrice;

        $sym          = CURRENCY_SYMBOLS[$currency] ?? $currency . ' ';
        $unitDisplay  = $sym . number_format($unitPrice, 4);
        $totalDisplay = $sym . number_format($totalValue, 2);

        // PHP equivalents (shown only when currency is not PHP)
        $unitPHP  = '';
        $totalPHP = '';
        if ($currency !== 'PHP') {
            $unitPHP  = '<span class="php-equiv">≈ ' . fmt_currency(to_php($unitPrice, $currency), 'PHP', 4) . '</span>';
            $totalPHP = '<span class="php-equiv">≈ ' . fmt_currency(to_php($totalValue, $currency), 'PHP', 2) . '</span>';
        }
        ?>

        <div class="detail-grid">
            <div>
                <h3 style="color:var(--green)">Request Info</h3>
                <p><strong>PR Number:</strong> <?= htmlspecialchars($req['pr_number']) ?></p>
                <p><strong>Requestor:</strong> <?= htmlspecialchars($req['requestor_name']) ?></p>
                <p><strong>Date:</strong> <?= date("M d, Y h:i A", strtotime($req['request_date'])) ?></p>
                <p><strong>Category:</strong> <?= htmlspecialchars($req['category']) ?>
                  <?php if (!empty($req['subcategory'])): ?>
                    &nbsp;/ <span style="color:#a78bfa"><?= htmlspecialchars($req['subcategory']) ?></span>
                  <?php endif; ?>
                </p>
                <p><strong>Quantity:</strong> <?= number_format($req['quantity']) ?></p>
                <p><strong>Item (MPN):</strong> <?= htmlspecialchars($req['mpn'] ?: '—') ?></p>
                <p><strong>Manufacturer:</strong> <?= htmlspecialchars($req['manufacturer'] ?: '—') ?></p>
                <p><strong>Currency:</strong> <?= htmlspecialchars($currency) ?></p>
                <p><strong>Unit Price:</strong>
                    <?= $unitDisplay ?><?= $unitPHP ?>
                </p>
                <p><strong>Distributor:</strong>
                    <?= htmlspecialchars($req['selected_distributor_text'] ?: ($req['distributor'] ?? '—')) ?>
                </p>

                <div style="margin-top:20px;padding-top:15px;border-top:2px solid var(--green);">
                    <p style="font-size:1.1em"><strong>Total Value:</strong>
                        <span style="color:var(--green);font-weight:bold;font-size:1.2em">
                            <?= $totalDisplay ?>
                        </span>
                        <?= $totalPHP ?>
                    </p>
                    <p style="font-size:.85em;color:#888">
                        (<?= number_format($quantity) ?> × <?= $unitDisplay ?>)
                    </p>
                </div>
            </div>

            <div>
                <h3 style="color:var(--green)">Justification</h3>
                <p><?= nl2br(htmlspecialchars($req['reason'])) ?></p>
                <?php if ($req['notes']): ?>
                    <h4>Additional Notes</h4>
                    <p><?= nl2br(htmlspecialchars($req['notes'])) ?></p>
                <?php endif; ?>
                <p><strong>Urgency:</strong> <?= urgency_badge($req['urgency'] ?? '') ?></p>
                <p><strong>Required By:</strong>
                    <?= $req['required_by'] ? date("M d, Y", strtotime($req['required_by'])) : '—' ?>
                </p>
            </div>
        </div>

        <?php
    } else {
        echo "<p style='color:#f87171'>Request not found.</p>";
    }
} else {
    echo "<p style='color:#f87171'>Invalid request.</p>";
}
$conn->close();
