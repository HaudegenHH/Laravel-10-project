<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAvatarRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Str;

use OpenAI\Laravel\Facades\OpenAI;

class AvatarController extends Controller
{
    public function update(UpdateAvatarRequest $request) {
        // validation behind the scenes in UpdateAvatarRequest
        // $request->validate([
        //     'avatar' => 'required|image'
        // ]);
        
        // 2nd param of store() is the "disk" that you can define
        // $path = $request->file('avatar')->store('avatars', 'public');

        // another (more readable) option to upload the image with the Storage facade:
        $path = Storage::disk('public')->put('avatars', $request->file('avatar'));
        // dd($path);

        // deleting already existing avatars
        if ($oldAvatar = $request->user()->avatar) {
            Storage::disk('public')->delete($oldAvatar);
        }
        
        // get authenticated user in laravel
        $user = auth()->user();

        $user->update(['avatar' => $path]);

        // dd(auth()->user());

        // return back()->with('message', 'Avatar is changed');
        return redirect(route('profile.edit'))->with('message', 'Avatar is updated');
    }

    public function generate() {
        
        $result = OpenAI::images()->create([
            "prompt" =>"create avatar for user with name " . auth()->user()->name,
            "n"      => 1,
            "size"   => "256x256",
        ]);
          
        // dd($result->data[0]->url);
        $url = $result->data[0]->url;

        // a random pic from the internet hardcoded url for testing 
        // $content = file_get_contents("https://upload.wikimedia.org/wikipedia/en/7/70/MacOS_Ventura_Desktop.png");
        
        // dynamically changed url
        $content = file_get_contents($url);

        $filename = Str::random(25);
        
        // like in update() put the image into the public/avatars folder 
        Storage::disk('public')->put("avatars/$filename.jpg", $content);

        // and update the avatar property on the authenticated user
        // (..to then use {{"storage/$user->avatar"}} in the blade template to point 
        // to the location created with the sym-link)
        auth()->user()->update(['avatar' => "avatars/$filename.jpg"]);
                
        return redirect(route('profile.edit'))->with('message', 'Avatar is updated');

    }
}
