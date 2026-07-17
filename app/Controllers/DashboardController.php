<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\ModelFactory;
use App\Models\InventoryItem;
use App\Models\NeedRequest;
use App\Models\Assignment;
use App\Models\Category;
use App\Models\ReturnReview;

final class DashboardController extends Controller
{
    private InventoryItem $inventory;
    private Assignment $assignments;
    private NeedRequest $needs;
    private Category $categories;
    private ReturnReview $returns;

    public function __construct(?ModelFactory $models = null)
    {
        $models ??= ModelFactory::default();
        $this->inventory = $models->inventory();
        $this->assignments = $models->assignments();
        $this->needs = $models->needs();
        $this->categories = $models->categories();
        $this->returns = $models->returns();
    }

    public function index(): void
    {
        $this->authorize('dashboard.view');

        if (Auth::isCollaborator()) {
            $this->redirect('portal');
        }

        $filterOptions = $this->inventory->dashboardFilterOptions();
        $categories = $this->categories->all(true);
        $filters = $this->dashboardFilters($_GET, $filterOptions, $categories);

        $this->render('dashboard/index', [
            'title' => 'Panel de control',
            'counts' => $this->inventory->dashboardCounts($filters),
            'nearDepreciation' => $this->inventory->nearDepreciation(90, $filters),
            'activeAssignments' => $this->assignments->all(true, $filters),
            'needs' => $this->needs->all(null, $filters),
            'pendingReturns' => $this->returns->pending($filters),
            'licenseSummary' => $this->inventory->licenseSummary($filters),
            'dashboardFilters' => $filters,
            'dashboardFilterOptions' => $filterOptions,
            'categories' => $categories,
            'generatedAt' => new \DateTimeImmutable('now'),
        ]);
    }

    private function dashboardFilters(array $input, array $filterOptions, array $categories): array
    {
        $types = $this->optionValues($filterOptions['tipos'] ?? []);
        $states = $this->optionValues($filterOptions['estados'] ?? []);
        $locations = $this->optionValues($filterOptions['ubicaciones'] ?? []);
        $categoryIds = array_map(static fn (array $category): int => (int) $category['id'], $categories);

        $filters = [
            'tipo' => '',
            'categoria_id' => 0,
            'ubicacion' => '',
            'estado' => '',
        ];

        $type = strtoupper(trim((string) ($input['tipo'] ?? '')));
        if ($type !== '' && in_array($type, $types, true)) {
            $filters['tipo'] = $type;
        }

        $categoryId = (int) ($input['categoria_id'] ?? 0);
        if ($categoryId > 0 && in_array($categoryId, $categoryIds, true)) {
            $filters['categoria_id'] = $categoryId;
        }

        $location = trim((string) ($input['ubicacion'] ?? ''));
        if ($location !== '' && in_array($location, $locations, true)) {
            $filters['ubicacion'] = $location;
        }

        $state = strtoupper(trim((string) ($input['estado'] ?? '')));
        if ($state !== '' && in_array($state, $states, true)) {
            $filters['estado'] = $state;
        }

        return $filters;
    }

    private function optionValues(array $rows): array
    {
        return array_values(array_filter(
            array_map(static fn (array $row): string => (string) ($row['value'] ?? ''), $rows),
            static fn (string $value): bool => $value !== ''
        ));
    }
}
