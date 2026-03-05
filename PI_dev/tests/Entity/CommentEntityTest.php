<?php

namespace App\Tests\Entity;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Comment (module Post).
 */
class CommentEntityTest extends TestCase
{
    public function testConstructorSetsDefaults(): void
    {
        $comment = new Comment();
        $this->assertCount(0, $comment->getReplies());
        $this->assertCount(0, $comment->getCommentLikes());
    }

    public function testGettersSetters(): void
    {
        $comment = new Comment();
        $comment->setContent('Un commentaire de test.');
        $this->assertSame('Un commentaire de test.', $comment->getContent());
    }

    public function testSetPostAndCommenter(): void
    {
        $user = new User();
        $user->setEmail('commenter@test.com');
        $post = new Post();
        $post->setTitle('Post');
        $post->setContent('Contenu long du post.');
        $post->setCreatedBy($user);

        $comment = new Comment();
        $comment->setPost($post);
        $comment->setCommenter($user);
        $comment->setCreatedAt(new \DateTimeImmutable());

        $this->assertSame($post, $comment->getPost());
        $this->assertSame($user, $comment->getCommenter());
    }
}
