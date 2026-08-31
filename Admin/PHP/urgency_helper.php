<?php
/**
 * urgency_helper.php
 * Include this wherever urgency badges need to be rendered consistently.
 */

/**
 * Returns the CSS class for an urgency level.
 */
function urgency_class(string $urgency): string {
    return match(strtolower(trim($urgency))) {
        'critical' => 'urgency-critical',
        'high'     => 'urgency-high',
        default    => 'urgency-normal',
    };
}

/**
 * Returns the icon for an urgency level.
 */
function urgency_icon(string $urgency): string {
    return match(strtolower(trim($urgency))) {
        'critical' => '<i class="fas fa-circle-exclamation"></i>',
        'high'     => '<i class="fas fa-arrow-up"></i>',
        default    => '<i class="fas fa-minus"></i>',
    };
}

/**
 * Returns a full urgency badge HTML span.
 */
function urgency_badge(string $urgency): string {
    if (empty(trim($urgency))) return '';
    $cls  = urgency_class($urgency);
    $icon = urgency_icon($urgency);
    $label = htmlspecialchars(ucfirst(strtolower(trim($urgency))));
    return "<span class=\"urgency-badge {$cls}\">{$icon} {$label}</span>";
}

/**
 * Returns the CSS class for a table row based on urgency.
 */
function urgency_row_class(string $urgency): string {
    return match(strtolower(trim($urgency))) {
        'critical' => 'urgency-row-critical',
        'high'     => 'urgency-row-high',
        default    => '',
    };
}
