import sys

changed_total = 0

path1 = "routes/web.php"
with open(path1, "r", encoding="utf-8") as f:
    c1 = f.read()

old1 = "    /* Master Items */\n    Route::middleware('purchasing')->prefix('master-items')->name('items.')->group(function () {"
new1 = "    /* Master Items */\n    Route::prefix('master-items')->name('items.')->group(function () {"

if old1 in c1:
    c1 = c1.replace(old1, new1, 1)
    with open(path1, "w", encoding="utf-8") as f:
        f.write(c1)
    print("BERHASIL: routes/web.php diubah — middleware 'purchasing' dihapus dari route Master Item.")
    changed_total += 1
else:
    print("PERINGATAN: marker route Master Item tidak ditemukan di routes/web.php.")

path2 = "resources/views/layouts/app.blade.php"
with open(path2, "r", encoding="utf-8") as f:
    c2 = f.read()

old2 = """        <a href="{{ route('history.master.vendors') }}" class="sidebar-link {{ request()->routeIs('history.master.vendors') || request()->routeIs('history.vendor.detail') ? 'active' : '' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><path d="M9 22v-4h6v4M8 6h.01M16 6h.01M12 6h.01M8 10h.01M16 10h.01M12 10h.01M8 14h.01M16 14h.01M12 14h.01M8 18h.01M16 18h.01M12 18h.01"/></svg>
            Master Vendor
        </a>
        <a href="{{ route('items.index') }}" class="sidebar-link {{ request()->routeIs('items.*') ? 'active' : '' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Master Item
        </a>
        @endif"""

new2 = """        <a href="{{ route('history.master.vendors') }}" class="sidebar-link {{ request()->routeIs('history.master.vendors') || request()->routeIs('history.vendor.detail') ? 'active' : '' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><path d="M9 22v-4h6v4M8 6h.01M16 6h.01M12 6h.01M8 10h.01M16 10h.01M12 10h.01M8 14h.01M16 14h.01M12 14h.01M8 18h.01M16 18h.01M12 18h.01"/></svg>
            Master Vendor
        </a>
        @endif
        <a href="{{ route('items.index') }}" class="sidebar-link {{ request()->routeIs('items.*') ? 'active' : '' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Master Item
        </a>"""

if old2 in c2:
    c2 = c2.replace(old2, new2, 1)
    with open(path2, "w", encoding="utf-8") as f:
        f.write(c2)
    print("BERHASIL: sidebar diubah — link Master Item sekarang di luar blok purchasing-only.")
    changed_total += 1
else:
    print("PERINGATAN: marker sidebar tidak ditemukan.")

print(f"\nTotal file diubah: {changed_total}/2")
