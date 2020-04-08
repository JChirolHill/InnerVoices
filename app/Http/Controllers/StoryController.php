<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Auth;
use App\Story;
use App\StoryArchetype;
use App\Character;
use App\Theme;

class StoryController extends Controller
{
    public function index() {
      // get all characters for this user
      $characters = Character::with('archetype')->where('user_id', '=', Auth::id())->get();

      // get all themes
      $themes = Theme::all();

      // get all archetypes
      $archetypes = StoryArchetype::all();

      return view('story.add', [
        'characters' => $characters,
        'themes' => $themes,
        'archetypes' => $archetypes
      ]);
    }

    public function store(Request $request) {
      // validation
      $request->validate([
        'title' => ['required',
          Rule::unique('stories')->where(function ($query) {
            return $query->where('user_id', Auth::id());
          })
        ],
        'descr' => 'required',
        'archetype' => 'required|exists:story_archetypes,id',
        'theme' => 'exists:themes,id'
      ]);

      // TODO: validate characters to make sure exist
      // 'characters' => 'exists:characters,id'

      // store in database
      $story = new Story();
      $story->title = $request->title;
      $story->user_id = Auth::id();
      $story->theme_id = $request->theme;
      $story->archetype_id = $request->archetype;
      $story->descr = $request->descr;
      $story->save();

      // add many to many relationships for characters
      // Story::find($story->id)

      // redirect to home with success
      return redirect()
        ->route('home')
        ->with('success', "Successfully created story {$story->title}");
    }

    public function archetypes() {
      $archetypes = StoryArchetype::all();
      return response()->json($archetypes);
    }
}
