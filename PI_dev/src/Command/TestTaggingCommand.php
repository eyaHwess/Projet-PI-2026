<?php

namespace App\Command;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\Tagging\TaggingManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-tagging',
    description: 'Test the automatic tagging system with sample posts'
)]
class TestTaggingCommand extends Command
{
    public function __construct(
        private TaggingManager $taggingManager,
        private PostRepository $postRepository,
        private UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Testing Automatic Tagging System');
        $io->section('Automatic Tagging (default strategy)');

        // Get a published post
        $posts = $this->postRepository->findBy(['status' => 'published']);        
        if (!$posts) {
            $io->error('No published posts found. Please create a post first.');
            return Command::FAILURE;
        }
        foreach ($posts as $post) {
            $io->section(sprintf('Testing Post ID %d', $post->getId()));
            
            $tags = $this->taggingManager->generateTagsForPost($post);
        
            if (empty($tags)) {
                $io->warning('No tags generated.');
                continue;
            }
        
            foreach ($tags as $tag) {
                $io->text('- ' . $tag->getName());
            }
        
            $io->newLine();
        }
        $io->text([
            'Testing with post:',
            sprintf('  ID: %d', $post->getId()),
            sprintf('  Title: %s', $post->getTitle()),
            sprintf('  Content length: %d characters', strlen($post->getContent())),
        ]);

        $io->newLine();
        $io->text('Generating tags...');
        
        // Generate tags using the default strategy configured in TaggingManager
        $tags = $this->taggingManager->generateTagsForPost($post);
        
        if (empty($tags)) {
            $io->warning('No tags were generated. The post might be too short or contain only stop words.');
            return Command::SUCCESS;
        }

        $io->success(sprintf('Generated %d tag(s):', count($tags)));
        
        $tagData = [];
        foreach ($tags as $tag) {
            $tagData[] = [
                $tag->getName(),
                $tag->getSlug(),
                $tag->getUsageCount(),
            ];
        }
        
        $io->table(
            ['Tag Name', 'Slug', 'Usage Count'],
            $tagData
        );

        // Show all tags for this post
        $io->section('All Tags for This Post');
        $allTags = $post->getTags();
        
        if ($allTags->isEmpty()) {
            $io->text('No tags assigned to this post.');
        } else {
            $allTagData = [];
            foreach ($allTags as $tag) {
                $allTagData[] = [
                    $tag->getName(),
                    $tag->getUsageCount(),
                ];
            }
            
            $io->table(
                ['Tag Name', 'Usage Count'],
                $allTagData
            );
        }

        $io->success('Tagging test completed!');
        
        return Command::SUCCESS;
    }
}
