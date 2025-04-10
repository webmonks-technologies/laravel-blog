@extends("layouts.app",['title'=>$post->gen_seo_title()])

@section('blog-custom-css')
    <link type="text/css" href="{{ asset('webmonks-blog.css') }}" rel="stylesheet">
@endsection

@section("content")

    @if(config("webmonksblog.reading_progress_bar"))
        <div id="scrollbar">
            <div id="scrollbar-bg"></div>
        </div>
    @endif

    {{--https://github.com/webmonks/laravel-blog--}}

    <div class='container'>
    <div class='row'>
        <div class='col-sm-12 col-md-12 col-lg-12'>

            @include("webmonksblog::partials.show_errors")
            @include("webmonksblog::partials.full_post_details")


            @if(config("webmonksblog.comments.type_of_comments_to_show","built_in") !== 'disabled')
                <div class="" id='maincommentscontainer'>
                    <h2 class='text-center' id='webmonksblogcomments'>Comments</h2>
                    @include("webmonksblog::partials.show_comments")
                </div>
            @else
                {{--Comments are disabled--}}
            @endif


        </div>
    </div>
    </div>

@endsection

@section('blog-custom-js')
    <script src="{{asset('webmonks-blog.js')}}"></script>
@endsection
