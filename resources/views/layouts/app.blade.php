<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? 'ProcureX' }} | PT. Dunia Kimia Jaya</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html{-webkit-font-smoothing:antialiased;font-size:13px;}
        body{font-family:'Inter',system-ui,sans-serif;background:#f3f4f6;color:#111827;min-height:150vh;display:flex}
 
        /* SIDEBAR */
        .sidebar{width:200px;height:100vh;background:#111827;display:flex;flex-direction:column;padding:20px 0;flex-shrink:0;position:fixed;top:0;left:0;bottom:0;z-index:100}
        .sidebar-brand{padding:0 16px 20px;display:flex;align-items:center;gap:9px;border-bottom:1px solid rgba(255,255,255,.07)}
        .sidebar-logo{width:32px;height:32px;background:#3b5bdb;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0}
        .sidebar-brand-name{font-size:13px;font-weight:700;color:#f9fafb;line-height:1.2}
        .sidebar-brand-sub{font-size:10px;color:#6b7280;margin-top:1px}
        .sidebar-nav{padding:12px 10px;display:flex;flex-direction:column;gap:2px;flex:1;overflow-y:auto;}
        .sidebar-label{font-size:10px;font-weight:600;color:#6b7280;letter-spacing:.08em;text-transform:uppercase;padding:8px 8px 4px;margin-top:6px}
        .sidebar-link{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:7px;color:#9ca3af;text-decoration:none;font-size:12.5px;font-weight:500;transition:background .15s,color .15s}
        .sidebar-link svg{flex-shrink:0;opacity:.7}
        .sidebar-link:hover{background:rgba(255,255,255,.07);color:#f9fafb}
        .sidebar-link:hover svg{opacity:1}
        .sidebar-link.active{background:rgba(255,255,255,.1);color:#f9fafb}
        .sidebar-link.active svg{opacity:1}
        .sidebar-footer{padding:10px;border-top:1px solid rgba(255,255,255,.07)}
        .sidebar-logout{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:7px;color:#f87171;font-size:12.5px;font-weight:500;background:none;border:none;cursor:pointer;width:100%;text-align:left;transition:background .15s}
        .sidebar-logout:hover{background:rgba(239,68,68,.08)}
 
        /* MAIN */
        .main-wrap{margin-left:200px;flex:1;display:flex;flex-direction:column;min-height:150vh;min-width:0}
 
        /* TOPBAR */
        .topbar{background:#fff;border-bottom:1px solid #e5e7eb;padding:0 28px;height:54px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50}
        .topbar-breadcrumb{display:flex;align-items:center;gap:5px;font-size:12px;color:#6b7280}
        .topbar-breadcrumb a{color:#6b7280;text-decoration:none}
        .topbar-breadcrumb a:hover{color:#111827}
        .topbar-breadcrumb .sep{color:#d1d5db}
        .topbar-breadcrumb .current{color:#111827;font-weight:500}
        .topbar-user{display:flex;align-items:center;gap:9px;font-size:12px}
        .topbar-avatar{width:30px;height:30px;background:#3b5bdb;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff}
        .topbar-name{font-weight:600;color:#111827;font-size:12.5px}
        .topbar-role{color:#9ca3af;font-size:11px}
 
        /* PAGE */
        .page-content{padding:24px 28px;flex:1;min-width:0;overflow-x:hidden}
        .page-footer{padding:14px 28px;font-size:11.5px;color:#9ca3af;border-top:1px solid #e5e7eb;background:#fff;display:flex;justify-content:space-between}
        .page-footer a{color:#9ca3af;text-decoration:none}
 
        /* ALERTS */
        .alert{padding:11px 14px;border-radius:8px;margin-bottom:16px;font-size:13px;border:1px solid}
        .alert-success{background:#f0fdf4;color:#15803d;border-color:#bbf7d0}
        .alert-danger{background:#fef2f2;color:#b91c1c;border-color:#fecaca}
        .alert ul{margin:4px 0 0 16px}
        
        /* NOTIFICATIONS */
        .notif-bell{position:relative;cursor:pointer;color:#6b7280;padding:6px;border-radius:50%;transition:background .15s,color .15s;display:flex;align-items:center;justify-content:center;}
        .notif-bell:hover{background:#f3f4f6;color:#111827}
        .notif-badge{position:absolute;top:2px;right:2px;background:#ef4444;color:#fff;font-size:9px;font-weight:800;width:15px;height:15px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid #fff;}
        .notif-dropdown{position:absolute;top:48px;right:0;width:320px;background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,.08);display:none;z-index:100;overflow:hidden;}
        .notif-header{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #f3f4f6;background:#f9fafb;}
        .notif-header span{font-size:13px;font-weight:700;color:#111827;}
        .notif-header button{background:none;border:none;color:#3b5bdb;font-size:11px;font-weight:600;cursor:pointer;}
        .notif-header button:hover{text-decoration:underline;}
        .notif-list{max-height:300px;overflow-y:auto;}
        .notif-item{padding:12px 16px;border-bottom:1px solid #f3f4f6;cursor:pointer;transition:background .15s;}
        .notif-item:hover{background:#f9fafb;}
        .notif-item.unread{background:#eff6ff;}
        .notif-item.unread:hover{background:#e0e7ff;}
        .notif-item:last-child{border-bottom:none;}
        
        /* TOAST */
        .toast-container{position:fixed;bottom:24px;right:24px;display:flex;flex-direction:column;gap:12px;z-index:9999;}
        .toast{background:#fff;border-left:4px solid #3b5bdb;border-radius:8px;box-shadow:0 10px 25px rgba(0,0,0,.15);padding:14px 18px;width:340px;transform:translateX(120%);transition:transform .3s cubic-bezier(0.34, 1.56, 0.64, 1);opacity:0;}
        .toast.show{transform:translateX(0);opacity:1;}

        /* UTILITIES */
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            color: #4b5563;
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-back:hover {
            background: #f9fafb;
            color: #111827;
            border-color: #9ca3af;
        }
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('img/Logo_DKJ.jpeg') }}" alt="DKJ Logo" class="sidebar-logo">
        <div>
            <div class="sidebar-brand-name">PT. Dunia Kimia Jaya</div>
            <div class="sidebar-brand-sub">Procurement Portal</div>
        </div>
    </div>
    <nav class="sidebar-nav">
        <span class="sidebar-label">Main Menu</span>
        @auth
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
            Dashboard
        </a>
        <a href="{{ route('purchase_requests.create') }}" class="sidebar-link {{ request()->routeIs('purchase_requests.create') ? 'active' : '' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
            New Request
        </a>
        <a href="{{ route('pr.list') }}" class="sidebar-link {{ request()->routeIs('pr.list') ? 'active' : '' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            PR List
        </a>
        @if(Auth::user()->role === 'purchasing')
        <div style="margin-bottom:2px">
            <div class="sidebar-link" style="color:#9ca3af;cursor:default;">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Procurement History
            </div>
            <div style="display:flex;flex-direction:column;gap:2px;padding-left:26px;margin-top:2px;">
                <a href="{{ route('history.orders') }}" class="sidebar-link {{ request()->routeIs('history.orders') ? 'active' : '' }}" style="padding:6px 10px;font-size:11.5px">Order Records</a>
                <a href="{{ route('history.items') }}" class="sidebar-link {{ request()->routeIs('history.items') ? 'active' : '' }}" style="padding:6px 10px;font-size:11.5px">Item Catalog</a>
                <a href="{{ route('history.vendors') }}" class="sidebar-link {{ request()->routeIs('history.vendors') ? 'active' : '' }}" style="padding:6px 10px;font-size:11.5px">Vendor Directory</a>
            </div>
        </div>
        <a href="{{ route('history.master.vendors') }}" class="sidebar-link {{ request()->routeIs('history.master.vendors') || request()->routeIs('history.vendor.detail') ? 'active' : '' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><path d="M9 22v-4h6v4M8 6h.01M16 6h.01M12 6h.01M8 10h.01M16 10h.01M12 10h.01M8 14h.01M16 14h.01M12 14h.01M8 18h.01M16 18h.01M12 18h.01"/></svg>
            Master Vendor
        </a>
        @endif
        <a href="{{ route('items.index') }}" class="sidebar-link {{ request()->routeIs('items.*') ? 'active' : '' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Master Item
        </a>
        <a href="{{ route('vendors.list') }}" class="sidebar-link {{ request()->routeIs('vendors.*') ? 'active' : '' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Vendor Selection
        </a>
        @endauth
    </nav>
    @auth
    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="post">@csrf
            <button type="submit" class="sidebar-logout">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Logout
            </button>
        </form>
    </div>
    @endauth
</aside>
 
<div class="main-wrap">
    <header class="topbar">
        <div class="topbar-breadcrumb">
            <a href="{{ route('dashboard') }}">Portal</a>
            <span class="sep">/</span>
            <span class="current">{{ $pageTitle ?? 'Dashboard' }}</span>
        </div>
        @auth
        <div class="topbar-user" style="position:relative; cursor:pointer;" onclick="toggleUserDropdown()">
            <!-- Bell Icon -->
            <div id="notif-bell" class="notif-bell" onclick="toggleNotifDropdown(); event.stopPropagation();">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                <span id="notif-badge" class="notif-badge" style="display:none;">0</span>
            </div>
            <!-- Dropdown -->
            <div id="notif-dropdown" class="notif-dropdown" onclick="event.stopPropagation();">
                <div class="notif-header">
                    <span>Notifications</span>
                    <button onclick="markAllNotifAsRead()">Mark all as read</button>
                </div>
                <div id="notif-list" class="notif-list">
                    <div style="padding:16px;text-align:center;color:#9ca3af;font-size:12px;">Loading...</div>
                </div>
            </div>

            <div style="width:1px;height:24px;background:#e5e7eb;margin:0 4px;"></div>

            <div class="topbar-avatar">{{ strtoupper(substr(Auth::user()->name,0,2)) }}</div>
            <div style="display:flex; align-items:center; gap:6px;">
                <div>
                    <div class="topbar-name">{{ Auth::user()->name }}</div>
                    <div class="topbar-role">{{ Auth::user()->role === 'purchasing' ? 'Purchasing' : 'IT Staff' }}</div>
                </div>
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:#9ca3af;"><path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>

            <div id="user-dropdown" style="display:none; position:absolute; top:100%; right:0; background:#fff; border:1px solid #e5e7eb; border-radius:8px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1); margin-top:12px; width:160px; z-index:100; overflow:hidden;">
                <form action="{{ route('logout') }}" method="post" style="margin:0;">
                    @csrf
                    <button type="submit" style="width:100%; text-align:left; background:none; border:none; padding:12px 16px; font-size:13px; font-weight:500; color:#ef4444; cursor:pointer; display:flex; align-items:center; gap:8px;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
        @endauth
    </header>
 
    <main class="page-content">
        @yield('content')
    </main>
 
    <footer class="page-footer">
        <span>© 2026 ProcureX · Employee Portal</span>
        <div style="display:flex;gap:12px;"><a href="#">Help</a><a href="#">Privacy</a><a href="#">Contact IT</a></div>
    </footer>
</div>



@auth
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let lastNotifIds = new Set();
    let isFirstFetch = true;
    
    function toggleNotifDropdown() {
        const dd = document.getElementById('notif-dropdown');
        dd.style.display = dd.style.display === 'block' ? 'none' : 'block';
        document.getElementById('user-dropdown').style.display = 'none';
    }

    function toggleUserDropdown() {
        const dd = document.getElementById('user-dropdown');
        dd.style.display = dd.style.display === 'block' ? 'none' : 'block';
        document.getElementById('notif-dropdown').style.display = 'none';
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.topbar-user')) {
            const ud = document.getElementById('user-dropdown');
            if (ud) ud.style.display = 'none';
            
            const nd = document.getElementById('notif-dropdown');
            if (nd && !e.target.closest('#notif-bell')) nd.style.display = 'none';
        }
    });

    function showToast(title, message, link = null, icon = 'success') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
                if (link) {
                    toast.style.cursor = 'pointer';
                    toast.addEventListener('click', () => window.location.href = link)
                }
            }
        });

        Toast.fire({
            icon: icon,
            title: `<span style="font-size:14px;font-weight:600;">${title}</span>`,
            html: `<span style="font-size:12px;">${message}</span>`
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            showToast('Berhasil', "{!! addslashes(session('success')) !!}", null, 'success');
        @endif
        @if(session('error'))
            showToast('Gagal', "{!! addslashes(session('error')) !!}", null, 'error');
        @endif
        @if($errors->any())
            showToast('Validation Error', 'Terdapat error pada input Anda. Silakan periksa kembali.', null, 'warning');
        @endif
    });

    function showConfirmModal(title, message, confirmText, confirmColor, onConfirm) {
        Swal.fire({
            title: `<span style="font-size:18px;font-weight:700;">${title}</span>`,
            html: `<span style="font-size:14px;">${message}</span>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: confirmColor || '#3b82f6',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: confirmText || 'Ya',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'swal-custom-popup',
                confirmButton: 'swal-custom-btn',
                cancelButton: 'swal-custom-btn'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                if (onConfirm) onConfirm();
            }
        });
    }

    function fetchNotifications() {
        fetch('{{ route("notifications.fetch") }}', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if(data.error) return;
            
            // Update badge
            const badge = document.getElementById('notif-badge');
            if (data.unread_count > 0) {
                badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }

            // Update dropdown list
            const list = document.getElementById('notif-list');
            if(data.notifications && data.notifications.length > 0) {
                list.innerHTML = '';
                data.notifications.forEach(n => {
                    const d = typeof n.data === 'string' ? JSON.parse(n.data) : n.data;
                    let link = `/vendor-selection?key=${d.category}_${d.pr_id || d.rfq_id}`;
                    
                    if (n.type && n.type.includes('VendorSelectedNotification')) {
                        const baseUrl = "{{ route('pr.list') }}";
                        link = `${baseUrl}?open_pr=${d.pr_id}&category=${d.category}`;
                    }
                    
                    // Check if it's new and unread, then show toast
                    if (n.read_at === null && !lastNotifIds.has(n.id) && !isFirstFetch) {
                        showToast('New Notification', `<b>${d.vendor_name || ''}</b> ${d.message} <b>${d.document_number || ''}</b>`, link);
                    }
                    lastNotifIds.add(n.id);

                    const item = document.createElement('div');
                    item.className = 'notif-item ' + (n.read_at === null ? 'unread' : '');
                    item.onclick = () => {
                        markNotifAsRead(n.id, link);
                    };
                    item.innerHTML = `
                        <div style="font-size:12px;color:#374151;line-height:1.4;">
                            <span style="font-weight:600;color:#111827;">${d.vendor_name}</span> ${d.message} <span style="font-family:monospace;font-weight:600;color:#3b5bdb;">${d.document_number}</span>
                        </div>
                        <div style="font-size:10px;color:#9ca3af;margin-top:4px;">
                            ${new Date(n.created_at).toLocaleString('id-ID', {day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'})}
                        </div>
                    `;
                    list.appendChild(item);
                });
            } else {
                list.innerHTML = '<div style="padding:16px;text-align:center;color:#9ca3af;font-size:12px;">No notifications yet.</div>';
            }
            
            isFirstFetch = false;
        })
        .catch(err => console.error(err));
    }

    function markNotifAsRead(id, link = null) {
        fetch('{{ route("notifications.read") }}', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ id: id })
        }).then(() => {
            if(link) window.location.href = link;
            else fetchNotifications();
        });
    }

    function markAllNotifAsRead() {
        markNotifAsRead(null);
    }

    // Close dropdown on click outside
    document.addEventListener('click', function(e) {
        const bell = document.getElementById('notif-bell');
        const dd = document.getElementById('notif-dropdown');
        if (dd && dd.style.display === 'block' && bell && !bell.contains(e.target) && !dd.contains(e.target)) {
            dd.style.display = 'none';
        }
    });

    // Auto-open modal if URL contains open_pr
    document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        const openPrId = params.get('open_pr');
        const openCategory = params.get('category');
        if(openPrId && openCategory) {
            setTimeout(() => {
                if(typeof openPRDetail === 'function') openPRDetail(openPrId, openCategory);
                if(typeof openDetailModal === 'function') openDetailModal(openPrId, openCategory);
            }, 500);
        }
    });

    // Initial fetch and start polling
    fetchNotifications();
    setInterval(fetchNotifications, 30000);
</script>
@endauth

</body>
</html>