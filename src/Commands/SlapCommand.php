<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;
use Yani\KeeperBot\Utility;

class SlapCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => 'slap',
            'has_parameters' => true,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        $author_tag     = Utility::getAuthorTagFromMessage($message);
        $slapped_person = Utility::combineParameters($parameters);

        $message->channel->sendMessage(MessageBuilder::new()->setContent("**_{$author_tag} slaps {$slapped_person}!_**"));
    }
}