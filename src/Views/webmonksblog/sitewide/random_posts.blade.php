<h5>Random Posts</h5>
<ul class="nav">
    @foreach(\WebMonksBlog\Models\WebMonksPost::inRandomOrder()->limit(5)->get() as $post)
        <li class="nav-item">
            <a class='nav-link' href='{{$post->url()}}'>{{$post->title}}</a>
        </li>
    @endforeach
</ul>