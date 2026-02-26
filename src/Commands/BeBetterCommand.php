<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;

class BeBetterCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => ['better', 'bebetter'],
            'has_parameters' => false,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        $message->channel->sendMessage(
            MessageBuilder::new()->addFile(__DIR__ . '/../../files/you-just-need-to-be-better.ogg')
        );
    }
}
