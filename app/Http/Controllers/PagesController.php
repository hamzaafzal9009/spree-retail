<?php

namespace App\Http\Controllers;

use App\Events\OrderMade;
use App\Helpers\Helper;
use App\Http\Requests\ApplyJob;
use App\Http\Requests\user\StoreRequestVendor;
use App\Mail\VendorRegisterMail;
use App\Models\Banner;
use App\Models\Career;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\VendorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PagesController extends Controller
{
    public function event()
    {
        $order = Order::all();
        event(new OrderMade($order[0],auth()->user()));
    }
    public function home()
    {
        //Get new arrivals
        $new_products = Product::orderBy('created_at','desc')->where('status','Active')->take(5)->get();
//        //Get new latest
//        $fashion_products = Product::where('main','Fashion')->where('status','Active')->take(5)->get();
//        //Get health and beauty
//        $health_products = Product::where('main','Health & Beauty')->where('status','Active')->take(5)->get();

        $f_cats = Category::where('main','Featured')->get();


        //dd($f_cats);
        $h_banners = Banner::where('type', 'Header')->get();


        $f_banner = Banner::where('type', 'Footer')->first();


        return view('pages.home',compact('f_cats','h_banners','f_banner','new_products'));
    }

    public function sellOnSpree()
    {
        return view('pages.sellOnSpree');
    }

    public function sellOnSpree_form()
    {
        return view('auth.vendorForm');
    }


    public function contact_us()
    {
        return view('pages.contact_us');
    }
    public function sellOnSpree_formSubmit(StoreRequestVendor $request)
    {
        $vendor = VendorRequest::create([
            'contact_name' => $request->name,
            'contact_email' => $request->email,
            'brand_name' => $request->brand_name,
            'website_link' => $request->link,
            'structure' => $request->structure,
            'about' => $request->about,
            'status' => config('enums.vendor_request_status.in_progress'),
        ]);
        Mail::send(new VendorRegisterMail($vendor));

        return view('auth.success');
    }

    public function main($main)
    {
        $name = null;
        if (in_array($main, config('enums.main_categories')))
        {
            $products = Product::where('main',$main)
                ->where('status','Active')->take(15)->get();
            $categories = Category::where('main',$main)
                ->has('parents','=',0)
                ->with('child')->get();

            return view('pages.main',compact('name','main','categories','products'));
        }else{
            return back();
        }

    }

    public function child($main,$p_name,$name)
    {
        if (in_array($main, config('enums.main_categories')))
        {
            $category = Category::where('name',$name)
                ->whereHas('parents',function ($query) use ($p_name){
                    $query->where('name',$p_name);
                })->first();


            $categories = Category::where('main',$main)
                ->has('parents','=',0)
                ->with('child')->get();

            $products = Product::where('main',$main)
                ->where('status','Active')
                ->whereHas('categories',function ($query) use ($name){
                    $query->where('name',$name);
                })->whereHas('categories',function ($query) use ($p_name){
                    $query->where('name',$p_name);
                })
                ->get();


            return view('pages.main',compact('name','main','categories','category','products'));
        }else{
            return back();
        }

    }

    public function category($p_name, $name)
    {
        $category = Category::where('name',$name)->first();

        $products = $category->products;

        if ($category != null){
            return view('pages.category',compact('category','products'));
        }
    }

    public function search(Request $request)
    {
        if($request->ajax())
        {
            $output="";
            $products=DB::table('products')->where('name','LIKE','%'.$request->search."%")->take(6)->get();
            if($products)
            {
                foreach ($products as $key => $product) {
                    $output.= '<a style="color:black; display: none" href="'.route('product.show',$product->slug).'">'.$product->name.'</a>';
                }
                return Response($output);
            }
        }
    }

    public function career()
    {
        $search = null;

        return view('new_website.career.index',compact('search'));
    }

    public function career_category($category)
    {
        $jobs = Career::all();
        $country = $jobs->pluck('country')->unique();

        $city = $jobs->pluck('city')->unique();

        $jobs = null;
        $jobs = Career::where('category',$category)->paginate(6);

        $search = null;

        return view('new_website.career.category',compact('jobs','country','city','category','search'));
    }

    public function career_apply($id)
    {

        $job = Career::findOrFail($id);


        return view('new_website.career.apply',compact('job'));
    }

    public function career_apply_store(ApplyJob $request,$id)
    {

        $job = Career::findOrFail($id);

        //get file name with extension
        $fileNameWithExt = $request['cv']->getClientOriginalName();
        //get file name
        $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
        //get extension
        $extension = $request['cv']->getClientOriginalExtension();
        //file name
        $fileNameToStore = $filename . '_' . time() . '.' . $extension;

        $path = $request['cv']->storeAs('cv', $fileNameToStore,'public');


        $apply = \App\Models\ApplyJob::create([
            'job_id' => $job['id'],
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'linkedin_profile' => $request['linkedin_profile'],
            'about' => $request['about'],
            'cv' => $fileNameToStore,
        ]);



        return view('auth.success');
    }

    public function searchJob(Request $request, $category)
    {
        $jobs = null;
        $country= null;
        $city = null;
        if ($category == 'default')
        {
            $jobs = Career::where('name','LIKE','%'.$request->search."%")
                ->paginate(6);
        }else{
            $jobs = Career::where('name','LIKE','%'.$request->search."%")
                ->where('category',$category)->paginate(6);
        }

       if ($jobs != null){
           $country = $jobs->pluck('country')->unique();

           $city = $jobs->pluck('city')->unique();
       }

        $search = $request['search'];

        return view('new_website.career.category',compact('jobs','country','city','search'));
    }

    public function kitchen()
    {
        return view('new_website.restaurant.index');
    }

    public function kitchenPartner_form()
    {
        return view('new_website.restaurant.kitchenPartner');
    }









}
