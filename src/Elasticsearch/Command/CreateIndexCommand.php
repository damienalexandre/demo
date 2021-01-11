<?php

namespace App\Elasticsearch\Command;

use App\Repository\PostRepository;
use Elastica\Document;
use JoliCode\Elastically\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateIndexCommand extends Command
{
    protected static $defaultName = 'app:elasticsearch:create-index';
    private $client;
    private $postRepository;

    protected function configure()
    {
        $this
            ->setDescription('Build new index from scratch and populate.')
        ;
    }

    public function __construct(string $name = null, Client $client, PostRepository $postRepository)
    {
        parent::__construct($name);
        $this->client = $client;
        $this->postRepository = $postRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $indexBuilder = $this->client->getIndexBuilder();
        $newIndex = $indexBuilder->createIndex('post');
        $indexer = $this->client->getIndexer();

        $allPosts = $this->postRepository->createQueryBuilder('post')->getQuery()->iterate();
        foreach ($allPosts as $post) {
            $post = $post[0];
            $indexer->scheduleIndex($newIndex, new Document($post->getId(), $post->toModel()));
        }

        $indexer->flush();

        $indexBuilder->markAsLive($newIndex, 'post');
        $indexBuilder->speedUpRefresh($newIndex);
        $indexBuilder->purgeOldIndices('post');

        return Command::SUCCESS;
    }
}
