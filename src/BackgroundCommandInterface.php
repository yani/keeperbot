<?php

namespace Yani\KeeperBot;

use Discord\Discord;
use Discord\Parts\Channel\Message;

interface BackgroundCommandInterface
{
    public function setBackgroundTaskHandler(BackgroundTaskHandler $task_handler): void;

    public function addBackgroundTask(BackgroundTask $task): void;

    public function runBackgroundTask(Discord $discord, Message $message, array $parameters = []): bool;

    public function runBackgroundTaskTimeout(Discord $discord, Message $message, array $parameters = []): void;
}