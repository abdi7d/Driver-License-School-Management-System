<?php
include_once __DIR__ . '/../config/jwt.php';

class JWT {
    private static function secret() {
        return defined('JWT_SECRET') ? JWT_SECRET : (getenv('JWT_SECRET') ?: 'drivepro_secret_key_2026');
    }

    public static function generate($payload) {
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payloadJson = json_encode($payload);

        $base64Header = self::base64UrlEncode($header);
        $base64Payload = self::base64UrlEncode($payloadJson);

        $signature = hash_hmac(
            'sha256',
            $base64Header . '.' . $base64Payload,
            self::secret(),
            true
        );

        return $base64Header . '.' . $base64Payload . '.' . self::base64UrlEncode($signature);
    }

    public static function verify($token) {
        $parts = explode('.', (string)$token);
        if (count($parts) !== 3) {
            return false;
        }

        [$header, $payload, $signature] = $parts;
        $expectedSignature = self::base64UrlEncode(
            hash_hmac('sha256', $header . '.' . $payload, self::secret(), true)
        );

        if (!hash_equals($expectedSignature, $signature)) {
            return false;
        }

        $decoded = json_decode(self::base64UrlDecode($payload), true);
        if (!is_array($decoded)) {
            return false;
        }

        if (isset($decoded['exp']) && time() > (int)$decoded['exp']) {
            return false;
        }

        return $decoded;
    }

    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode($data) {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'));
    }
}
?>