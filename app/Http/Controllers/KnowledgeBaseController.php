<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class KnowledgeBaseController extends Controller
{
    /**
     * Display the Knowledge Base home/index.
     */
    public function index()
    {
        return Inertia::render('KnowledgeBase/Index');
    }

    /**
     * Display a specific Knowledge Base article.
     */
    public function show($slug)
    {
        // For now, we only have one article
        if ($slug === 'how-to-create-a-ticket') {
            return Inertia::render('KnowledgeBase/Articles/CreateTicket');
        }

        abort(404);
    }
}
