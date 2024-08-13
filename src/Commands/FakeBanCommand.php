<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;
use Yani\KeeperBot\Utility;

class FakeBanCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => 'ban',
            'has_parameters' => true,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        $fake_banned_person = Utility::combineParameters($parameters);
        $message->channel->sendMessage(MessageBuilder::new()->setContent("**{$fake_banned_person} has been banned from the server!**"));
    }
}