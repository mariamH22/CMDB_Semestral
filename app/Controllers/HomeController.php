<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\ModelFactory;
use App\Models\News;

final class HomeController extends Controller
{
    private News $news;

    public function __construct(?ModelFactory $models = null)
    {
        $models ??= ModelFactory::default();
        $this->news = $models->news();
    }

    public function index(): void
    {
        $this->render('home/index', [
            'title' => 'CMDB Integral - Inventario de Hardware y Software',
            'news' => $this->news->published(),
        ]);
    }
}
