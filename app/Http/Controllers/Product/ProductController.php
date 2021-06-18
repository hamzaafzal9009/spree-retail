<?php

namespace App\Http\Controllers\Product;

use App\Helpers\Helper;
use App\Helpers\Recommendation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreRequest  $request
     */
    public function store(StoreRequest $request)
    {
        $fileNameToStore = Helper::fileStore($request->user(), $request['thumbnail'],'product');

        $slug = Helper::createSlug(Product::class, $request['name']);

        $product = Product::create([
            'user_id' => auth()->user()->id,
            'name' => $request['name'],
            'featured' => $request['featured'],
            'main' => $request['main_category'],
            'slug' => $slug,
            'quantity' => $request['quantity'],
            'remaining' => $request['quantity'],
            'price' => $request['price'],
            'description' => $request['description'],
            'thumbnail' => $fileNameToStore,
            'status' => 'Active',
            'length' => $request['length'],
            'width' => $request['width'],
            'height' => $request['height'],
            'weight' => $request['weight'],
        ]);

        $recom = new Recommendation;

        $recom->updateSimilarityMatrix($product);

        $product->categories()->attach($request->category);

        return redirect()->route('admin.dashboard.product.index')->with('popup_success','Product has been created');

    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($slug, $category = null)
    {
        $product = Product::where('slug',$slug)->first();

        $recommendations = new Recommendation;

        $recommendations = $recommendations->recommend($product);

        $category = Category::where('slug',$category)->first();


        return view('product.show',compact('product','category','recommendations'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     */
    public function edit($slug)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     */
    public function update(UpdateRequest $request, $slug)
    {
        $product = Product::where('slug',$slug)->first();

        //Check if user has updated the thumbnail
        if ($request->has('thumbnail')){
            $fileNameToStore = Helper::fileStore($request->user(), $request['thumbnail'],'product');
        }else{
            $fileNameToStore = $product->thumbnail;
        }

        //Check if user has changed the name
        if ($request['name'] != $product->name){
            $slug = Helper::createSlug(Product::class, $request['name']);
        }else{
            $slug = $product->slug;
        }

        //Update Product

        $product->name = $request['name'];
        $product->slug = $slug;
        $product->featured = $request['featured'];
        $product->main = $request['main_category'];
        $product->quantity = $request['quantity'];
        $product->price = $request['price'];
        $product->description = $request['description'];
        $product->thumbnail = $fileNameToStore;
        $product->length = $request['length'];
        $product->width = $request['width'];
        $product->height = $request['height'];
        $product->weight = $request['weight'];

        $product->save();

        $product->categories()->sync($request->category);

        $recom = new Recommendation;

        $recom->updateSimilarityMatrix($product);

        return redirect()->route('admin.dashboard.product.index')->with('popup_success','Product has been updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     */
    public function destroy($slug)
    {
        $product = Product::where('slug',$slug)->first();

        //Deleting Product
        $product->categories()->detach();

        //Deleting Blog Thumbnail
        Storage::delete('public/product/'.$product->thumbnail);

        $product->delete();


        return redirect()->route('admin.dashboard.product.index')->with('popup_success','Product has been deleted');
    }
}
