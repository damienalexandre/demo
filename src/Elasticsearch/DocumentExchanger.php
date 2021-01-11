<?php


namespace App\Elasticsearch;

use App\Model\Post;
use App\Repository\PostRepository;
use Elastica\Document;
use JoliCode\Elastically\Messenger\DocumentExchangerInterface;

class DocumentExchanger implements DocumentExchangerInterface
{
    private $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function fetchDocument(string $className, string $id): ?Document
    {
        if ($className === Post::class) {
            $post = $this->postRepository->find($id);
            if ($post) {
                return new Document($id, $post->toModel());
            }
        }

        return null;
    }
}
