<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeAvatarRequest;
use App\Http\Requests\ChangeEmailRequest;
use App\Http\Requests\ChangeNameRequest;
use App\Http\Requests\DownloadFileRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UploadFileRequest;
use App\Models\File;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // UNSECURE
    //    public function show($id)
    //	{
    //		$user = User::findOrFail($id);
    //
    //        return view('auth.profile',compact('user'));
    //	}

    // SECURE
    public function profile()
    {
        if (! $user = Auth::user()) {
            return response()->json(['message' => 'Forbidden Operation'], 403);
        }

        return view('auth.profile', compact('user'));
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $user = Auth::user();

        abort_unless($user && (int) $user->getKey() === (int) $id, 403);

        $user->update($request->validated());

        return back()->with('message', 'User updated');
    }

    public function changeEmail(ChangeEmailRequest $request)
    {
        if (! $user = Auth::user()) {
            return response()->json(['message' => 'Forbidden Operation'], 403);
        }

        $user->email = $request->validated('email');
        $user->save();

        return back()->with('message', 'Changed successfully');
    }

    public function changeName(ChangeNameRequest $request)
    {
        if (! $user = Auth::user()) {
            return response()->json(['message' => 'Forbidden Operation'], 403);
        }

        $user->name = $request->validated('name');
        $user->save();

        return back()->with('message', 'Changed successfully');
    }

    public function changeImg(ChangeAvatarRequest $request)
    {
        if (! $user = Auth::user()) {
            return back()->with('message', 'Please Log In');
        }

        $newImage = $request->validated('avatar');
        // calculate hash

        // UNSECURE with md5
        //        $newImageHash = md5_file($newImage);

        // SECURE with sha56
        $newImageHash = hash_file('sha256', $newImage);

        // compare hash
        if (hash_equals((string) $user->avatar, $newImageHash)) {
            return redirect()->back()->with('message', 'Image not updated, same');
        }
        // Define the path to store the image
        $path = 'images/users/'.$user->id;

        Storage::disk('public')->deleteDirectory($path);

        // Store the image in the defined path
        $filePath = $newImage->storeAs($path, $newImageHash, 'public');

        $user->avatar = $newImageHash;
        $user->save();

        return redirect()->back()->with('message', 'Image updated');
    }

    public function download(DownloadFileRequest $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Forbidden Operation'], 403);
        }

        $filename = basename($request->validated('filename'));

        if (in_array($filename, ['privacy.pdf', 'cookie-policy.pdf'], true)) {
            return Storage::disk('local')->download($filename);
        }

        $fileRecord = File::query()
            ->where('name', $filename)
            ->where('user_id', $user->getKey())
            ->firstOrFail();

        $path = "docs/users/{$user->getKey()}/{$fileRecord->name}";

        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->download($path, $fileRecord->name);
    }

    public function upload(UploadFileRequest $request)
    {

        if (! $user = Auth::user()) {
            return back()->with('message', 'Please Log In');
        }

        $file = $request->validated('file');

        // UNSECURE
        //
        //        $filename = $file->getClientOriginalName();
        //
        //        $file->move($path, $filename);
        //
        //        File::create([
        //            'name' => $filename,
        //            'user_id' => $user->id,
        //
        //        ]);

        // SECURE

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];

        $extension = strtolower($file->getClientOriginalExtension());

        $mimeType = $file->getMimeType();

        if (! in_array($extension, $allowedExtensions, true)
            || ! in_array($mimeType, $allowedMimeTypes, true)) {
            return back()->withErrors('File type not allowed');
        }

        $filename = bin2hex(random_bytes(16)).'.'.$extension;

        $file->storeAs("docs/users/{$user->id}", $filename, 'public');

        File::create([
            'name' => $filename,
            'user_id' => $user->id,
        ]);

        return back()->withMessage('Upload successful');
    }
}
