<?php

namespace Yani\KeeperBot;

use Discord\Discord;
use Discord\Parts\Channel\Message;

interface CommandInterface
{

    public function getCommandConfig(): array;

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void;

}