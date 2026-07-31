<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Article;
use App\Services\FinancialDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard()
	{
		return view("admin.dashboard");
	}

    public function articles()
    {
        $users = User::latest()->get();
        $articles = Article::latest()->get();
        return view('admin.articles', compact('articles'));
    }

    public function users()
    {
        $users = User::all();
        return view('admin.users', compact('users'));
    }
    
    public function toggleArticleStatus($id) {
        // SECURE
        // if(!Auth::user()->isAdmin()){
        //     return back()->withMessage("Operation not permitted");
        // }
        
        $article = Article::find($id);
        $article->published = !$article->published;
        $article->save();
        return back();
    }

	public function toggleUsersAdmin($id)
	{
        // SECURE
        // if(!Auth::user()->isAdmin()){
        //     return back()->withMessage("Operation not permitted");
        // }
        // UNSECURE
		$user = User::find($id);
        $user->is_admin = !$user->is_admin;
        $user->save();
        return back();
	}

    // UNSECURE - VULNERABILE A SSRF: fetch di URL arbitrari passati dall'utente
    public function fetchExternalData(Request $request, FinancialDataService $financialDataService)
    {
        // UNSECURE: nessuna validazione dell'URL fornito dall'utente
        $url = $request->get('url');
        $result = $financialDataService->fetchExternalData($url);
        return response()->json($result);

        // SECURE
        // $url = $request->get('url');
        // $parsed = parse_url($url);
        // if (!$parsed || strtolower($parsed['scheme'] ?? '') !== 'https') {
        //     return response()->json(['status' => 'error', 'error' => 'Only HTTPS URLs are allowed'], 422);
        // }
        // $ip = gethostbyname($parsed['host'] ?? '');
        // if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        //     return response()->json(['status' => 'error', 'error' => 'Private/localhost addresses are not allowed'], 422);
        // }
        // return response()->json($financialDataService->fetchExternalData($url));
    }
}