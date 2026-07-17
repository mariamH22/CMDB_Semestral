<?php
declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Contracts\EncryptionServiceInterface;
use App\Core\Contracts\KeyManagementInterface;
use App\Core\Contracts\KeyStoreInterface;

final class RsaKeyManagementService implements KeyManagementInterface
{
    public function __construct(
        private KeyStoreInterface $keyStore,
        private EncryptionServiceInterface $encryption
    ) {
    }

    public function isConfigured(): bool
    {
        return $this->keyStore->isConfigured()
            && $this->encryption->isConfigured()
            && function_exists('openssl_pkey_new')
            && function_exists('openssl_pkey_export');
    }

    public function generateForUser(int $userId, string $name = 'Llave RSA de usuario', int $bits = 3072): ?array
    {
        if (!$this->isConfigured() || $userId < 1 || $bits < 2048) {
            return null;
        }

        // Se genera una pareja RSA por usuario para soportar no repudio en acciones sensibles.
        $key = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => $bits,
        ]);

        if ($key === false) {
            return null;
        }

        $privateKey = '';
        if (!openssl_pkey_export($key, $privateKey)) {
            return null;
        }

        $details = openssl_pkey_get_details($key);
        if (!is_array($details) || empty($details['key'])) {
            return null;
        }

        $publicKey = (string) $details['key'];
        $fingerprint = hash('sha256', $publicKey);
        // La llave privada nunca se guarda en claro ni dentro de public/.
        $encryptedPrivateKey = $this->encryption->encrypt($privateKey);
        if ($encryptedPrivateKey === null) {
            return null;
        }

        $reference = $this->keyStore->storeEncryptedPrivateKey($userId, $fingerprint, $encryptedPrivateKey);
        if ($reference === null) {
            return null;
        }

        return [
            'usuario_id' => $userId,
            'nombre' => $name,
            'public_key' => $publicKey,
            'key_store_reference' => $reference,
            'fingerprint' => $fingerprint,
            'estado' => 'ACTIVA',
            'algoritmo' => 'RSA-SHA256',
            'bits' => $bits,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    public function loadPrivateKey(string $reference): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $encrypted = $this->keyStore->readEncryptedPrivateKey($reference);
        if ($encrypted === null) {
            return null;
        }

        return $this->encryption->decrypt($encrypted);
    }
}
