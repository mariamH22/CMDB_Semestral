<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\Assignment;
use App\Models\AuditLog;
use App\Models\AuditTrailVerifier;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Collaborator;
use App\Models\DigitalSignatureRecord;
use App\Models\InventoryItem;
use App\Models\InventoryQr;
use App\Models\InventoryStateHistory;
use App\Models\LicenseAssignment;
use App\Models\NeedRequest;
use App\Models\News;
use App\Models\PasswordReset;
use App\Models\ReturnReview;
use App\Models\RsaKey;
use App\Models\User;

final class ModelFactory
{
    public function __construct(private Database $db)
    {
    }

    public static function default(): self
    {
        return new self(Database::instance());
    }

    public function db(): Database
    {
        return $this->db;
    }

    public function assignments(): Assignment
    {
        return new Assignment($this->db);
    }

    public function audit(): AuditLog
    {
        return new AuditLog($this->db);
    }

    public function auditTrailVerifier(): AuditTrailVerifier
    {
        return new AuditTrailVerifier($this->db);
    }

    public function budgets(): Budget
    {
        return new Budget($this->db);
    }

    public function categories(): Category
    {
        return new Category($this->db);
    }

    public function collaborators(): Collaborator
    {
        return new Collaborator($this->db);
    }

    public function digitalSignatures(): DigitalSignatureRecord
    {
        return new DigitalSignatureRecord($this->db);
    }

    public function inventory(): InventoryItem
    {
        return new InventoryItem($this->db);
    }

    public function inventoryQr(): InventoryQr
    {
        return new InventoryQr($this->db);
    }

    public function inventoryStateHistory(): InventoryStateHistory
    {
        return new InventoryStateHistory($this->db);
    }

    public function licenseAssignments(): LicenseAssignment
    {
        return new LicenseAssignment($this->db);
    }

    public function needs(): NeedRequest
    {
        return new NeedRequest($this->db);
    }

    public function news(): News
    {
        return new News($this->db);
    }

    public function passwordResets(): PasswordReset
    {
        return new PasswordReset($this->db);
    }

    public function returns(): ReturnReview
    {
        return new ReturnReview($this->db);
    }

    public function rsaKeys(): RsaKey
    {
        return new RsaKey($this->db);
    }

    public function users(): User
    {
        return new User($this->db);
    }

    public function reports(): ReportService
    {
        return new ReportService(
            $this->inventory(),
            $this->assignments(),
            $this->categories(),
            $this->licenseAssignments(),
            $this->needs(),
            $this->returns(),
            $this->inventoryStateHistory()
        );
    }
}
