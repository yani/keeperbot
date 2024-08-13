<?php

namespace Yani\KeeperBot;

use Discord\Discord;
use Discord\Parts\Channel\Message;

class BackgroundCommand
{
    protected BackgroundTaskHandler $task_handler;

    public function setBackgroundTaskHandler(BackgroundTaskHandler $task_handler): void
    {
        $this->task_handler = $task_handler;
    }

    public function addBackgroundTask(BackgroundTask $task): void
    {
        if(!$this->task_handler)
        {
            throw new \Exception('no task handler present');
        }

        $this->task_handler->addTask($task);
    }
}