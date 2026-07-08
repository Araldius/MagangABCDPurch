import subprocess, sys, os

result = subprocess.run(
    ["find", ".", "-name", "DashboardController.php"],
    capture_output=True, text=True
)
paths = [p for p in result.stdout.strip().split("\n") if p]

if not paths:
    print("File DashboardController.php tidak ditemukan.")
    sys.exit(1)

path = paths[0]
print("File ditemukan di:", path)

with open(path, "r", encoding="utf-8") as f:
    content = f.read()

old_str = """        // Detail item per vendor — diambil dari order records (PR items, match via purchase_request_item_id)
        $items = $group->flatMap(function ($selection) {
            $prItems     = optional(optional($selection->rfq)->purchaseRequest)->items ?? collect();
            $prItemsById = $prItems->keyBy('id');

    private function userDashboard($request)
    {"""

new_str = """        // Detail item per vendor — diambil dari order records (PR items, match via purchase_request_item_id)
        $items = $group->flatMap(function ($selection) {
            $prItems     = optional(optional($selection->rfq)->purchaseRequest)->items ?? collect();
            $prItemsById = $prItems->keyBy('id');

            return $selection->selectionItems->map(function ($si) use ($prItemsById) {
                $prItem = $prItemsById->get($si->purchase_request_item_id);
                return [
                    'item_name' => $prItem->item_name ?? ($prItem->name ?? ('Item #' . $si->purchase_request_item_id)),
                    'quantity'  => $si->final_quantity,
                    'price'     => $si->final_price_per_item,
                    'total'     => ($si->final_price_per_item ?? 0) * ($si->final_quantity ?? 0),
                ];
            });
        });

            return [
                'vendor'      => $vendorName,
                'frequency'   => $frequency,
                'total_value' => $totalValue,
                'items'       => $items->values(),
            ];
        })->values();

        return response()->json(['data' => $data]);
    }

    private function userDashboard($request)
    {"""

if old_str not in content:
    print("MARKER TIDAK DITEMUKAN. Isi baris 40-65 file saat ini:")
    lines = content.split("\n")
    for i, line in enumerate(lines[39:65], start=40):
        print(i, line)
    sys.exit(1)

content = content.replace(old_str, new_str, 1)

with open(path, "w", encoding="utf-8") as f:
    f.write(content)

print("BERHASIL diperbaiki:", path)
