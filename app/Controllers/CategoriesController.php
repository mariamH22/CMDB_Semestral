<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\ModelFactory;
use App\Core\Sanitizer;
use App\Core\Validator;
use App\Models\AuditLog;
use App\Models\Category;

final class CategoriesController extends Controller
{
    private Category $categories;
    private AuditLog $audit;

    public function __construct(?ModelFactory $models = null)
    {
        $models ??= ModelFactory::default();
        $this->categories = $models->categories();
        $this->audit = $models->audit();
    }

    public function index(): void
    {
        $this->authorize('categories.view');
        $this->render('categories/index', ['title' => 'Categorías', 'categories' => $this->categories->all()]);
    }

    public function create(): void
    {
        $this->authorize('categories.manage');
        $this->render('categories/form', ['title' => 'Nueva categoría', 'category' => null]);
    }

    public function store(): void
    {
        try {
            $this->authorize('categories.manage');
            $this->csrf();
            $data = $this->data();
            $id = $this->categories->create($data);
            $this->audit->create(Auth::id(), 'CATEGORIAS', 'CREAR', "Categoría #{$id} creada.", 'INFO', [
                'entity' => 'categorias',
                'entity_id' => $id,
                'after' => $data,
            ]);
            flash('success', 'Categoría registrada.');
            $this->redirect('categories');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'categories/create');
        }
    }

    public function edit(): void
    {
        $this->authorize('categories.manage');
        $category = $this->categories->find((int) ($_GET['id'] ?? 0));
        if (!$category) {
            flash('error', 'Categoría no encontrada.');
            $this->redirect('categories');
        }
        $this->render('categories/form', ['title' => 'Editar categoría', 'category' => $category]);
    }

    public function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        try {
            $this->authorize('categories.manage');
            $this->csrf();
            $before = $this->categories->find($id);
            $data = $this->data();
            $this->categories->update($id, $data);
            $this->audit->create(Auth::id(), 'CATEGORIAS', 'ACTUALIZAR', "Categoría #{$id} actualizada.", 'INFO', [
                'entity' => 'categorias',
                'entity_id' => $id,
                'before' => $before ?? [],
                'after' => $data,
            ]);
            flash('success', 'Categoría actualizada.');
            $this->redirect('categories');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'categories/edit?id=' . $id);
        }
    }

    public function deactivate(): void
    {
        try {
            $this->authorize('categories.manage');
            $this->csrf();
            $id = (int) ($_POST['id'] ?? 0);
            $before = $this->categories->find($id);
            $this->categories->deactivate($id);
            $this->audit->create(Auth::id(), 'CATEGORIAS', 'BAJA_LOGICA', "Categoría #{$id} dada de baja.", 'INFO', [
                'entity' => 'categorias',
                'entity_id' => $id,
                'before' => $before ?? [],
                'after' => ['activo' => 0],
            ]);
            flash('success', 'Categoría dada de baja.');
            $this->redirect('categories');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'categories');
        }
    }

    private function data(): array
    {
        $type = $_POST['tipo'] ?? '';
        if (!in_array($type, ['HARDWARE', 'SOFTWARE'], true)) {
            throw new \RuntimeException('Seleccione un tipo de categoría válido.');
        }

        return [
            'nombre' => Validator::required(Sanitizer::text($_POST['nombre'] ?? '', 100), 'Nombre'),
            'tipo' => $type,
            'descripcion' => Sanitizer::text($_POST['descripcion'] ?? '', 255),
            'activo' => isset($_POST['activo']) ? 1 : 0,
        ];
    }
}
