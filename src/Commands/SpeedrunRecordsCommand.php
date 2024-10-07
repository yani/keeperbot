<?php

namespace Yani\KeeperBot\Commands;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Builders\MessageBuilder;

use Yani\KeeperBot\CommandInterface;
use Yani\KeeperBot\Utility;
use Psr\Http\Message\ResponseInterface;
use Discord\Parts\Embed\Embed;

class SpeedrunRecordsCommand implements CommandInterface
{
    public function getCommandConfig(): array
    {
        return [
            'command'        => ['speedrun', 'records', 'record'],
            'has_parameters' => true,
        ];
    }

    public function handleCommand(Discord $discord, Message $message, array $parameters = []): void
    {
        $param = Utility::combineParameters($parameters);

        // Setup variables
        $game_name = null;
        $game = null;
        $category = null;
        if(\in_array(\strtolower($param), ['1', 'dk1', 'kfx', 'keeperfx', 'dungeon keeper', 'dungeon keeper 1', 'dungeonkeeper', 'dungeonkeeper1'])){
            $game_name = 'Dungeon Keeper 1';
            $game = 'dk1';
            $category_name = 'Full game - Any%';
            $category = 'Full_game_any';
        } else if(\in_array(\strtolower($param), ['2', 'dk2', 'dungeonkeeper2', 'dungeon keeper 2'])){
            $game_name = 'Dungeon Keeper 2';
            $game = 'dk2';
            $category_name = 'Full game - Any%';
            $category = 'ndxjo852';
        } else {
            $message->reply("Invalid <game>. Choose between: `dk1`, `dk2`");
            return;
        }

        // Create the request
        $browser = Utility::createBrowserInstance();
        $browser->get("https://www.speedrun.com/api/v1/leaderboards/{$game}/category/{$category}?embed=variables,players")
            ->then(function (ResponseInterface $response) use ($message, $discord, $game_name, $game, $category_name) {

                // Get body of response
                $body = (string)$response->getBody();
                if (empty($body)) {
                    $message->reply("Failed to connect to Speedrun.com API...");
                    return;
                }

                // Decode JSON
                $json = \json_decode($body, true);
                if (empty($json) || !is_array($json) || !isset($json['data']) || !is_array($json['data'])) {
                    $message->reply("Invalid server response...");
                    return;
                }

                if($game == 'dk1')
                {

                    // Get 'Version' data
                    $version_data = $json['data']['variables']['data'][0] ?? null;
                    if($version_data == null){
                        $message->reply("Failed to get variable data for this Speedrun.com game entry...");
                        return;
                    }

                    // Make sure the version data is for 'Version'
                    if($version_data['id'] !== 'yn2vj2j8'){
                        $message->reply("First variable is not the expected 'yn2vj2j8' on Speedrun.com game entry...");
                        return;
                    }

                    // Get versions
                    $versions = $version_data['values']['choices'];

                }

                // Get player data
                $player_data = $json['data']['players']['data'] ?? null;
                if(!\is_array($player_data)){
                    $message->reply("Failed to get player data for this Speedrun.com game entry...");
                    return;
                }

                // Get players
                $players = [];
                foreach($player_data as $player)
                {
                    $country_code = $player['location']['country']['code'] ?? null;
                    if($country_code == null || \strlen($country_code) != 2){
                        $country_code = null;
                    }

                    $players[$player['id']] = [
                        'name'    => \str_replace(['_', '*', '.'], ['\\_', '\\*', '\\.'], $player['names']['international']),
                        'url'     => $player['weblink'],
                        'country' => $country_code,
                    ];
                }

                // Create description
                $description = '';
                $description .= $category_name . PHP_EOL . PHP_EOL;
                // $description .= "`{$category_name}`" . PHP_EOL . PHP_EOL;

                // Loop trough runs
                foreach($json['data']['runs'] as $run)
                {
                    // Get player 
                    $player = $players[
                        $run['run']['players'][0]['id']
                    ];

                    // Show medal
                    if($run['place'] === 1) {
                        $description .= ':first_place:';
                    } else if ($run['place'] === 2) {
                        $description .= ':second_place:';
                    } else if ($run['place'] === 3) {
                        $description .= ':third_place:';
                    } else {
                        $description .= '#' . $run['place'] . ' - ';
                    }
                    $description .= ' ';
                    
                    // Show possible flag
                    if($player['country'] !== null)
                    {
                        $description .= ':flag_' . $player['country'] . ':';
                        $description .= ' ';
                    }

                    // Show username
                    $description .= '**' . $player['name'] . '**';

                    // Show time + link
                    $time = $run['run']['times']['primary_t'];
                    $time_string = \gmdate("H:i:s", $time);
                    $description .= " - [**{$time_string}**]({$run['run']['weblink']})";
                    $description .= PHP_EOL;
                    
                    // Add submitted date
                    $submitted = new \DateTime($run['run']['date']);
                    $description .= "<t:{$submitted->getTimestamp()}:R>";
                    
                    // Add version
                    // DK2 has no version at the moment
                    if($game == 'dk1')
                    {
                        $description .= ' - ';
                        $description .= $versions[$run['run']['values']['yn2vj2j8']];
                    }

                    // Break after 5th
                    if($run['place'] == 5) {
                        break;
                    } else {
                        $description .= PHP_EOL . PHP_EOL;
                    }
                }

                // Create embed for the alpha patch
                $embed = new Embed($discord, [
                    'title'       => "{$game_name} - Speedrun Records",
                    'description' => $description,
                    'color'       => 14484741, // DD0505
                ]);

                // Send the embed as a message to the user
                $message->channel->sendEmbed($embed);
            });

    }
}