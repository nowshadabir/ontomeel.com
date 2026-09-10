<?php
/**
 * Ontomeel Bookshop - Automated Git Deployment Webhook
 * Triggers `git pull origin main` automatically when pushed from GitHub.
 */

// 1. Load Environment Variables (.env)
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $name = trim($parts[0]);
            $value = trim($parts[1], " \t\n\r\0\x0B\"'");
            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
}

// 2. Fetch Secret Deployment Token from ENV
$secret_token = getenv('DEPLOY_SECRET_KEY') ?: ($_ENV['DEPLOY_SECRET_KEY'] ?? '');

if (empty($secret_token)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'status' => 'error',
        'message' => 'সার্ভার কনফিগারেশন ত্রুটি: .env ফাইলে DEPLOY_SECRET_KEY সেট করা নেই।'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// 3. Security Check
$provided_token = $_GET['token'] ?? $_POST['token'] ?? $_SERVER['HTTP_X_DEPLOY_TOKEN'] ?? '';

// GitHub Ping Event Support
$github_event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';
if ($github_event === 'ping') {
    http_response_code(200);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['status' => 'success', 'message' => 'GitHub Ping Received Successfully!']);
    exit();
}

if (!hash_equals($secret_token, (string)$provided_token)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'status' => 'error',
        'message' => 'অননুমোদিত অ্যাক্সেস (Unauthorized): Invalid or missing security token.'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// 4. Environment & Git Discovery
header('Content-Type: application/json; charset=UTF-8');
set_time_limit(120);

$repo_dir = __DIR__;
chdir($repo_dir);

// Find git executable path
$git_bin = 'git';
$possible_paths = ['/usr/bin/git', '/usr/local/bin/git', '/usr/bin/cpanel_git', 'git'];
foreach ($possible_paths as $path) {
    if (@is_executable($path)) {
        $git_bin = $path;
        break;
    }
}

// 4. Execute Git Deployment
$commands = [
    "{$git_bin} rev-parse --is-inside-work-tree 2>&1",
    "{$git_bin} fetch origin main 2>&1",
    "{$git_bin} reset --hard origin/main 2>&1",
    "{$git_bin} log -1 --pretty=format:'%h - %an: %s (%cr)' 2>&1"
];

$output_log = [];
$status_success = true;

foreach ($commands as $cmd) {
    $out = shell_exec($cmd);
    $output_log[] = [
        'command' => $cmd,
        'output' => trim($out ?? '')
    ];
}

// 5. Response
$last_commit = end($output_log)['output'] ?? 'Unknown';

echo json_encode([
    'status' => 'success',
    'message' => 'ডিপ্লয়মেন্ট সফলভাবে সম্পন্ন হয়েছে!',
    'repo_directory' => $repo_dir,
    'deployed_at' => date('Y-m-d H:i:s'),
    'last_commit' => $last_commit,
    'log' => $output_log
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit();
