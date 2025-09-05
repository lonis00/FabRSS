<?php

include_once './feed.php';
use Discord\Builders\MessageBuilder;

function get_xml_data($url) {
    $rss = simplexml_load_string(file_get_contents($url));
    if($rss) {
        $feed_title = (string) $rss->channel->title;
        if($feed_title == "")
            $feed_title = (string) $url;
            $feed = new Feed();
            $does_feed_exist = $feed->fetch($feed_title);
            if($does_feed_exist > 0) {
                $feed_new_items = array();
                $item_counter = 0;
                foreach ($rss->channel->item as $item) {
                    $item_hash = hash('md5', $item->title);
                    if($item_hash != $feed->hash) {
                        $feed_new_items[] = array(
                            "title" => (string) $item->title,
                            "description" => (string) $item->description,
                            "link" => (string) $item->link
                        );
                    }
                    $item_counter++;
                    if($item_counter > 10)
                        break;
                }
                $rss_item = $rss->channel->item;
                return $feed_new_items;
            } else {
                return null;
            }
    } else {
        return null;
    }
}

function add_feed($url, $guild_id, $channel_id) {
    $xml_str = file_get_contents($url);
    $rss = simplexml_load_string($xml_str);
    $feed_obj = array();
    $feed = new Feed();
    if($rss) {
        $feed_title = (string) $rss->channel->title;
        if($feed_title == "")
            $feed_title = (string) $url;

        $item_counter = 0;
        foreach ($rss->channel->item as $rss_item) {
            $does_feed_exist = $feed->fetch($rss_item->title);
            $hashed_item = hash('md5', $rss_item);
            if($does_feed_exist > 0) {
                // Do nothing
                // It is already saved
                return 202; // Accepted but nothing changed
            } else {
                $feed->name = $feed_title;
                $feed->hash = $hashed_item;
                $feed->guild_id = $guild_id;
                $feed->channel_id = $channel_id;
                $feed->url = $url;
                $feed->save(false);
                return 201;
            }
            $item_counter++;
            if($item_counter > 5)
                break;
        }
    } else {
        return 404; // No xml file found
    }
}

function remove_feed($url) {
    $xml_str = file_get_contents($url);
    $rss = simplexml_load_string($xml_str);
    $feed_obj = array();
    $feed = new Feed();
    if($rss) {
        $feed_title = (string) $rss->channel->title;
        if($feed_title == "")
            $feed_title = (string) $url;

        $item_counter = 0;
        foreach ($rss->channel->item as $rss_item) {
            $does_feed_exist = $feed->fetch($feed_title);
            $hashed_item = hash('md5', $rss_item);
            if($does_feed_exist > 0) {
                // Do nothing
                // It is already saved
                $feed->delete();
                return 200;
            } else {
                // There is no feed to delete
                return 404;
            }
            $item_counter++;
            if($item_counter > 5)
                break;
        }
    } else {
        return 404; // No xml file found
    }
}

function check_for_updates($client) {
    $feed_str = file_get_contents('./feedfile.json');
    $data = json_decode($feed_str, true);
    if(count($data["feeds"]) > 0) {
        foreach ($data["feeds"] as $feed_array) {
            $xml_data = get_xml_data($feed_array["url"]);
            if($xml_data != null && count($xml_data) > 0) {
                $feed_to_update = new Feed();
                $is_fetch_correct = $feed_to_update->fetch($feed_array["name"]);
                if($is_fetch_correct > 0) {
                    $feed_to_update->hash = hash('md5', $xml_data[0]["title"]);
                    $feed_to_update->save(true);
                    print "C'est mis à jour chef\n";
                } else {
                    print "Can't fetch ".$feed_array["url"]."\n";
                }
                foreach ($xml_data as $rss_item) {
                    publish($client, $feed_array["channel_id"], $rss_item);
                }
            } else {
                print "Feed is NULL\n";
            }
        }
    }
}

function publish($discord_obj, $channel_id, $item_to_publish) {
    $feed = new Feed();
    $channel = $discord_obj->getChannel($channel_id);
    $msg_builder = MessageBuilder::new();
    $msg_builder->setContent("Nouvelle publication sur ".$item_to_publish["title"]);
    if($channel->type == 0) {
        // simple text channel
        $msg_builder->addEmbed(array(
            "title" => $item_to_publish["title"],
            "type" => "article",
            "description" => $item_to_publish["description"],
            "url" => $item_to_publish["link"],
            "color" => 16741120 // Orange
        ));
        $channel->sendMessage($msg_builder);
    } elseif($channel->type == 15 || $channel->type == 16) {
        // forum channel or media channel

        // TODO : Get categories to attache tags
        // TODO : Get content of the article to write it correctly IN the thread post
        $new_thread_options = array(
            "name" => $item_to_publish["title"],
            "auto_archive_duration" => 60, // minutes
            "message" => MessageBuilder::new()->setContent(
                $item_to_publish["description"]."\n".$item_to_publish["link"]
            ),
        );
        $channel->startThread($new_thread_options);
    }
}
?>