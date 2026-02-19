<?php
namespace App\Controller\Post;

use App\Service\Post\PostService;
use App\Service\Post\PostLikeService;
use App\Service\Post\CommentService;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class PostController extends AbstractController
{
    #[Route('/post/new', name: 'post_new', methods: ['GET'])]
    public function new(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('post/create.html.twig');
    }

    #[Route('/post/create', name: 'post_create', methods: ['POST'])]
    public function create(Request $request, PostService $postService): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $title = $request->request->get('title');
        $content = $request->request->get('content');

        $postService->createPost($title, $content, $user);

        return $this->redirectToRoute('post_new');
    }


    #[Route('/post/{id}/edit', name: 'post_edit', methods: ['POST'])]
    public function edit(
        int $id,
        Request $request,
        PostService $postService
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $title = $request->request->get('title');
        $content = $request->request->get('content');

        $postService->editPost($id, $title, $content, $user);

        return $this->redirectToRoute('post_list');
    }


    #[Route('/post/{id}/delete', name: 'post_delete', methods: ['POST'])]
    public function delete(
        int $id,
        PostService $postService
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $postService->deletePost($id, $user);

        return $this->redirectToRoute('post_list');
    }

    #[Route('/posts', name: 'post_list_view', methods: ['GET'])]
    public function adminList(PostRepository $postRepository, PostLikeService $postLikeService): Response
    {
        // Get all posts from database, ordered by newest first
        $posts = $postRepository->findBy([], ['createdAt' => 'DESC']);

        // Get current user (may be null if not logged in)
        $currentUser = $this->getUser();

        // Add like status for each post
        $postsWithLikeStatus = [];
        foreach ($posts as $post) {
            $postsWithLikeStatus[] = [
                'post' => $post,
                'isLikedByCurrentUser' => $postLikeService->hasUserLikedPost($post, $currentUser)
            ];
        }

        return $this->render('admin/post/post_list.html.twig', [
            'postsWithLikeStatus' => $postsWithLikeStatus,
            'currentUser' => $currentUser
        ]);
    }

    #[Route('/posts/create', name: 'post_create_ajax', methods: ['POST'])]
    public function createFromList(Request $request, PostService $postService): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $title = $request->request->get('title');
        $content = $request->request->get('content');

        $postService->createPost($title, $content, $user);

        // Redirect back to post list
        return $this->redirectToRoute('post_list_view');
    }

    #[Route('/posts/{id}/like', name: 'post_like', methods: ['POST'])]
    public function likePost(int $id, PostLikeService $postLikeService): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $result = $postLikeService->toggleLike($id, $user);

        // Return JSON response for AJAX
        return $this->json($result);
    }

    #[Route('/posts/{id}/comment', name: 'post_comment', methods: ['POST'])]
    public function addComment(int $id, Request $request, CommentService $commentService): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $content = $request->request->get('content');

        if (empty(trim($content))) {
            return $this->json(['error' => 'Comment cannot be empty'], 400);
        }

        $comment = $commentService->createComment($id, $content, $user);

        // Return comment data as JSON
        return $this->json([
            'success' => true,
            'comment' => [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'createdAt' => $comment->getCreatedAt()->format('d M Y, H:i'),
                'commenter' => [
                    'firstName' => $comment->getCommenter()->getFirstName(),
                    'lastName' => $comment->getCommenter()->getLastName()
                ]
            ]
        ]);
    }

    #[Route('/postList', name: 'post_list', methods: ['GET'])]
    public function list(PostRepository $repo): Response
    {
        return $this->render('post/postList.html.twig', [
            'posts' => $repo->findBy([], ['createdAt' => 'DESC'])
        ]);
    }

}
