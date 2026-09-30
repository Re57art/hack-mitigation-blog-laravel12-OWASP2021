<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleRequest;
use App\Http\Requests\SearchRequest;
use App\Models\Article;
use App\Services\HtmlFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ArticleController extends Controller
{
    public function index(Request $request, HtmlFilterService $htmlFilterService)
    {
        // UNSECURE
        // $articles = Article::latest()->where('published', true)->take(6)->get();

        // SECURE
        $articles = Article::latest()->where('published', true)->take(6)->get();
        $articles = $htmlFilterService->filterHtmlCollectionByField($articles, 'content');
        if ($request->wantsJson()) {
            return response()->json($articles);
        }

        return view('articles.index', compact('articles'));
    }

    public function search(SearchRequest $request)
    {

        // UNSECURE
        //        $articles = Article::whereRaw("title like '%{$request->search}%'")->get();

        // SECURE
        $articles = Article::where('title', 'LIKE', "%{$request->search}%")
            ->orWhere('content', 'LIKE', "%{$request->search}%")
            ->get();

        return view('articles.index', compact('articles'));
    }

    // UNSECURE
    //    public function show(Article $article, Request $request)
    //    {
    //        if ($request->wantsJson()) {
    //            return response()->json($article);
    //        }
    //
    //        return view('articles.show', compact('article'));
    //    }

    // SECURE
    public function show(Article $article, Request $request, HtmlFilterService $htmlFilterService)
    {
        $article->content = $htmlFilterService->filterHtml($article->content);
        if ($request->wantsJson()) {
            return response()->json($article);
        }

        return view('articles.show', compact('article'));
    }

    public function store(ArticleRequest $request, HtmlFilterService $htmlFilterService)
    {
        // UNSECURE
        // $articleData = $request->all();

        // SECURE
        $articleData = $request->validated();
        $articleData['content'] = $htmlFilterService->filterHtml($articleData['content']);

        $articleData['user_id'] = Auth::id();

        $article = Article::create($articleData);

        if ($request->wantsJson()) {
            return response()->json($article, 201);
        }

        return redirect()->route('articles.index');
    }

    public function create()
    {
        return view('articles.create');
    }

    public function edit(Article $article)
    {
        Gate::authorize('update', $article);

        return view('articles.edit', compact('article'));
    }

    public function update(ArticleRequest $request, Article $article, HtmlFilterService $htmlFilterService)
    {
        if ($request->user()->cannot('update', $article)) {
            return redirect()->route('articles.index')->with('message', 'Unauthorized');
        }

        // UNSECURE
        // $articleData = $request->all();

        // SECURE
        $articleData = $request->validated();
        $articleData['content'] = $htmlFilterService->filterHtml($articleData['content']);

        $article->update($articleData);

        if ($request->wantsJson()) {
            return response()->json($article, 200);
        }

        return redirect()->route('articles.show', $article);
    }

    public function destroy(Article $article, Request $request)
    {
        // SECURE
        Gate::authorize('update', $article);

        $article->delete();

        if ($request->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('articles.index')->with('message', 'Article deleted successfully');
    }
}
