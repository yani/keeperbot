<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;
use Discord\Factory\Factory;
use Discord\Parts\Channel\Attachment;
use Yani\KeeperBot\CommandInterface;
use Yani\KeeperBot\Utility;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Video;

class BellOfShameCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => ['shame', 'bellofshame'],
            'has_parameters' => false,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        $message->channel->sendMessage(
            MessageBuilder::new()->addFile(__DIR__ . '/../../files/bell-of-shame.mp3')
        );
    }
}