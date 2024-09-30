<?php

/**
 * A list of single text commands.
 * 
 * --Disable dictionary for this file: cSpell: disable
 */

return [
    
    // General
    'keeperklan'      => 'https://keeperklan.com',
    'megathread'      => 'https://www.reddit.com/r/dungeonkeeper/comments/1319ns5/dungeon_keeper_megathread/',
    'fandom'          => 'https://dungeonkeeper.fandom.com/wiki/Dungeon_Keeper_Wiki',
    'lon'             => 'https://lordsofnether.com',
    'reddit'          => 'https://www.reddit.com/r/dungeonkeeper/',
    'lemmy'           => 'https://lemmy.world/c/dungeonkeeper',

    // DK1 / KFX
    'keeperfx'        => 'https://keeperfx.net',
    'lubiki'          => 'https://lubiki.keeperklan.com/index.php',
    'unearth'         => 'https://keeperfx.net/workshop/item/1/unearth',
    'implauncher'     => 'https://keeperfx.net/workshop/item/410/implauncher-beta',
    'workshop'        => 'You can find custom KeeperFX content like custom maps, campaigns, tools, assets, and much more on the KeeperFX.net workshop: https://keeperfx.net/workshop/browse',
    'kfxrepo'         => 'https://github.com/dkfans/keeperfx',
    'buildkfx'        => 'https://keeperfx.net/wiki/building-keeperfx',
    'controls'        => 'https://keeperfx.net/wiki/new-game-controls-and-commands',
    'palette'         => 'https://github.com/dkfans/FXInfo/wiki/Palettes',
    'palettes'        => 'https://github.com/dkfans/FXInfo/wiki/Palettes',
    'music'           => 'https://keeperfx.local/workshop/item/393/keeperfx-music',
    'wiki'            => 'https://github.com/dkfans/keeperfx/wiki',
    'vscode'          => 'You can download VSCode at <https://code.visualstudio.com/>' . PHP_EOL . 'You can find the Dungeon Keeper Scripting Assistant here: <https://marketplace.visualstudio.com/items?itemName=kxvv.vscode-dk-scripting-assist>',
    'history'         => 'https://keeperfx.net/history',
    'ami'             => 'https://forums.sufficientvelocity.com/threads/dungeon-keeper-ami-sailor-moon-dungeon-keeper-story-only-thread.30066/',
    'novel'           => 'https://forums.sufficientvelocity.com/threads/dungeon-keeper-ami-sailor-moon-dungeon-keeper-story-only-thread.30066/',
    'sfx'             => 'https://github.com/dkfans/FXsounds',
    'gfx'             => 'https://github.com/dkfans/FXGraphics',
    'fxinfo'          => 'https://github.com/dkfans/FXInfo/wiki',
    'commands'        => 'https://keeperfx.net/wiki/level-script-commands',
    'script'          => 'https://keeperfx.net/wiki/level-script-commands',
    'packetsave'      => 'The `-packetsave [filename]` command line option creates a file in your game directory that stores all your input actions and the random seed used to play. You can then use `-packetload [filename]` to load the packet and see the game play back. You can frameskip to speed it up, and use alt+T to Take over and continue from any point. This is useful if you make a map and make changes, as it allows you to get back to the point where you fixed a mistake without having the play the entire map from scratch. See [here](https://github.com/dkfans/keeperfx/wiki/Command-Line-Options).',
    'packetload'      => 'The `-packetsave [filename]` command line option creates a file in your game directory that stores all your input actions and the random seed used to play. You can then use `-packetload [filename]` to load the packet and see the game play back. You can frameskip to speed it up, and use alt+T to Take over and continue from any point. This is useful if you make a map and make changes, as it allows you to get back to the point where you fixed a mistake without having the play the entire map from scratch. See [here](https://github.com/dkfans/keeperfx/wiki/Command-Line-Options).',
    'packets'         => 'The `-packetsave [filename]` command line option creates a file in your game directory that stores all your input actions and the random seed used to play. You can then use `-packetload [filename]` to load the packet and see the game play back. You can frameskip to speed it up, and use alt+T to Take over and continue from any point. This is useful if you make a map and make changes, as it allows you to get back to the point where you fixed a mistake without having the play the entire map from scratch. See [here](https://github.com/dkfans/keeperfx/wiki/Command-Line-Options).',

    // DK2
    'openkeeper'      => 'https://github.com/tonihele/OpenKeeper',
    'gim'             => 'https://keeperklan.com/downloads.php?do=file&id=109',
    'ember'           => 'https://github.com/DiaLight/Ember',
    'flame'           => 'https://github.com/DiaLight/Flame',

    // Meme
    'walter'          => ':flag_fr: :french_bread: :croissant: hon hon',
    'rap'             => 'https://www.youtube.com/watch?v=vH67duada9E',
    'dkrap'           => 'https://www.youtube.com/watch?v=vH67duada9E',
    'germanrap'       => 'https://www.youtube.com/watch?v=xXkfyGQsjAk',
    'kiss'            => '_**\*smooch\***_ :kiss:',
    'smooch'          => '_**\*smooch\***_ :kiss:',

    // Bugs
    'lvl10spellbug'   => "> **Level 10 spell bug:** once a creature reaches level 10, creature spells such as Speed and Armour no longer work for its entire duration. Because of this, creatures who get these before level 10 become much weaker overall (e.g. level 9 Samurai are much more dangerous than level 10 ones). This happens because these spells are cast at the creature's experience level but spells only have 9 levels. The game looks for the level 10 value but ends up reading the next entry in the data (possibly the level 1 entry for the next spell). This affects many spells, but is particularly noticeable with Speed. Fixed in KeeperFX.",
    'overflowbug'     => "> **Overflow of 8bit values:** several values in the game that would increase past 255 (or eight ones in binary) would continue counting back at 0. An example is the Dexterity value for units: when, for instance, a Horned Reaper would level up enough for his Dexterity to grow past 255, he would go from a unit that could deal out consistent damage to one that would hardly ever land a hit. This is one of the reasons level 6 Horned Reapers are more powerful than level 10 ones, the other being the abovementioned level 10 spell bug. Fixed in KeeperFX.",

];