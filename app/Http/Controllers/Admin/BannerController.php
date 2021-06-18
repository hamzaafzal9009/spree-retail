<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BannerStoreRequest;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::all();


        return view('admin.dashboard.banner.index',compact('banners'));
    }

    public function create()
    {

        return view('admin.dashboard.banner.create');
    }

    public function store(BannerStoreRequest $request)
    {
        $fileNameToStore = Helper::fileStore($request->user(), $request['image'],'banner');

        $banner = Banner::create([
            'type' => $request['type'] == 1 ? 'Header' : 'Footer',
            'url' => $request['url'],
            'image' => $fileNameToStore,
        ]);

        return back()->with('popup_success','Banner created successfully');
    }

    public function edit($slug)
    {
        $product = Product::where('slug',$slug)->first();

        $parents = Category::where('main',$product->main)->get();

        $categories = Category::all();

        $cat = $product->categories->pluck('id')->toArray();

        return view('admin.dashboard.product.edit',compact('product','categories','cat','parents'));
    }
}
