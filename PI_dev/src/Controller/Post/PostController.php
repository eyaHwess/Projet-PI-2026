<?php
namespace App\Controller\Post;

use App\Service\Post\PostService;
use App\Service\Post\PostLikeService;
use App\Service\Post\CommentService;
use App\Service\Post\CommentLikeService;
use App\Service\Post\SavedPostService;
use App\Service\Moderation\ModerationService;
use App\Service\Tagging\TaggingManager;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use App\Enum\PostStatus;
use Doctrine\ORM\EntityManagerInterface;
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
    public function create(
        Request $request, 
        PostService $postService,
        ModerationService $moderationService
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $title = $request->request->get('title');
        $content = $request->request->get('content');

        // Moderate title and content
        $titleResult = $moderationService->analyzeContent($title);
        $contentResult = $moderationService->analyzeContent($content);

        // Check if either title or content is inappropriate
        if (!$titleResult->isClean()) {
            $this->addFlash('error', $titleResult->getMessage());
            return $this->redirectToRoute('post_new');
        }

        if (!$contentResult->isClean()) {
            $this->addFlash('error', $contentResult->getMessage());
            return $this->redirectToRoute('post_new');
        }

        // Content is clean, proceed with creation
        $postService->createPost($title, $content, $user);
        $this->addFlash('success', 'Post created successfully!');

        return $this->redirectToRoute('post_new');
    }


    #[Route('/posts/{id}/edit', name: 'post_edit_ajax', methods: ['POST'])]
    public function editFromList(
        int $id,
        Request $request,
        PostService $postService,
        ModerationService $moderationService
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $title = $request->request->get('title');
        $content = $request->request->get('content');
        $status = $request->request->get('status');

        // Moderate post (title + content merged into one result)
        $moderationResult = $moderationService->analyzePost($title, $content, $user, 'post_edit');

        // Check if post content is inappropriate
        if (!$moderationResult->isClean()) {
            return $this->json([
                'success' => false,
                'error' => $moderationResult->getMessage(),
                'moderation' => $moderationResult->toArray()
            ], 400);
        }

        try {
            $postService->editPost($id, $title, $content, $user, $status);
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
    public function adminList(
        Request $request, 
        PostRepository $postRepository, 
        TagRepository $tagRepository,
        PostLikeService $postLikeService, 
        CommentLikeService $commentLikeService, 
        SavedPostService $savedPostService
    ): Response {
        // Get filter and sort parameters from request
        $sortBy = $request->query->get('sort', 'newest'); // newest, oldest, most_liked, most_commented
        $filterBy = $request->query->get('filter', 'all'); // all, following, my_posts
        $tagSlug = $request->query->get('tag'); // tag slug for filtering
        
        // Base query - only published posts
        $queryBuilder = $postRepository->createQueryBuilder('p')
            ->where('p.status = :status')
            ->setParameter('status', PostStatus::PUBLISHED->value);
        
        // Get current user
        $currentUser = $this->getUser();
        
        // Apply tag filter if provided
        if ($tagSlug) {
            $tag = $tagRepository->findBySlug($tagSlug);
            if ($tag) {
                $queryBuilder->innerJoin('p.tags', 't')
                    ->andWhere('t.id = :tagId')
                    ->setParameter('tagId', $tag->getId());
            }
        }
        
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

        // Add like and save status for each post
        $postsWithLikeStatus = [];
        foreach ($posts as $post) {
            $postsWithLikeStatus[] = [
                'post' => $post,
                'isLikedByCurrentUser' => $postLikeService->hasUserLikedPost($post, $currentUser),
                'isSavedByCurrentUser' => $savedPostService->hasUserSavedPost($post, $currentUser)
            ];
        }

        // Fetch available tags for filter dropdown
        $availableTags = $tagRepository->findAvailableTags();

        return $this->render('post/post_list.html.twig', [
            'postsWithLikeStatus' => $postsWithLikeStatus,
            'currentUser' => $currentUser,
            'commentLikeService' => $commentLikeService,
            'currentSort' => $sortBy,
            'currentFilter' => $filterBy,
            'currentTag' => $tagSlug,
            'availableTags' => $availableTags
        ]);
    }

    #[Route('/posts/drafts/list', name: 'post_drafts_list', methods: ['GET'])]
    public function draftsList(PostRepository $postRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $currentUser = $this->getUser();
        
        // Get only draft posts for current user
        $drafts = $postRepository->createQueryBuilder('p')
            ->where('p.status = :status')
            ->andWhere('p.createdBy = :user')
            ->setParameter('status', PostStatus::DRAFT->value)
            ->setParameter('user', $currentUser)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        // Return rendered HTML
        return $this->render('post/post_drafts.html.twig', [
            'drafts' => $drafts
        ]);
    }

    #[Route('/posts/{id}/edit-data', name: 'post_edit_data', methods: ['GET'])]
    public function getPostEditData(int $id, PostRepository $postRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $currentUser = $this->getUser();
        
        $post = $postRepository->find($id);
        
        if (!$post) {
            return $this->json(['error' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }
        
        // Check if user owns the post
        if ($post->getCreatedBy() !== $currentUser) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        return $this->json([
            'id' => $post->getId(),
            'title' => $post->getTitle(),
            'content' => $post->getContent(),
            'status' => $post->getStatus()
        ]);
    }

    #[Route('/posts/scheduled/list', name: 'post_scheduled_list', methods: ['GET'])]
    public function scheduledList(PostRepository $postRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $currentUser = $this->getUser();
        
        // Get only scheduled posts for current user
        $scheduled = $postRepository->createQueryBuilder('p')
            ->where('p.status = :status')
            ->andWhere('p.createdBy = :user')
            ->setParameter('status', PostStatus::SCHEDULED->value)
            ->setParameter('user', $currentUser)
            ->orderBy('p.scheduledAt', 'ASC')
            ->getQuery()
            ->getResult();

        // Return rendered HTML
        return $this->render('post/post_scheduled.html.twig', [
            'scheduled' => $scheduled
        ]);
    }

    #[Route('/posts/{id}/publish', name: 'post_publish', methods: ['POST'])]
    public function publishNow(int $id, PostRepository $postRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $currentUser = $this->getUser();
        
        $post = $postRepository->find($id);
        
        if (!$post) {
            return $this->json(['error' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }
        
        // Check if user owns the post
        if ($post->getCreatedBy() !== $currentUser) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        // Publish the post immediately
        $post->setStatus(PostStatus::PUBLISHED->value);
        $post->setScheduledAt(null); // Clear scheduled time since we're publishing now
        
        $em->flush();
        
        return $this->json(['success' => true]);
    }


    #[Route('/posts/create', name: 'post_create_ajax', methods: ['POST'])]
    public function createFromList(
        Request $request, 
        PostService $postService,
        ModerationService $moderationService,
        TaggingManager $taggingManager
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $title = $request->request->get('title');
        $content = $request->request->get('content');
        $status = $request->request->get('status', PostStatus::PUBLISHED->value);
        $scheduledAtStr = $request->request->get('scheduledAt');
        
        // Parse scheduled date if provided
        $scheduledAt = null;
        if ($scheduledAtStr) {
            try {
                $scheduledAt = new \DateTimeImmutable($scheduledAtStr);
            } catch (\Exception $e) {
                // Invalid date format, ignore
            }
        }

        // Moderate post (title + content merged into one result)
        $moderationResult = $moderationService->analyzePost($title, $content, $user, 'post');

        // Check if post content is inappropriate
        if (!$moderationResult->isClean()) {
            // Return JSON for AJAX requests
            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => false,
                    'error' => $moderationResult->getMessage(),
                    'moderation' => $moderationResult->toArray()
                ], 400);
            }
            $this->addFlash('error', $moderationResult->getMessage());
            return $this->redirectToRoute('post_list_view');
        }

        // Content is clean, proceed with creation
        $post = $postService->createPost($title, $content, $user, $status, [], $scheduledAt);
        
        // Generate tags automatically (only for published posts)
        if ($post && $status === PostStatus::PUBLISHED->value) {
            $taggingManager->generateTagsForPost($post);
        }
        
        // Return JSON for AJAX requests
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'message' => 'Post created successfully!'
            ]);
        }
        
        $this->addFlash('success', 'Post created successfully!');
        return $this->redirectToRoute('post_list_view');
    }

    #[Route('/posts/upload-image', name: 'post_upload_image', methods: ['POST'])]
    public function uploadImage(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $uploadedFile = $request->files->get('upload');
        
        if (!$uploadedFile) {
            return $this->json(['error' => ['message' => 'No file uploaded']], 400);
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/posts/inline';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $originalFilename);
        $newFilename = $safeFilename . '_' . uniqid() . '.' . $uploadedFile->guessExtension();
        
        try {
            $uploadedFile->move($uploadDir, $newFilename);
            $url = '/uploads/posts/inline/' . $newFilename;
            
            // Return CKEditor expected format
            return $this->json([
                'url' => $url
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => ['message' => 'Upload failed: ' . $e->getMessage()]], 500);
        }
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

    #[Route('/posts/{id}/save', name: 'post_save', methods: ['POST'])]
    public function savePost(int $id, SavedPostService $savedPostService): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $result = $savedPostService->toggleSave($id, $user);

        return $this->json($result);
    }

    #[Route('/posts/{id}/comment', name: 'post_comment', methods: ['POST'])]
    public function addComment(
        int $id, 
        Request $request, 
        CommentService $commentService,
        ModerationService $moderationService
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $content = $request->request->get('content');
        $parentCommentId = $request->request->get('parent_comment_id');

        if (empty(trim($content))) {
            return $this->json(['error' => 'Comment cannot be empty'], 400);
        }

        // Moderate comment content
        $moderationResult = $moderationService->analyzeContent($content, $user, 'comment');

        if (!$moderationResult->isClean()) {
            return $this->json([
                'error' => $moderationResult->getMessage(),
                'moderation' => $moderationResult->toArray()
            ], 400);
        }

        // Content is clean, create comment
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
    public function editComment(
        int $id, 
        Request $request, 
        CommentService $commentService,
        ModerationService $moderationService
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $content = $request->request->get('content');

        // Moderate comment content
        $moderationResult = $moderationService->analyzeContent($content, $user, 'comment_edit');

        if (!$moderationResult->isClean()) {
            return $this->json([
                'success' => false,
                'error' => $moderationResult->getMessage(),
                'moderation' => $moderationResult->toArray()
            ], 400);
        }

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

    #[Route('/posts/{id}/track-view', name: 'post_track_view', methods: ['POST'])]
    public function trackView(int $id, Request $request, PostRepository $postRepository, EntityManagerInterface $entityManager): Response
    {
        $post = $postRepository->find($id);
        
        if (!$post) {
            return $this->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        // Check session to prevent duplicate views
        $sessionKey = 'post_viewed_' . $id;
        if (!$request->getSession()->has($sessionKey)) {
            $post->incrementViewCount();
            $entityManager->flush();
            
            // Mark as viewed in session
            $request->getSession()->set($sessionKey, true);
            
            return $this->json(['success' => true, 'viewCount' => $post->getViewCount()]);
        }
        
        return $this->json(['success' => false, 'message' => 'Already counted']);
    }

    #[Route('/posts/{id}/track-click', name: 'post_track_click', methods: ['POST'])]
    public function trackClick(int $id, PostRepository $postRepository, EntityManagerInterface $entityManager): Response
    {
        $post = $postRepository->find($id);
        
        if (!$post) {
            return $this->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        $post->incrementClickCount();
        $entityManager->flush();
        
        return $this->json(['success' => true, 'clickCount' => $post->getClickCount()]);
    }
}
