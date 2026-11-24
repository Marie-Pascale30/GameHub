<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

/** Pages statiques. */
final class PageController extends Controller
{
    public function legal(): void
    {
        $this->render('legal', ['title' => 'Mentions légales & confidentialité']);
    }
}
