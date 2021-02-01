<?php
declare(strict_types=1);
namespace DataTransformer;

use DateTimeImmutable;
use Exception;
use stdClass;

class PostTransformer extends AbstractDataTransformer
{
    /**
     * @param stdClass $data
     * @return array|null
     */
    public function transform($data): ?array
    {
        $post = [];
        try {
            $postDate = new DateTimeImmutable($data->created_time);
        }
        catch (Exception $e) {
            return null;
        }
        $post['id'] = $data->id;
        $post['date']['date'] = $postDate->format('d-m-Y');
        $post['date']['week'] = (int)$postDate->format('W');
        $post['date']['month'] = (int)$postDate->format('n');
        $post['date']['year'] = (int)$postDate->format('Y');
        $post['type'] = $data->type;
        $post['length'] = strlen($data->message);
        $post['user']['id'] = $data->from_id;
        $post['user']['name'] = $data->from_name;
        return $post;
    }
}
