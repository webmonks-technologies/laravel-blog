<?php

namespace WebMonksBlog\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use WebMonksBlog\Events\CategoryAdded;
use WebMonksBlog\Events\CategoryEdited;
use WebMonksBlog\Events\CategoryWillBeDeleted;
use WebMonksBlog\Helpers;
use WebMonksBlog\Middleware\LoadLanguage;
use WebMonksBlog\Middleware\UserCanManageBlogPosts;
use WebMonksBlog\Models\WebMonksCategory;
use WebMonksBlog\Models\WebMonksCategoryTranslation;
use WebMonksBlog\Models\WebMonksLanguage;
use WebMonksBlog\Requests\DeleteWebMonksBlogCategoryRequest;
use WebMonksBlog\Requests\StoreWebMonksBlogCategoryRequest;
use WebMonksBlog\Requests\UpdateWebMonksBlogCategoryRequest;

/**
 * Class WebMonksCategoryAdminController
 * @package WebMonksBlog\Controllers
 */
class WebMonksCategoryAdminController extends Controller
{
    /**
     * WebMonksCategoryAdminController constructor.
     */
    public function __construct()
    {
        $this->middleware(UserCanManageBlogPosts::class);
        $this->middleware(LoadLanguage::class);

    }

    /**
     * Show list of categories
     *
     * @return mixed
     */
    public function index(Request $request){
        $language_id = $request->get('language_id');
        $categories = WebMonksCategoryTranslation::orderBy("category_id")->where('lang_id', $language_id)->paginate(25);
        return view("webmonksblog_admin::categories.index",[
            'categories' => $categories,
            'language_id' => $language_id
        ]);
    }

    /**
     * Show the form for creating new category
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create_category(Request $request){
        $language_id = $request->get('language_id');
        $language_list = WebMonksLanguage::where('active',true)->get();

        $cat_list = WebMonksCategory::whereHas('categoryTranslations', function ($query) use ($language_id) {
            return $query->where('lang_id', '=', $language_id);
        })->get();

        $rootList = WebMonksCategory::roots()->get();
        WebMonksCategory::loadSiblingsWithList($rootList);


        return view("webmonksblog_admin::categories.add_category",[
            'category' => new \WebMonksBlog\Models\WebMonksCategory(),
            'category_translation' => new \WebMonksBlog\Models\WebMonksCategoryTranslation(),
            'category_tree' => $cat_list,
            'cat_roots' => $rootList,
            'language_id' => $language_id,
            'language_list' => $language_list
        ]);
    }

    /**
     * Store a new category
     *
     * @param StoreWebMonksBlogCategoryRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     *
     * This controller is totally REST controller
     */
    public function store_category(Request $request){
        $language_id = $request->get('language_id');
        $language_list = $request['data'];

        if ($request['parent_id']== 0){
            $request['parent_id'] = null;
        }
        $new_category = WebMonksCategory::create([
            'parent_id' => $request['parent_id']
        ]);

        foreach ($language_list as $key => $value) {
            if ($value['lang_id'] != -1 && $value['category_name'] !== null){
                //check for slug availability
                $obj = WebMonksCategoryTranslation::where('slug',$value['slug'])->first();
                if ($obj){
                    WebMonksCategory::destroy($new_category->id);
                    return response()->json([
                        'code' => 403,
                        'message' => "slug is already taken",
                        'data' => $value['lang_id']
                    ]);
                }
                $new_category_translation = $new_category->categoryTranslations()->create([
                    'category_name' => $value['category_name'],
                    'slug' => $value['slug'],
                    'category_description' => $value['category_description'],
                    'lang_id' => $value['lang_id'],
                    'category_id' => $new_category->id
                ]);
            }
        }

        event(new CategoryAdded($new_category, $new_category_translation));
        Helpers::flash_message("Saved new category");
        return response()->json([
            'code' => 200,
            'message' => "category successfully aaded"
        ]);
    }

    /**
     * Show the edit form for category
     * @param $categoryId
     * @return mixed
     */
    public function edit_category($categoryId, Request $request){
        $language_id = $request->get('language_id');
        $language_list = WebMonksLanguage::where('active',true)->get();

        $category = WebMonksCategory::findOrFail($categoryId);
        $cat_trans = WebMonksCategoryTranslation::where(
            [
                ['lang_id', '=', $language_id],
                ['category_id', '=', $categoryId]
            ]
        )->first();

        return view("webmonksblog_admin::categories.edit_category",[
            'category' => $category,
            'category_translation' => $cat_trans,
            'categories_list' => WebMonksCategoryTranslation::orderBy("category_id")->where('lang_id', $language_id)->get(),
            'language_id' => $language_id,
            'language_list' => $language_list
        ]);
    }

    /**
     * Save submitted changes
     *
     * @param UpdateWebMonksBlogCategoryRequest $request
     * @param $categoryId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update_category(UpdateWebMonksBlogCategoryRequest $request, $categoryId){
        /** @var WebMonksCategory $category */
        $category = WebMonksCategory::findOrFail($categoryId);
        $language_id = $request->get('language_id');
        $translation = WebMonksCategoryTranslation::where(
            [
                ['lang_id', '=', $language_id],
                ['category_id', '=', $categoryId]
            ]
        )->first();
        $category->fill($request->all());
        $translation->fill($request->all());
        
        // if the parent_id is passed in as 0 it will create an error
        if ($category->parent_id <= 0) {
            $category->parent_id = null;
        }
        
        $category->save();
        $translation->save();

        Helpers::flash_message("Saved category changes");
        event(new CategoryEdited($category));
        return redirect($translation->edit_url());
    }

    /**
     * Delete the category
     *
     * @param DeleteWebMonksBlogCategoryRequest $request
     * @param $categoryId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function destroy_category(DeleteWebMonksBlogCategoryRequest $request, $categoryId){

        /* Please keep this in, so code inspectiwons don't say $request was unused. Of course it might now get marked as left/right parts are equal */
        $request=$request;

        $category = WebMonksCategory::findOrFail($categoryId);
        $children = $category->children()->get();
        if (sizeof($children) > 0) {
            Helpers::flash_message("This category could not be deleted it has some sub-categories. First try to change parent category of subs.");
            return redirect(route('webmonksblog.admin.categories.index'));
        }

        event(new CategoryWillBeDeleted($category));
        $category->delete();

        Helpers::flash_message("Category successfully deleted!");
        return redirect( route('webmonksblog.admin.categories.index') );
    }

}
