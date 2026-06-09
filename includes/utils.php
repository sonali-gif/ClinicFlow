<?php
// Function to parse bill text into an array of items and calculate total
function parseBill($billText) {
    $items = [];
    $total = 0;
    if (empty($billText)) {
        return ['items' => $items, 'total' => $total];
    }

    // Try to decode as JSON first (for new structured prescriptions)
    $json_data = json_decode($billText, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($json_data)) {
        foreach ($json_data as $medicine) {
            if (isset($medicine['name'])) {
                // For pharmacy bill, initially set amount to 0, it will be filled by pharma
                $items[] = ['item' => htmlspecialchars($medicine['name']), 'amount' => 0];
            }
        }
        // If JSON is successfully parsed, we don't need to process further as a plain text bill
        return ['items' => $items, 'total' => $total];
    }
    
    // Fallback to old plain text parsing if not JSON or invalid JSON
    $lines = explode("\n", $billText);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        // Try to split by colon first, then by hyphen
        $parts = explode(':', $line, 2);
        if (count($parts) < 2) {
            $parts = explode('-', $line, 2);
        }

        if (count($parts) == 2) {
            $item = trim($parts[0]);
            // Remove non-numeric characters except for decimal point
            $amountStr = preg_replace('/[^0-9.]/', '', $parts[1]);
            $amount = (float)$amountStr;
            
            if ($item) {
                $items[] = ['item' => $item, 'amount' => $amount];
                $total += $amount;
            }
        } else {
            // If parsing fails, add as a single item with 0 amount
            $items[] = ['item' => $line, 'amount' => 0];
        }
    }
    return ['items' => $items, 'total' => $total];
}
?>