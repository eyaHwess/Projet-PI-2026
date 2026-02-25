<?php
namespace App\Controller\Post;

use App\Service\Post\PostService;
use App\Service\Post\PostLikeService;
use App\Service\Post\CommentService;
use App\Service\Post\CommentLikeService;
use App\Repository\PostRepository;
use App\Enum\PostStatus;
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


    #[Route('/posts/{id}/edit', name: 'post_edit_ajax', methods: ['POST'])]
    public function editFromList(
        int $id,
        Request $request,
        PostService $postService
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $title = $request->request->get('title');
        $content = $request->request->get('content');

        try {
            $postService->editPost($id, $title, $content, $user);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 403);
        }
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


    #[Route('/posts/{id}/delete', name: 'post_delete_ajax', methods: ['POST'])]
    public function deleteFromList(int $id, PostService $postService): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        
        try {
            $postService->deletePost($id, $user);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 403);
        }
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
    public function adminList(Request $request, PostRepository $postRepository, PostLikeService $postLikeService, CommentLikeService $commentLikeService): Response
    {
        // Get filter and sort parameters from request
        $sortBy = $request->query->get('sort', 'newest'); // newest, oldest, most_liked, most_commented
        $filterBy = $request->query->get('filter', 'all'); // all, following, my_posts
        
        // Base query - only published posts
        $queryBuilder = $postRepository->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', PostStatus::PUBLISHED->value);
        
        // Get current user
        $currentUser = $this->getUser();
        
        // Apply filters
        if ($filterBy === 'my_posts' && $currentUser) {
            $queryBuilder->andWhere('p.createdBy = :user')
                ->setParameter('user', $currentUser);
        }
        // Note: 'following' filter would require a Follow/Friend system
        
        // Apply sorting
        switch ($sortBy) {
            case 'oldest':
                $queryBuilder->orderBy('p.createdAt', 'ASC');
                break;
            case 'most_liked':
                $queryBuilder->leftJoin('p.postLikes', 'pl')
                    ->groupBy('p.id')
                    ->orderBy('COUNT(pl.id)', 'DESC');
                break;
            case 'most_commented':
                $queryBuilder->leftJoin('p.comments', 'c')
                    ->groupBy('p.id')
                    ->orderBy('COUNT(c.id)', 'DESC');
                break;
            case 'newest':
            default:
                $queryBuilder->orderBy('p.createdAt', 'DESC');
                break;
        }
        
        $posts = $queryBuilder->getQuery()->getResult();

        // Add like status for each post
        $postsWithLikeStatus = [];
        foreach ($posts as $post) {
            $postsWithLikeStatus[] = [
                'post' => $post,
                'isLikedByCurrentUser' => $postLikeService->hasUserLikedPost($post, $currentUser)
            ];
        }

        return $this->render('post/post_list.html.twig', [
            'postsWithLikeStatus' => $postsWithLikeStatus,
            'currentUser' => $currentUser,
            'commentLikeService' => $commentLikeService,
            'currentSort' => $sortBy,
            'currentFilter' => $filterBy
        ]);
    }

    #[Route('/posts/create', name: 'post_create_ajax', methods: ['POST'])]
    public function createFromList(Request $request, PostService $postService): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $title = $request->request->get('title');
        $content = $request->request->get('content');
        $status = $request->request->get('status', PostStatus::PUBLISHED->value);

        // Handle image uploads
        $images = [];
        $uploadedFiles = $request->files->get('images', []);
        
        if (!empty($uploadedFiles)) {
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/posts';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $maxImages = 3;
            $count = 0;
            
            foreach ($uploadedFiles as $uploadedFile) {
                if ($count >= $maxImages) {
                    break;
                }
                
                if ($uploadedFile) {
                    $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                    // Simple sanitization: remove special characters
                    $safeFilename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $originalFilename);
                    $newFilename = $safeFilename . '_' . uniqid() . '.' . $uploadedFile->guessExtension();
                    
                    try {
                        $uploadedFile->move($uploadDir, $newFilename);
                        $images[] = '/uploads/posts/' . $newFilename;
                        $count++;
                    } catch (\Exception $e) {
                        // Handle upload error silently or log it
                    }
                }
            }
        }

        $postService->createPost($title, $content, $user, $status, $images);

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
        $parentCommentId = $request->request->get('parent_comment_id');

        if (empty(trim($content))) {
            return $this->json(['error' => 'Comment cannot be empty'], 400);
        }

        $comment = $commentService->createComment($id, $content, $user, $parentCommentId);

        // Return comment data as JSON
        return $this->json([
            'success' => true,
            'comment' => [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'createdAt' => $comment->getCreatedAt()->format('d M Y, H:i'),
                'isReply' => $comment->isReply(),
                'commenter' => [
                    'id' => $comment->getCommenter()->getId(),
                    'firstName' => $comment->getCommenter()->getFirstName(),
                    'lastName' => $comment->getCommenter()->getLastName()
                ]
            ]
        ]);
    }

    #[Route('/comments/{id}/like', name: 'comment_like', methods: ['POST'])]
    public function likeComment(int $id, CommentLikeService $commentLikeService): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $result = $commentLikeService->toggleLike($id, $user);

        return $this->json($result);
    }

    #[Route('/postList', name: 'post_list', methods: ['GET'])]
    public function list(PostRepository $repo): Response
    {
        return $this->render('post/postList.html.twig', [
            'posts' => $repo->findBy([], ['createdAt' => 'DESC'])
        ]);
    }



    #[Route('/comments/{id}/edit', name: 'comment_edit', methods: ['POST'])]
    public function editComment(int $id, Request $request, CommentService $commentService): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $content = $request->request->get('content');

        try {
            $commentService->editComment($id, $content, $user);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 403);
        }
    }

    #[Route('/comments/{id}/delete', name: 'comment_delete', methods: ['POST'])]
    public function deleteComment(int $id, CommentService $commentService): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        try {
            $commentService->deleteComment($id, $user);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 403);
        }
    }
}
