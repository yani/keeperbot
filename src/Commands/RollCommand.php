<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;
use Yani\KeeperBot\Utility;

class RollCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command' => 'roll',
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        $author_tag = Utility::getAuthorTagFromMessage($message);

        $roll_min = 1;
        $roll_max = 100;

        if(\count($parameters) === 1)
        {
            $roll_max = (int) $parameters[0];
        }

        if(\count($parameters) === 2)
        {
            $roll_min = (int) $parameters[0];
            $roll_max = (int) $parameters[1];
        }

        if($roll_min > $roll_max)
        {
            Utility::reactRaisedEyebrowFaceToMessage($message);
            return;
        }

        $roll = \random_int($roll_min, $roll_max);
        $message->channel->sendMessage(MessageBuilder::new()->setContent("{$author_tag} rolls a **{$roll}** ({$roll_min}-{$roll_max})"));
    }
}