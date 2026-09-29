<?php
// admin/dashboard/includes/head.php
if (!isset($page_title)) {
    $page_title = 'অ্যাডমিন ড্যাশবোর্ড | অন্ত্যমিল';
}
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($page_title); ?></title>

<!-- Google Fonts for Bengali & Mono -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<script src="../../assets/js/tailwind-config.js"></script>
<link rel="stylesheet" href="../../assets/css/style.css">

<style>
    .font-mono {
        font-family: 'JetBrains Mono', monospace;
    }
    .font-anek {
        font-family: 'Anek Bangla', sans-serif;
    }
    body {
        background-color: #faf8f5;
        color: #1c1917;
    }
    .sidebar-link.active {
        background: #1c1917;
        color: #ffffff;
    }
    /* Scrollbar styling for cream theme */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    ::-webkit-scrollbar-track {
        background: #faf8f5;
    }
    ::-webkit-scrollbar-thumb {
        background: #d6d0c4;
        border-radius: 3px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #b8b0a2;
    }
</style>
