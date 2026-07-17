<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Contracts\CanonicalPayloadInterface;
use App\Core\Contracts\DigitalSignatureInterface;
use App\Core\Contracts\EncryptionServiceInterface;
use App\Core\Contracts\IntegritySignerInterface;
use App\Core\Contracts\KeyManagementInterface;
use App\Core\Contracts\KeyStoreInterface;
use App\Core\Contracts\PasswordHasherInterface;
use App\Core\Contracts\SignatureVerifierInterface;
use App\Core\Security\AesGcmEncryptionService;
use App\Core\Security\AuthenticatedEncryptionService;
use App\Core\Security\AuditDataSanitizer;
use App\Core\Security\AuditTrailService;
use App\Core\Security\CanonicalPayloadSerializer;
use App\Core\Security\FileKeyStore;
use App\Core\Security\IntegritySignerService;
use App\Core\Security\PasswordHasher;
use App\Core\Security\RsaDigitalSignatureService;
use App\Core\Security\RsaKeyManagementService;
use App\Core\Security\RsaSignatureVerifierService;
use App\Core\Security\SignatureVerificationService;
use App\Core\Security\SignedPayloadFactory;

final class ServiceContainer
{
    private static array $services = [];

    public static function reset(): void
    {
        self::$services = [];
    }

    public static function canonicalPayload(): CanonicalPayloadInterface
    {
        return self::$services['canonicalPayload'] ??= new CanonicalPayloadSerializer();
    }

    public static function integritySigner(): IntegritySignerInterface
    {
        return self::$services['integritySigner'] ??= new IntegritySignerService(self::canonicalPayload());
    }

    public static function passwordHasher(): PasswordHasherInterface
    {
        return self::$services['passwordHasher'] ??= new PasswordHasher();
    }

    public static function encryption(): EncryptionServiceInterface
    {
        return self::$services['encryption'] ??= new AesGcmEncryptionService(
            Config::get('security.key_encryption_key')
        );
    }

    public static function licenseEncryption(): EncryptionServiceInterface
    {
        return self::$services['licenseEncryption'] ??= new AuthenticatedEncryptionService(
            Config::get('security.license_key_encryption_key')
        );
    }

    public static function licenseKeyProtector(): LicenseKeyProtector
    {
        return self::$services['licenseKeyProtector'] ??= new LicenseKeyProtector(self::licenseEncryption());
    }

    public static function keyStore(): KeyStoreInterface
    {
        return self::$services['keyStore'] ??= new FileKeyStore(
            Config::get('security.key_store_path')
        );
    }

    public static function keyManagement(): KeyManagementInterface
    {
        return self::$services['keyManagement'] ??= new RsaKeyManagementService(
            self::keyStore(),
            self::encryption()
        );
    }

    public static function digitalSignature(): DigitalSignatureInterface
    {
        return self::$services['digitalSignature'] ??= new RsaDigitalSignatureService();
    }

    public static function signatureVerifier(): SignatureVerifierInterface
    {
        return self::$services['signatureVerifier'] ??= new RsaSignatureVerifierService();
    }

    public static function signedPayloadFactory(): SignedPayloadFactory
    {
        return self::$services['signedPayloadFactory'] ??= new SignedPayloadFactory(self::canonicalPayload());
    }

    public static function signatureVerification(): SignatureVerificationService
    {
        return self::$services['signatureVerification'] ??= new SignatureVerificationService(self::signatureVerifier());
    }

    public static function auditDataSanitizer(): AuditDataSanitizer
    {
        return self::$services['auditDataSanitizer'] ??= new AuditDataSanitizer();
    }

    public static function auditTrail(): AuditTrailService
    {
        return self::$services['auditTrail'] ??= new AuditTrailService(
            self::canonicalPayload(),
            self::auditDataSanitizer()
        );
    }
}
