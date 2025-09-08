<?php

include 'vendor/autoload.php';
include './lib.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Discord\Discord;
// use Discord\Parts\Channel\Message;
// use Discord\WebSockets\Intents;
// use Discord\DiscordCommandClient;
use Discord\Builders\MessageBuilder;
use Discord\Builders\CommandBuilder;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\interaction;
use Discord\Parts\Interactions\Command\Option;

// $discord = new DiscordCommandClient([
$discord = new Discord([
    'token' => $_ENV['DISCORD_BOT_TOKEN'],
    // 'prefix' => $_ENV['COMMAND_PREFIX'],
    // 'discordOptions' => [
    //     'intents' => Intents::getDefaultIntents() | Intents::MESSAGE_CONTENT,
    // ],
]);

function add_feed_cmd($client, $feed_url, $channel) {
    $response = "";
    $response_status = add_feed($feed_url, $channel->guild->id, $channel->id);
    if($response_status == 404)
        $response = "Le fichier XML n'a pas été trouvé, verifiez votre URL";
    elseif($response_status == 201)
        $response = "Flux enregistré ✅, les nouveaux postes seront postés dans le canal <#".$channel->id.">";
    elseif($response_status == 202)
        $response = "Flux déjà enregistré, aucun changement.";
    return $response;
}

function call_remove_feed($url) {
    $response_status = remove_feed($url);
    if($response_status == 404)
        $response = "Le fichier XML ou le flux n'ont pas été trouvé, verifiez votre URL.";
    elseif($response_status == 200)
        $response = "Flux supprimé 🗑️.";
    return $response;
}

function register_commands($client) {
    $commands = array(
        "addfeed" => $client->application->commands->create(
            CommandBuilder::new()
                ->setName('addfeed')
                ->setDescription('Save a new RSS feed to follow and where the posts will be.')
                ->addOption((new Option($client))
                    ->setName('url')
                    ->setDescription('URL of the feed to follow')
                    ->setType(Option::STRING)
                    ->setRequired(true)
                )
                ->addOption((new Option($client))
                    ->setName('channel')
                    ->setDescription('Channel where news will be post')
                    ->setType(Option::CHANNEL)
                    ->setRequired(true)
                )
                ->toArray()
        ),
        "removefeed" => $client->application->commands->create(
            CommandBuilder::new()
                ->setName('removefeed')
                ->setDescription('Remove a saved RSS feed but not the posts in the channel.')
                ->addOption((new Option($client))
                    ->setName('url')
                    ->setDescription('URL of the feed to remove')
                    ->setType(Option::STRING)
                    ->setRequired(true)
                )
                ->toArray()
        ),
        "ping" => $client->application->commands->create(CommandBuilder::new()
            ->setName('ping')
            ->setDescription('Will respond if it can.')
            ->toArray()
        ),
    );

    foreach ($commands as $cmd_name => $cmd) {
        $client->application->commands->save($cmd);
        print "Command '".$cmd_name."' saved \n";
    }
}


$discord->on('init', function(Discord $discord){
    echo "Bot is ready!", PHP_EOL;

    print "Preparing commands\n";
    register_commands($discord);

    $discord->listenCommand('addfeed', function(interaction $interaction) {
        $url = $interaction->data->options->offsetGet('url')->value;
        $channel = $interaction->data->resolved->channels->first();
        $response_message = add_feed_cmd($discord, $url, $channel);
        $interaction->respondWithMessage(MessageBuilder::new()->setContent($response_message));
    });

    $discord->listenCommand('removefeed', function(interaction $interaction) {
        print "Feed à supprimer !\n";
        $url = $interaction->data->options->offsetGet('url')->value;
        print "URL = ".$url."\n";

        $function_response = call_remove_feed($url);
        $interaction->respondWithMessage(MessageBuilder::new()->setContent($function_response));
    });


    $discord->listenCommand('ping', function(interaction $interaction) {
        $interaction->respondWithMessage(MessageBuilder::new()->setContent('Pong!'));
    });

});

$discord->run();