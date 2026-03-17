<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use App\Models\DesignerPortfolio;
use App\Models\PortfolioImage;

class DesignerPortfolioController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Portfolio List
    |--------------------------------------------------------------------------
    */

    public function index()
    {

        $portfolios = DesignerPortfolio::where('designer_id', Auth::id())
            ->with(['images:id,portfolio_id,image_path'])
            ->latest()
            ->paginate(12);

        return view('designer.portfolio.index', compact('portfolios'));

    }



    /*
    |--------------------------------------------------------------------------
    | Create Portfolio
    |--------------------------------------------------------------------------
    */

    public function create()
    {

        return view('designer.portfolio.create');

    }



    /*
    |--------------------------------------------------------------------------
    | Store Portfolio
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);


        DB::transaction(function () use ($request, $data) {

            $portfolio = DesignerPortfolio::create([
                'designer_id' => Auth::id(),
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'] ?? null,
            ]);


            if ($request->hasFile('images')) {

                foreach ($request->file('images') as $image) {

                    $path = $image->store('portfolio/'.Auth::id(), 'public');

                    PortfolioImage::create([
                        'portfolio_id' => $portfolio->id,
                        'image_path' => $path
                    ]);

                }

            }

        });


        return redirect()
            ->route('designer.portfolio.index')
            ->with('success', 'Portfolio created successfully.');

    }



    /*
    |--------------------------------------------------------------------------
    | Edit Portfolio
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {

        $portfolio = DesignerPortfolio::where('designer_id', Auth::id())
            ->with('images')
            ->findOrFail($id);

        return view('designer.portfolio.edit', compact('portfolio'));

    }



    /*
    |--------------------------------------------------------------------------
    | Update Portfolio
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {

        $portfolio = DesignerPortfolio::where('designer_id', Auth::id())
            ->with('images')
            ->findOrFail($id);


        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);


        DB::transaction(function () use ($request, $portfolio, $data) {

            $portfolio->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'] ?? null,
            ]);


            if ($request->hasFile('images')) {

                foreach ($request->file('images') as $image) {

                    $path = $image->store('portfolio/'.Auth::id(), 'public');

                    PortfolioImage::create([
                        'portfolio_id' => $portfolio->id,
                        'image_path' => $path
                    ]);

                }

            }

        });


        return redirect()
            ->route('designer.portfolio.index')
            ->with('success', 'Portfolio updated successfully.');

    }



    /*
    |--------------------------------------------------------------------------
    | Delete Portfolio
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {

        $portfolio = DesignerPortfolio::where('designer_id', Auth::id())
            ->with('images')
            ->findOrFail($id);


        DB::transaction(function () use ($portfolio) {

            foreach ($portfolio->images as $image) {

                Storage::disk('public')->delete($image->image_path);

                $image->delete();

            }

            $portfolio->delete();

        });


        return redirect()
            ->route('designer.portfolio.index')
            ->with('success', 'Portfolio deleted successfully.');

    }

}
