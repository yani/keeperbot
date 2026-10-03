<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use NXP\Exception\DivisionByZeroException;
use NXP\Exception\UnknownVariableException;
use NXP\MathExecutor;
use Yani\KeeperBot\CommandInterface;
use Yani\KeeperBot\Utility;

class MathCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => ['math', 'calc', 'calculate'],
            'has_parameters' => true,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        $expression = Utility::combineParameters($parameters);

        try {
            $executor = new MathExecutor();
            $result   = $executor->execute($expression);
            if($result === INF) {
                $message->reply("I can't count that far buddy");
                return;
            }
            $message->reply("{$expression} = **{$result}**");
        } catch (UnknownVariableException $e) {
            $message->reply("Unknown variable detected");
        } catch (DivisionByZeroException $e) {
            $message->reply("DIVIDE BY ZERO DETECTED :rotating_light: CALL THE POLICE");
        } catch (\Exception $e) {
            $message->reply("I don't know what you're trying to do here, Jimmy Neutron");
        }
    }
}