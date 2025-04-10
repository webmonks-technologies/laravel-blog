@extends("layouts.app",['title'=>$title])

@section('blog-custom-css')
    <link type="text/css" href="{{ asset('webmonks-blog.css') }}" rel="stylesheet">
@endsection

@section("content")

    <div class='col-sm-12 webmonksblog_container'>
        @if(\Auth::check() && \Auth::user()->canManageWebMonksBlogPosts())
            <div class="text-center">
                <p class='mb-1'>You are logged in as a blog admin user.
                    <br>
                    <a href='{{route("webmonksblog.admin.index")}}'
                       class='btn border  btn-outline-primary btn-sm '>
                        <i class="fa fa-cogs" aria-hidden="true"></i>
                        Go To Blog Admin Panel</a>
                </p>
            </div>
        @endif

        <div class="row">
            <div class="col-md-9">

                @if($category_chain)
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12">
                                @forelse($category_chain as $cat)
                                    / <a href="{{$cat->categoryTranslations[0]->url($locale)}}">
                                        <span class="cat1">{{$cat->categoryTranslations[0]['category_name']}}</span>
                                    </a>
                                @empty @endforelse
                            </div>
                        </div>
                    </div>
                @endif

                @if(isset($webmonksblog_category) && $webmonksblog_category)
                    <h2 class='text-center'> {{$webmonksblog_category->category_name}}</h2>

                    @if($webmonksblog_category->category_description)
                        <p class='text-center'>{{$webmonksblog_category->category_description}}</p>
                    @endif

                @endif

                <div class="container">
                    <div class="row">
                        @forelse($posts as $post)
                            @include("webmonksblog::partials.index_loop")
                        @empty
                            <div class="col-md-12">
                                <div class='alert alert-danger'>No posts!</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <h6>Blog Categories</h6>
                <ul class="webmonks-cat-hierarchy">
                    @if($categories)
                        @include("webmonksblog::partials._category_partial", [
    'category_tree' => $categories,
    'name_chain' => $nameChain = "",
    'routeWithoutLocale' => $routeWithoutLocale
    ])
                    @else
                        <span>No Categories</span>
                    @endif
                </ul>
            </div>
        </div>

        @if (config('webmonksblog.search.search_enabled') )
            @include('webmonksblog::sitewide.search_form')
        @endif
        <div class="row">
            <div class="col-md-12 text-center">
                @foreach($lang_list as $lang)
                    <a href="{{route("webmonksblog.index" , $lang->locale)}}">
                        <span>{{$lang->name}}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

@endsection
