<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;
use Psr\Http\Message\ResponseInterface;

use Yani\KeeperBot\Utility;


class CatCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => 'cat',
            'has_parameters' => false,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        // Get browser
        $browser = Utility::createBrowserInstance();

        // Async request
        $browser->get("https://cataas.com/cat")
            ->then(function (ResponseInterface $response) use ($message, $discord) {

                // Make sure this the page returned something
                if ($response->getStatusCode() === 404) {
                    $message->reply('Cat not found');
                    return;
                }

                // Get body of response
                $cat_image = $response->getBody();
                if (empty($cat_image)) {
                    $message->reply('Cat not found');
                    return;
                }

                // Get file type
                $content_type = $response->getHeader('content-type')[0];
                $file_extension = \explode('/', $content_type)[1];

                // Create a local temp image file
                $filename = tempnam(sys_get_temp_dir(), 'cat_') . '.' . $file_extension;
                \file_put_contents($filename, $cat_image);

                // Send message
                $message->channel->sendMessage(
                    MessageBuilder::new()->addFile($filename)
                );

                // Remove the temp file
                \unlink($filename);
            });
    }
}
