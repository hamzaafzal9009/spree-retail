@extends('layouts.app')
@section('css-files')
    <link rel="stylesheet" type="text/css" href="{{asset('css/style.css')}}">
    <style>
        .card{
            border: none;
        }
        .card-img-top{
            height: 100%;
            width: 115% !important;
        }
    </style>
@endsection
@section('content')
<div>

    @if(count($h_banners) > 0)
        <div id="demo" class="carousel slide" data-ride="carousel">
            <ul class="carousel-indicators">
                <li data-target="#demo" data-slide-to="0" class="active"></li>
                <li data-target="#demo" data-slide-to="{{count($h_banners)}}"></li>
            </ul>
            <div class="carousel-inner">
                @foreach($h_banners as $h_banner)
                    <div class="carousel-item @if($loop->iteration == 1)active @endif">
                        <a href="">
                            <img src="{{asset('storage/banner/'.$h_banner->image)}}" width="100%">
                        </a>
                    </div>
                @endforeach
            </div>
            <a class="carousel-control-prev" href="#demo" data-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </a>
            <a class="carousel-control-next" href="#demo" data-slide="next">
                <span class="carousel-control-next-icon"></span>
            </a>
        </div>

    @endif

    <!-- <div class="col-sm-12 p-0">
     <img src="https://img.lovepik.com//back_pic/05/70/26/915b9dfcd2e30f6.jpg_wh860.jpg" style="width: 100%; height: 300px;">
 </div> -->
</div>
<article>
    @if(count($new_products) > 0)
        <div class="mt-2 mb-2">
            <div class="container-fluid">
                <div class="d-flex space-between fs-15 mt-3" style="    margin-top: 44px !important;">
                    <div>
                        <h4 class="fs-15" style="margin-left: 28px;margin-bottom: 20px"> <b>New Arrivals</b> </h4>
                    </div>
                    <div class="shopAll">
                        <a href="" style="font-weight: 600;margin-right: 120px">Shop All <i class="fa fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="row" style="margin-left: -2px;">
                    @foreach($new_products as $product)
                        <div class="col-sm-12 col-md-10 col-lg-6 col-xl-2 ml-3">
                            <div class="card" style="margin-bottom: 1rem">
                                <a href="{{route('product.show',$product->slug)}}"><img class="card-img-top" src="{{asset('storage/product/'.$product->thumbnail)}}"></a>
                            </div>
                            <div class="mt-2">
                                <p class="text-bold mb-1">{{$product->user->vendor_profile->brand_name}}</p>
                                <p title="Source Title" style="font-size: 13px" class="mb-1">{{$product->name}}</p>
                                <p class="text-bold">${{$product->price}}.00</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if(count($f_cats) > 0)
        @foreach($f_cats as $f_cat)
            @php
                $products = \App\Models\Product::where('featured',$f_cat->name)
                            ->where('status','Active')->take(5)->get();
            @endphp
            @if(count($products) > 0)
                <div class="mt-2 mb-2">
                    <div class="container-fluid">
                        <div class="d-flex space-between fs-15 mt-3" style="    margin-top: 44px !important;">
                            <div>
                                <h4 class="fs-15" style="margin-left: 28px;margin-bottom: 20px"> <b>{{$f_cat->name}}</b> </h4>
                            </div>
                            <div class="shopAll">
                                <a href="" style="font-weight: 600;margin-right: 120px">Shop All <i class="fa fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row" style="margin-left: -2px;">
                            @foreach($products as $product)
                                <div class="col-sm-12 col-md-10 col-lg-6 col-xl-2 ml-3">
                                    <div class="card" style="margin-bottom: 1rem">
                                        <a href="{{route('product.show',$product->slug)}}"><img class="card-img-top" src="{{asset('storage/product/'.$product->thumbnail)}}"></a>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-bold mb-1">{{$product->user->vendor_profile->brand_name}}</p>
                                        <p title="Source Title" style="font-size: 13px" class="mb-1">{{$product->name}}</p>
                                        <p class="text-bold">${{$product->price}}.00</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
            @if(!is_null($f_banner))
                <div class="container-fluid">
                    <div class="col-sm-12 footer_banner" style="background-image: url({{asset('storage/banner/'.$f_banner->image)}});width: 90%;margin-left: 2rem;margin-top: 6rem">
                    </div>
                </div>
            @endif

</article>

@endsection
