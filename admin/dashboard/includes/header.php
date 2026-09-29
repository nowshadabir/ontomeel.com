<?php
// admin/dashboard/includes/header.php
if (!isset($header_title)) {
    $header_title = $page_title ?? 'ড্যাশবোর্ড';
}
if (!isset($header_subtitle)) {
    $header_subtitle = 'অন্ত্যমিল অ্যাডমিন প্যানেল';
}
?>
<!-- Top Navigation Bar -->
<header class="bg-white/90 backdrop-blur-md px-6 lg:px-10 py-3 border-b border-[#e7e3da] sticky top-0 z-30 flex items-center justify-between shadow-xs">
    <div class="flex items-center gap-3">
        <button onclick="toggleSidebar()" class="lg:hidden text-stone-600 hover:text-stone-900 p-1.5 rounded-lg hover:bg-[#f3f0e8] transition-colors" aria-label="মেনু খুলুন">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
        </button>
        <div>
            <div class="text-xs font-mono text-stone-500 uppercase tracking-wider">ADMIN / <?php echo strtoupper($current_page ?? 'DASHBOARD'); ?></div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <a href="/" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 text-xs font-medium text-stone-600 hover:text-stone-950 bg-[#f4f1ea] hover:bg-[#ebe6dc] px-3 py-1.5 rounded-lg border border-[#ded8cc] transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
            <span>ওয়েবসাইট দেখুন</span>
        </a>
        <div class="flex items-center gap-2.5 pl-2 border-l border-[#e7e3da]">
            <div class="text-right hidden sm:block">
                <p class="text-xs font-semibold text-stone-900 font-anek">
                    <?php echo htmlspecialchars($_SESSION['admin_full_name'] ?? $_SESSION['admin_name'] ?? $_SESSION['admin_username'] ?? 'Admin'); ?>
                </p>
                <p class="text-[10px] text-stone-500 font-mono">
                    <?php echo htmlspecialchars($_SESSION['admin_role'] ?? 'Administrator'); ?>
                </p>
            </div>
            <div class="w-8 h-8 rounded-full bg-[#f4f1ea] border border-[#ded8cc] text-stone-800 flex items-center justify-center font-mono text-xs font-bold shadow-xs">
                <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)); ?>
            </div>
        </div>
    </div>
</header>
