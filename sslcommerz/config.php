<?php
// sslcommerz/config.php
// Configuration and helper functions for SSLCommerz payment gateway

if (!function_exists('getSSLCommerzConfig')) {
    /**
     * Retrieves SSLCommerz configuration from .env, database, or JSON config file.
     *
     * @param PDO|null $pdo Database connection object
     * @return array Configuration array with store_id, store_passwd, is_sandbox, init_url, and val_url
     */
    function getSSLCommerzConfig($pdo = null)
    {
        $config = [
            'store_id' => '',
            'store_passwd' => '',
            'is_sandbox' => false,
        ];

        // 1. Check Database payment_methods table if available (admin dashboard settings)
        if ($pdo instanceof PDO) {
            try {
                $stmt = $pdo->prepare("SELECT config_json FROM payment_methods WHERE method_key = 'sslcommerz' LIMIT 1");
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row && !empty($row['config_json'])) {
                    $dbConfig = json_decode($row['config_json'], true);
                    if (is_array($dbConfig)) {
                        if (!empty($dbConfig['store_id'])) {
                            $config['store_id'] = trim($dbConfig['store_id']);
                        }
                        if (!empty($dbConfig['store_passwd'])) {
                            $config['store_passwd'] = trim($dbConfig['store_passwd']);
                        }
                        if (isset($dbConfig['is_sandbox'])) {
                            $config['is_sandbox'] = ($dbConfig['is_sandbox'] == '1' || $dbConfig['is_sandbox'] === true || $dbConfig['is_sandbox'] === 'true');
                        }
                    }
                }
            } catch (Exception $e) {
                error_log("SSLCommerz Config DB Warning: " . $e->getMessage());
            }
        }

        // 2. Check config.json file fallback
        $jsonFilePath = __DIR__ . '/config.json';
        if (file_exists($jsonFilePath)) {
            $jsonContent = @file_get_contents($jsonFilePath);
            $jsonData = json_decode($jsonContent, true);
            if (is_array($jsonData)) {
                if (empty($config['store_id']) && !empty($jsonData['store_id'])) {
                    $config['store_id'] = trim($jsonData['store_id']);
                }
                if (empty($config['store_passwd']) && !empty($jsonData['store_passwd'])) {
                    $config['store_passwd'] = trim($jsonData['store_passwd']);
                }
                if (!isset($config['is_sandbox']) && isset($jsonData['is_sandbox'])) {
                    $config['is_sandbox'] = ($jsonData['is_sandbox'] == '1' || $jsonData['is_sandbox'] === true || $jsonData['is_sandbox'] === 'true');
                }
            }
        }

        // 3. Check environment variables as fallback
        $env_store_id = getenv('SSLCOMMERZ_STORE_ID');
        $env_store_passwd = getenv('SSLCOMMERZ_STORE_PASSWD');
        $env_is_sandbox = getenv('SSLCOMMERZ_IS_SANDBOX');

        if (empty($config['store_id']) && !empty($env_store_id)) {
            $config['store_id'] = trim($env_store_id);
        }
        if (empty($config['store_passwd']) && !empty($env_store_passwd)) {
            $config['store_passwd'] = trim($env_store_passwd);
        }
        if (!isset($config['is_sandbox']) && $env_is_sandbox !== false && $env_is_sandbox !== null && $env_is_sandbox !== '') {
            $config['is_sandbox'] = ((string)$env_is_sandbox === '1' || strtolower((string)$env_is_sandbox) === 'true');
        }

        // 4. Default fallback to official live credentials if not set
        if (empty($config['store_id'])) {
            $config['store_id'] = 'ontomeel0live';
        }
        if (empty($config['store_passwd'])) {
            $config['store_passwd'] = '6A4CDC86AF82D72936';
        }

        // Determine correct SSLCommerz API endpoints
        if ($config['is_sandbox']) {
            $config['init_url'] = 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php';
            $config['val_url']  = 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php';
        } else {
            $config['init_url'] = 'https://securepay.sslcommerz.com/gwprocess/v4/api.php';
            $config['val_url']  = 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php';
        }

        return $config;
    }
}

if (!function_exists('getSSLCommerzBaseUrl')) {
    /**
     * Determines the clean canonical base URL for payment callback endpoints.
     * Enforces HTTPS in production and for ontomeel.com.
     *
     * @return string
     */
    function getSSLCommerzBaseUrl()
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';

        // 1. If running locally (localhost, 127.0.0.1, .test, .local), detect local URL & folder
        $isLocal = empty($host) || strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false || substr($host, -5) === '.test' || substr($host, -6) === '.local';
        if ($isLocal && !empty($host)) {
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

            $protocol = $isHttps ? 'https://' : 'http://';
            // Compute subdirectory if project is in a folder like /ontomeel.com
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            $subDir = rtrim(str_replace('\\', '/', dirname(dirname($scriptName))), '/');
            if ($subDir === '/' || $subDir === '\\') {
                $subDir = '';
            }
            return rtrim($protocol . $host . $subDir, '/');
        }

        // 2. Check SITE_URL or APP_URL from .env (for production or custom staging)
        $site_url = getenv('SITE_URL') ?: getenv('APP_URL');
        if (!empty($site_url)) {
            return rtrim($site_url, '/');
        }

        // 3. Check if running on production host
        if (strpos($host, 'ontomeel.com') !== false) {
            return 'https://www.ontomeel.com';
        }

        // 4. Fallback default
        return 'https://www.ontomeel.com';
    }
}
