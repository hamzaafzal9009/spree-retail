<?php

namespace App\Http\Controllers\Vendor;

use App\Helpers\Helper;
use App\Helpers\Recommendation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\VendorStripe;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use function PHPUnit\Framework\isNull;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('user_id',auth()->user()->id)->orderBy('created_at','desc')->get();


        return view('vendor.dashboard.product.index',compact('products'));
    }

    public function create()
    {
        $VendorStripe = VendorStripe::where( ['vendor_id' => auth()->user()->id] )->first();

        if ($VendorStripe == null){
            return back()->with('popup_error','Please attach your bank account first');
        }
        $categories = Category::all();

        return view('vendor.dashboard.product.create',compact('categories'));
    }

    public function edit($slug)
    {
        $VendorStripe = VendorStripe::where( ['vendor_id' => auth()->user()->id] )->first();
        if ($VendorStripe == null){
            return back()->with('popup_error','Please attach your bank account first');
        }

        $product = Product::where('slug',$slug)->where('user_id',auth()->user()->id)->first();

        if ($product == null){
            return back()->with('popup_error','Product does not exists');
        }

        if ($product->status != config('enums.product_status.active')){
            return back()->with('popup_error','Product cannot be edited because it is not active');
        }

        $parents = Category::where('main',$product->main)->get();

        $categories = Category::all();

        $cat = $product->categories->pluck('id')->toArray();


        return view('vendor.dashboard.product.edit',compact('product','categories','cat','parents'));
    }

    public function store(StoreRequest $request)
    {
        $VendorStripe = VendorStripe::where( ['vendor_id' => auth()->user()->id] )->first();
        if ($VendorStripe == null){
            return back()->with('popup_error','Please attach your bank account first');
        }

        $fileNameToStore = Helper::fileStore($request->user(), $request['thumbnail'],'product');

        $slug = Helper::createSlug(Product::class, $request['name']);

        $product = Product::create([
            'user_id' => auth()->user()->id,
            'name' => $request['name'],
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

        return redirect()->route('vendor.dashboard.product.index')->with('popup_success','Product has been created');

    }

    public function update(UpdateRequest $request, $slug)
    {
        $VendorStripe = VendorStripe::where( ['vendor_id' => auth()->user()->id] )->first();
        if ($VendorStripe == null){
            return back()->with('popup_error','Please attach your bank account first');
        }

        $product = Product::where('slug',$slug)->where('user_id',auth()->user()->id)->first();

        if ($product == null){
            return back()->with('popup_error','Product does not exists');
        }

        if ($product->status != config('enums.product_status.active')){
            return back()->with('popup_error','Product cannot be edited because it is not active');
        }

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

        return redirect()->route('vendor.dashboard.product.index')->with('popup_success','Product has been updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     */
    public function destroy($slug)
    {
        $product = Product::where('slug',$slug)->where('user_id',auth()->user()->id)->first();

        if ($product == null){
            return back()->with('popup_error','Product does not exists');
        }
        $inactive_d = $product->inactive;

        $t_d = Carbon::now()->toDateString();
        if ($inactive_d != null)
        {
            $date_w = Carbon::createFromDate($inactive_d);
            $now = Carbon::now();
            $diff = $date_w->diffInDays($now);
            if ($diff < 40){
                $diff = 40 - $diff;
                return redirect()->route('vendor.dashboard.product.index')->with('popup_error','Product has been set as inactive and will be deleted after 40 days. Until then it will not be shown on our site. '.$diff.' day(s) remaining');
            }
        }else{
            $product->status = config('enums.product_status.inactive');
            $product->inactive = $t_d;
            $product->save();
            return redirect()->route('vendor.dashboard.product.index')->with('popup_error','Product has been set as inactive and will be deleted after 40 days. Until then it will not be shown on our site');
        }
        //Deleting Product
        $product->categories()->detach();


        $product->delete();


        return redirect()->route('vendor.dashboard.product.index')->with('popup_success','Product has been deleted');
    }

    public function active($slug)
    {
        $product = Product::where('slug',$slug)->where('user_id',auth()->user()->id)->first();

        if ($product == null){
            return back()->with('popup_error','Product does not exists');
        }

        $product->status = config('enums.product_status.active');
        $product->inactive = null;
        $product->save();

        return back()->with('popup_success','Product has been activated');
    }

    public function parent(Request $request)
    {
        $categories = Category::where('main',$request->name)->get()->pluck('name');

        return response()->json([
            'array'   => json_encode($categories),
            'error'     => 0
        ]);
    }
}
