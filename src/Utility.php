<?php

namespace Yani\KeeperBot;

use Discord\Parts\Channel\Message;
use React\Http\Browser;

class Utility
{

    public static function getAuthorTagFromMessage(Message $message)
    {
        return '<@' . $message->author->id . '>';
    }

    public static function combineParameters(array $parameters)
    {
        return \implode(' ', $parameters);
    }

    public static function reactRaisedEyebrowFaceToMessage(Message $message)
    {
        $message->react("🤨");
    }

    public static function createBrowserInstance(): Browser
    {
        return new Browser(
            new \React\Socket\Connector([
                'tls' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ],
            ])
        );
    }
}