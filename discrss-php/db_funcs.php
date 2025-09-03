<?php

include_once './feed.php';

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
            print "Does feed exist ? ".$does_feed_exist."\n";
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
?>