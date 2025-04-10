<?php

namespace WebMonksBlog\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use WebMonksBlog\Laravel\Fulltext\Search;
use WebMonksBlog\Models\WebMonksCategoryTranslation;
use Illuminate\Http\Request;
use WebMonksBlog\Captcha\UsesCaptcha;
use WebMonksBlog\Middleware\DetectLanguage;
use WebMonksBlog\Models\WebMonksCategory;
use WebMonksBlog\Models\WebMonksLanguage;
use WebMonksBlog\Models\WebMonksPost;
use WebMonksBlog\Models\WebMonksPostTranslation;

/**
 * Class WebMonksReaderController
 * All of the main public facing methods for viewing blog content (index, single posts)
 * @package WebMonksBlog\Controllers
 */
class WebMonksReaderController extends Controller
{
    use UsesCaptcha;

    public function __construct()
    {
        $this->middleware(DetectLanguage::class);
    }

    /**
     * Show blog posts
     * If category_slug is set, then only show from that category
     *
     * @param null $category_slug
     * @return mixed
     */
    public function index($locale = null, Request $request, $category_slug = null)
    {
        // the published_at + is_published are handled by WebMonksBlogPublishedScope, and don't take effect if the logged in user can manageb log posts

        //todo
        $title = 'Blog Page'; // default title...

        $categoryChain = null;
        $posts = array();
        if ($category_slug) {
            $category = WebMonksCategoryTranslation::where("slug", $category_slug)->with('category')->firstOrFail()->category;
            $categoryChain = $category->getAncestorsAndSelf();
            $posts = $category->posts()->where("webmonks_post_categories.category_id", $category->id)->with([ 'postTranslations' => function($query) use ($request){
                $query->where("lang_id" , '=' , $request->get("lang_id"));
            }
            ])->get();

            $posts = WebMonksPostTranslation::join('webmonks_posts', 'webmonks_post_translations.post_id', '=', 'webmonks_posts.id')
                ->where('lang_id', $request->get("lang_id"))
                ->where("is_published" , '=' , true)
                ->where('posted_at', '<', Carbon::now()->format('Y-m-d H:i:s'))
                ->orderBy("posted_at", "desc")
                ->whereIn('webmonks_posts.id', $posts->pluck('id'))
                ->paginate(config("webmonksblog.per_page", 10));

            // at the moment we handle this special case (viewing a category) by hard coding in the following two lines.
            // You can easily override this in the view files.
            \View::share('webmonksblog_category', $category); // so the view can say "You are viewing $CATEGORYNAME category posts"
            $title = 'Posts in ' . $category->category_name . " category"; // hardcode title here...
        } else {
            $posts = WebMonksPostTranslation::join('webmonks_posts', 'webmonks_post_translations.post_id', '=', 'webmonks_posts.id')
                ->where('lang_id', $request->get("lang_id"))
                ->where("is_published" , '=' , true)
                ->where('posted_at', '<', Carbon::now()->format('Y-m-d H:i:s'))
                ->orderBy("posted_at", "desc")
                ->paginate(config("webmonksblog.per_page", 10));
        }

        //load category hierarchy
        $rootList = WebMonksCategory::roots()->get();
        WebMonksCategory::loadSiblingsWithList($rootList);

        return view("webmonksblog::index", [
            'lang_list' => WebMonksLanguage::all('locale','name'),
            'locale' => $request->get("locale"),
            'lang_id' => $request->get('lang_id'),
            'category_chain' => $categoryChain,
            'categories' => $rootList,
            'posts' => $posts,
            'title' => $title,
            'routeWithoutLocale' => $request->get("routeWithoutLocale")
        ]);
    }

    /**
     * Show the search results for $_GET['s']
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function search(Request $request)
    {
        if (!config("webmonksblog.search.search_enabled")) {
            throw new \Exception("Search is disabled");
        }
        $query = $request->get("s");
        $search = new Search();
        $search_results = $search->run($query);

        \View::share("title", "Search results for " . e($query));

        $rootList = WebMonksCategory::roots()->get();
        WebMonksCategory::loadSiblingsWithList($rootList);

        return view("webmonksblog::search", [
            'lang_id' => $request->get('lang_id'),
            'locale' => $request->get("locale"),
            'categories' => $rootList,
            'query' => $query,
            'search_results' => $search_results,
            'routeWithoutLocale' => $request->get("routeWithoutLocale")
        ]
        );
    }

    /**
     * View all posts in $category_slug category
     *
     * @param Request $request
     * @param $category_slug
     * @return mixed
     */
    public function view_category(Request $request)
    {
        $hierarchy = $request->route('subcategories');

        $categories = explode('/', $hierarchy);
        return $this->index($request->get('locale'), $request, end($categories));
    }

    /**
     * View a single post and (if enabled) it's comments
     *
     * @param Request $request
     * @param $blogPostSlug
     * @return mixed
     */
    public function viewSinglePost(Request $request)
    {
        $blogPostSlug = $request->route('blogPostSlug');

        // the published_at + is_published are handled by WebMonksBlogPublishedScope, and don't take effect if the logged in user can manage log posts
        $blog_post = WebMonksPostTranslation::where([
            ["slug", "=", $blogPostSlug],
            ['lang_id', "=" , $request->get("lang_id")]
        ])->firstOrFail();

        if ($captcha = $this->getCaptchaObject()) {
            $captcha->runCaptchaBeforeShowingPosts($request, $blog_post);
        }

        $categories = $blog_post->post->categories()->with([ 'categoryTranslations' => function($query) use ($request){
            $query->where("lang_id" , '=' , $request->get("lang_id"));
        }
        ])->get();
        return view("webmonksblog::single_post", [
            'post' => $blog_post,
            // the default scope only selects approved comments, ordered by id
            'comments' => $blog_post->post->comments()
                ->with("user")
                ->get(),
            'captcha' => $captcha,
            'categories' => $categories,
            'locale' => $request->get("locale"),
            'routeWithoutLocale' => $request->get("routeWithoutLocale")
        ]);
    }
}
