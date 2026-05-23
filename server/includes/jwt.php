<?php

class JWT {

    private static $secret = "drivepro_secret_key_2026";

    // CREATE TOKEN
    public static function generate($payload) {

        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);

        $payload = json_encode($payload);

        $base64Header = self::base64UrlEncode($header);
        $base64Payload = self::base64UrlEncode($payload);

        $signature = hash_hmac(
            'sha256',
            $base64Header . "." . $base64Payload,
            self::$secret,
            true
        );

        $base64Signature = self::base64UrlEncode($signature);

        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }

    // VERIFY TOKEN
    public static function verify($token) {

        $parts = explode(".", $token);
        if (count($parts) !== 3) return false;

        [$header, $payload, $signature] = $parts;

        $validSignature = self::base64UrlEncode(
            hash_hmac('sha256', $header . "." . $payload, self::$secret, true)
        );

        if ($signature !== $validSignature) return false;

        $decoded = json_decode(self::base64UrlDecode($payload), true);
        if (!$decoded) return false;
        if (isset($decoded["exp"]) && time() > (int)$decoded["exp"]) return false;

        return $decoded;
    }

    // ENCODE
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // DECODE
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
?>