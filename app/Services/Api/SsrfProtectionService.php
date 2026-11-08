<?php

namespace App\Services\Api;

class SsrfProtectionService
{
    /**
     * Blocked CIDR ranges (IPv4).
     */
    protected static $blockedIpv4Ranges = [
        '0.0.0.0/8',          // Current network ("this" host)
        '10.0.0.0/8',         // Private-use networks (RFC 1918)
        '100.64.0.0/10',      // Carrier-grade NAT (RFC 6598)
        '127.0.0.0/8',        // Loopback (RFC 1122)
        '169.254.0.0/16',     // Link-local / Cloud Metadata (AWS, Azure, GCP, DO)
        '172.16.0.0/12',      // Private-use networks (RFC 1918)
        '192.0.0.0/24',       // IETF Protocol Assignments
        '192.0.2.0/24',       // TEST-NET-1 (RFC 5737)
        '192.88.99.0/24',     // 6to4 Relay Anycast (RFC 7526)
        '192.168.0.0/16',     // Private-use networks (RFC 1918)
        '198.18.0.0/15',      // Network Interconnect Device Benchmark
        '198.51.100.0/24',    // TEST-NET-2 (RFC 5737)
        '203.0.113.0/24',     // TEST-NET-3 (RFC 5737)
        '224.0.0.0/4',        // Multicast (RFC 5771)
        '240.0.0.0/4',        // Reserved for future use (RFC 1112)
        '255.255.255.255/32', // Limited broadcast
    ];

    /**
     * Blocked domain names (cloud metadata, internal aliases).
     */
    protected static $blockedDomains = [
        'localhost',
        'localhost.localdomain',
        'metadata.google.internal',
        'metadata.google',
        'instance-data',
        'internal',
        'local',
    ];

    /**
     * Validate a user-provided URL against SSRF vulnerabilities.
     *
     * @param string $url
     * @return array [bool $isSafe, string|null $reason]
     */
    public static function validateUrl($url)
    {
        if (empty($url) || !is_string($url)) {
            return [false, 'URL is empty or invalid.'];
        }

        // Scheme validation
        $parsed = @parse_url($url);
        if (!$parsed || empty($parsed['scheme']) || empty($parsed['host'])) {
            return [false, 'Malformed URL or missing host.'];
        }

        $scheme = strtolower($parsed['scheme']);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return [false, 'Only HTTP and HTTPS protocols are permitted.'];
        }

        // Block embedded user credentials (e.g. http://admin:pass@host)
        if (!empty($parsed['user']) || !empty($parsed['pass'])) {
            return [false, 'Embedded authentication credentials in URL are prohibited.'];
        }

        $host = strtolower($parsed['host']);

        // Check if host matches blocked domain
        foreach (self::$blockedDomains as $blocked) {
            if ($host === $blocked || substr($host, -(strlen($blocked) + 1)) === '.' . $blocked) {
                return [false, 'Access to internal or reserved hostnames is blocked.'];
            }
        }

        list($ips, $dnsReason) = self::resolveIps($host);
        if (empty($ips)) {
            return [false, $dnsReason ?: 'Unable to resolve destination hostname via DNS.'];
        }

        // Validate each resolved IP against blocked ranges
        foreach ($ips as $ip) {
            if (self::isIpBlocked($ip)) {
                return [false, 'The target hostname resolves to a restricted or private network address.'];
            }
        }

        return [true, null];
    }

    /**
     * Resolve a host to all of its public IP addresses.
     *
     * @param string $host
     * @return array [array $ips, string|null $reason]
     */
    protected static function resolveIps($host)
    {
        $ips = [];
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $ips[] = $host;
            return [$ips, null];
        }

        $dnsRecords = @dns_get_record($host, DNS_A + DNS_AAAA);
        if (!empty($dnsRecords)) {
            foreach ($dnsRecords as $rec) {
                if (isset($rec['ip'])) {
                    $ips[] = $rec['ip'];
                } elseif (isset($rec['ipv6'])) {
                    $ips[] = $rec['ipv6'];
                }
            }
        } else {
            $resolved = @gethostbyname($host);
            if ($resolved && $resolved !== $host && filter_var($resolved, FILTER_VALIDATE_IP)) {
                $ips[] = $resolved;
            }
        }

        return [$ips, null];
    }

    /**
     * Validate a URL for SSRF and return its safe, publicly-resolved IP set.
     * The returned IPs can be pinned (e.g. curl CURLOPT_RESOLVE) so the
     * connection cannot be diverted to an internal address by DNS
     * re-resolution or an attacker-controlled redirect.
     *
     * @param string $url
     * @return array [bool $isSafe, string|null $reason, array $ips]
     */
    public static function resolveSafe($url)
    {
        list($isSafe, $reason) = self::validateUrl($url);
        if (!$isSafe) {
            return [false, $reason, []];
        }

        $host = strtolower(parse_url($url, PHP_URL_HOST));
        list($ips) = self::resolveIps($host);

        return [true, null, $ips];
    }

    /**
     * Check if a single IP address (v4 or v6) is in a blocked/reserved subnet.
     *
     * @param string $ip
     * @return bool
     */
    public static function isIpBlocked($ip)
    {
        // IPv6 checks
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // ::1 (Loopback)
            if ($ip === '::1' || $ip === '0:0:0:0:0:0:0:1') {
                return true;
            }
            // Unique Local Address (fc00::/7)
            $hex = bin2hex(inet_pton($ip));
            if (substr($hex, 0, 2) === 'fc' || substr($hex, 0, 2) === 'fd') {
                return true;
            }
            // Link-local (fe80::/10)
            if (substr($hex, 0, 3) === 'fe8' || substr($hex, 0, 3) === 'fe9' || substr($hex, 0, 3) === 'fea' || substr($hex, 0, 3) === 'feb') {
                return true;
            }
            return false;
        }

        // IPv4 checks
        $longIp = ip2long($ip);
        if ($longIp === false) {
            return true; // invalid IP treated as unsafe
        }

        foreach (self::$blockedIpv4Ranges as $range) {
            if (self::cidrMatch($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an IPv4 address matches a CIDR subnet.
     */
    protected static function cidrMatch($ip, $cidr)
    {
        list($subnet, $bits) = explode('/', $cidr);
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $mask = -1 << (32 - (int)$bits);
        $subnetMasked = $subnetLong & $mask;

        return ($ipLong & $mask) == $subnetMasked;
    }
}
