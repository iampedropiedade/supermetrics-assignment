<?php
declare(strict_types=1);
namespace ApiClient;

use DataTransformer\AbstractDataTransformer;
use Exception\AppException;
use Http\Request\Request;
use Http\Client\Exception\ClientException;
use ApiClient\Exception\ApiException;

class Posts extends Api
{
    protected const PATH = 'assignment/posts';

    /**
     * @param AbstractDataTransformer $dataTransformer
     * @param int $page
     * @return array|null
     * @throws ApiException
     */
    public function get(AbstractDataTransformer $dataTransformer, int $page = 1): ?array
    {
        $this->request->setUrl($this->buildUrl(self::BASE_URL, self::PATH));
        $this->request->setMethod(Request::METHOD_GET);
        $this->request->setEncoding(Request::ENCODING_QUERY);
        $this->request->addData('page', $page);
        try {
            $response = $this->request->send();
        }
        catch (ClientException $e) {
            throw new ApiException('We could not access the API endpoint');
        }
        $data = $response->getBodyAsObject()->data;
        if (!is_array($data->posts) || $page !== $data->page) {
            return null;
        }
        $posts = [];
        foreach ($data->posts as $post) {
            $posts[$post->id] = $dataTransformer->transform($post);
        }
        return $posts;
    }

    /**
     * @param AbstractDataTransformer $dataTransformer
     * @return array
     * @throws AppException
     */
    public function getAll(AbstractDataTransformer $dataTransformer): array
    {
        try {
            $page = 1;
            $collatedPosts = [];
            while ($posts = $this->get($dataTransformer, $page++)) {
                $collatedPosts += $posts;
            }
            return $collatedPosts;
        }
        catch (ApiException $e) {
            throw new AppException($e->getMessage());
        }
    }

}
