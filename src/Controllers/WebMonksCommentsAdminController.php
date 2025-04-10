<?php

namespace WebMonksBlog\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use WebMonksBlog\Events\CommentApproved;
use WebMonksBlog\Events\CommentWillBeDeleted;
use WebMonksBlog\Helpers;
use WebMonksBlog\Middleware\LoadLanguage;
use WebMonksBlog\Middleware\UserCanManageBlogPosts;
use WebMonksBlog\Models\WebMonksComment;

/**
 * Class WebMonksCommentsAdminController
 * @package WebMonksBlog\Controllers
 */
class WebMonksCommentsAdminController extends Controller
{
    /**
     * WebMonksCommentsAdminController constructor.
     */
    public function __construct()
    {
        $this->middleware(UserCanManageBlogPosts::class);
        $this->middleware(LoadLanguage::class);

    }

    /**
     * Show all comments (and show buttons with approve/delete)
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $comments = WebMonksComment::withoutGlobalScopes()->orderBy("created_at", "desc")
            ->with("post");

        if ($request->get("waiting_for_approval")) {
            $comments->where("approved", false);
        }

        $comments = $comments->paginate(100);
        return view("webmonksblog_admin::comments.index")
            ->withComments($comments
            );
    }


    /**
     * Approve a comment
     *
     * @param $blogCommentId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve($blogCommentId)
    {
        $comment = WebMonksComment::withoutGlobalScopes()->findOrFail($blogCommentId);
        $comment->approved = true;
        $comment->save();

        Helpers::flash_message("Approved!");
        event(new CommentApproved($comment));

        return back();

    }

    /**
     * Delete a submitted comment
     *
     * @param $blogCommentId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($blogCommentId)
    {
        $comment = WebMonksComment::withoutGlobalScopes()->findOrFail($blogCommentId);
        event(new CommentWillBeDeleted($comment));

        $comment->delete();

        Helpers::flash_message("Deleted!");
        return back();
    }


}
