<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\ModelFactory;
use App\Core\Sanitizer;
use App\Core\Validator;
use App\Models\AuditLog;
use App\Models\News;

final class NewsController extends Controller
{
    private News $news;
    private AuditLog $audit;

    public function __construct(?ModelFactory $models = null)
    {
        $models ??= ModelFactory::default();
        $this->news = $models->news();
        $this->audit = $models->audit();
    }

    public function index(): void
    {
        $this->render('news/index', ['title' => 'Noticias de Hardware y Software', 'news' => $this->news->published()]);
    }

    public function adminIndex(): void
    {
        $this->authorize('news.manage');
        $this->render('news/admin_index', ['title' => 'Administrar noticias', 'news' => $this->news->all()]);
    }

    public function create(): void
    {
        $this->authorize('news.manage');
        $this->render('news/form', ['title' => 'Nueva noticia', 'article' => null]);
    }

    public function store(): void
    {
        try {
            $this->authorize('news.manage');
            $this->csrf();
            $image = $this->uploadImage('imagen', 'equipment');
            $data = $this->data();
            $data['usuario_id'] = Auth::id();
            $data['imagen'] = $image['path'] ?? null;
            $id = $this->news->create($data);
            $this->audit->create(Auth::id(), 'NOTICIAS', 'CREAR', "Noticia #{$id} creada.");
            flash('success', 'Noticia publicada.');
            $this->redirect('news/admin');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'news/create');
        }
    }

    public function edit(): void
    {
        $this->authorize('news.manage');
        $article = $this->news->find((int) ($_GET['id'] ?? 0));
        if (!$article) {
            flash('error', 'Noticia no encontrada.');
            $this->redirect('news/admin');
        }
        $this->render('news/form', ['title' => 'Editar noticia', 'article' => $article]);
    }

    public function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        try {
            $this->authorize('news.manage');
            $this->csrf();
            $image = $this->uploadImage('imagen', 'equipment');
            $data = $this->data();
            $data['imagen'] = $image['path'] ?? null;
            $this->news->update($id, $data);
            $this->audit->create(Auth::id(), 'NOTICIAS', 'ACTUALIZAR', "Noticia #{$id} actualizada.");
            flash('success', 'Noticia actualizada.');
            $this->redirect('news/admin');
        } catch (\Throwable $exception) {
            $this->formError($exception, 'news/edit?id=' . $id);
        }
    }

    private function data(): array
    {
        return [
            'titulo' => Validator::required(Sanitizer::text($_POST['titulo'] ?? '', 180), 'Título'),
            'resumen' => Validator::required(Sanitizer::text($_POST['resumen'] ?? '', 300), 'Resumen'),
            'contenido' => Validator::required(Sanitizer::text($_POST['contenido'] ?? '', 3000), 'Contenido'),
            'publicada' => isset($_POST['publicada']) ? 1 : 0,
        ];
    }
}
