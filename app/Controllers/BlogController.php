<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Database;
use App\Helpers\Session;

class BlogController
{
    public function index(): void
    {
        $posts = Database::query(
            "SELECT bp.*, CONCAT(u.first_name, ' ', u.last_name) AS author_name
             FROM blog_posts bp
             LEFT JOIN users u ON u.id = bp.author_id
             WHERE bp.status = 'published'
             ORDER BY bp.published_at DESC, bp.created_at DESC"
        )->fetchAll();

        $title = 'Blog ProsDevis';
        $metaDescription = 'Conseils devis, facturation, TVA, relances clients et digitalisation pour artisans, freelances et PME.';
        $canonical = '/blog';

        ob_start();
        require __DIR__ . '/../Views/blog/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/public.php';
    }

    public function show(string $slug): void
    {
        $post = Database::query(
            "SELECT bp.*, CONCAT(u.first_name, ' ', u.last_name) AS author_name
             FROM blog_posts bp
             LEFT JOIN users u ON u.id = bp.author_id
             WHERE bp.slug = ? AND bp.status = 'published'
             LIMIT 1",
            [$slug]
        )->fetch();

        if (!$post) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }

        $related = Database::query(
            "SELECT id, slug, title, excerpt, published_at
             FROM blog_posts
             WHERE status = 'published' AND slug != ?
             ORDER BY published_at DESC
             LIMIT 3",
            [$slug]
        )->fetchAll();

        $title = $post['meta_title'] ?: $post['title'];
        $metaDescription = $post['meta_desc'] ?: ($post['excerpt'] ?: 'Article ProsDevis');
        $canonical = '/blog/' . $post['slug'];
        $ogImage = $post['og_image'] ?: $post['cover_image'];

        ob_start();
        require __DIR__ . '/../Views/blog/show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/public.php';
    }

    public function adminIndex(): void
    {
        Auth::require();
        $posts = Database::query(
            "SELECT bp.*, CONCAT(u.first_name, ' ', u.last_name) AS author_name
             FROM blog_posts bp
             LEFT JOIN users u ON u.id = bp.author_id
             ORDER BY bp.created_at DESC"
        )->fetchAll();

        $title = 'Administration du blog';
        ob_start();
        require __DIR__ . '/../Views/blog/admin_index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function create(): void
    {
        Auth::require();
        $post = null;
        $title = 'Nouvel article';
        ob_start();
        require __DIR__ . '/../Views/blog/form.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function store(): void
    {
        Auth::require();
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) { http_response_code(403); exit; }

        $title = trim($_POST['title'] ?? '');
        $slug  = trim($_POST['slug'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $status  = in_array($_POST['status'] ?? 'draft', ['draft', 'published'], true) ? $_POST['status'] : 'draft';
        $metaTitle = trim($_POST['meta_title'] ?? '');
        $metaDesc  = trim($_POST['meta_desc'] ?? '');
        $coverImage = trim($_POST['cover_image'] ?? '');
        $ogImage    = trim($_POST['og_image'] ?? '');

        if ($title === '' || $slug === '' || $content === '') {
            Session::flash('error', 'Titre, slug et contenu sont obligatoires.');
            header('Location: /admin/blog/create');
            exit;
        }

        Database::query(
            'INSERT INTO blog_posts (author_id, slug, title, excerpt, content, cover_image, status, meta_title, meta_desc, og_image, published_at, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [
                Auth::id(),
                $slug,
                $title,
                $excerpt,
                $content,
                $coverImage ?: null,
                $status,
                $metaTitle ?: null,
                $metaDesc ?: null,
                $ogImage ?: null,
                $status === 'published' ? date('Y-m-d H:i:s') : null,
            ]
        );

        Session::flash('success', 'Article créé.');
        header('Location: /admin/blog');
        exit;
    }

    public function edit(int $id): void
    {
        Auth::require();
        $post = Database::query('SELECT * FROM blog_posts WHERE id = ? LIMIT 1', [$id])->fetch();
        if (!$post) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }

        $title = 'Modifier l\'article';
        ob_start();
        require __DIR__ . '/../Views/blog/form.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function update(int $id): void
    {
        Auth::require();
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) { http_response_code(403); exit; }

        $post = Database::query('SELECT * FROM blog_posts WHERE id = ? LIMIT 1', [$id])->fetch();
        if (!$post) {
            http_response_code(404);
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $slug  = trim($_POST['slug'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $status  = in_array($_POST['status'] ?? 'draft', ['draft', 'published'], true) ? $_POST['status'] : 'draft';
        $metaTitle = trim($_POST['meta_title'] ?? '');
        $metaDesc  = trim($_POST['meta_desc'] ?? '');
        $coverImage = trim($_POST['cover_image'] ?? '');
        $ogImage    = trim($_POST['og_image'] ?? '');
        $publishedAt = $post['published_at'];
        if ($status === 'published' && !$publishedAt) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        Database::query(
            'UPDATE blog_posts
             SET slug = ?, title = ?, excerpt = ?, content = ?, cover_image = ?, status = ?, meta_title = ?, meta_desc = ?, og_image = ?, published_at = ?, updated_at = NOW()
             WHERE id = ?',
            [$slug, $title, $excerpt, $content, $coverImage ?: null, $status, $metaTitle ?: null, $metaDesc ?: null, $ogImage ?: null, $publishedAt, $id]
        );

        Session::flash('success', 'Article mis à jour.');
        header('Location: /admin/blog');
        exit;
    }

    public function sitemap(): void
    {
        $posts = Database::query(
            "SELECT slug, updated_at, published_at FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC"
        )->fetchAll();

        header('Content-Type: application/xml; charset=UTF-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        echo '<url><loc>' . htmlspecialchars($this->absoluteUrl('/')) . '</loc></url>';
        echo '<url><loc>' . htmlspecialchars($this->absoluteUrl('/blog')) . '</loc></url>';
        foreach ($posts as $post) {
            echo '<url>';
            echo '<loc>' . htmlspecialchars($this->absoluteUrl('/blog/' . $post['slug'])) . '</loc>';
            if (!empty($post['updated_at'])) {
                echo '<lastmod>' . date('c', strtotime($post['updated_at'])) . '</lastmod>';
            }
            echo '</url>';
        }
        echo '</urlset>';
    }

    private function absoluteUrl(string $path): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . $path;
    }
}
