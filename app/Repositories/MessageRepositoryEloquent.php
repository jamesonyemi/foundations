<?php

namespace App\Repositories;

use App\Models\Message;

class MessageRepositoryEloquent implements MessageRepository
{
    /**
     * @var Message
     */
    private $model;

    /**
     * SMessageRepositoryEloquent constructor.
     * @param Message $model
     */
    public function __construct(Message $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model;
    }
}
