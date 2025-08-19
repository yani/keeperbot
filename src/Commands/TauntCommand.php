<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\Utility;
use Yani\KeeperBot\CommandInterface;

class TauntCommand implements CommandInterface
{
    private array $taunts = [
        "Your efforts are futile!",
        "My dungeon is the finest you shall ever see!",
        "My minions will leave your dungeon a crumbling ruin!",
        "Your entrails will decorate the darkest alcove of my domain!",
        "Your corpse will feed my minions yet, Keeper!",
        "Prepare for oblivion, Keeper!",
        "Pathetic creature! I shall crush you in an instant!",
    ];

    public function getCommandConfig(): array
    {
        return [
            'command' => ['taunt'],
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        // Check if we are taunting a person
        if (empty($parameters) === false) {
            $taunted_person = Utility::combineParameters($parameters);
            $message->channel->sendMessage(MessageBuilder::new()->setContent("{$taunted_person} **_{$this->getRandomTaunt()}_**"));
            return;
        }

        // General taunt
        $message->channel->sendMessage(MessageBuilder::new()->setContent("**_{$this->getRandomTaunt()}_**"));
    }

    public function getRandomTaunt(): string
    {
        return $this->taunts[\random_int(0, \count($this->taunts) - 1)];
    }
}
