<?php

include_once './feed.php';

function download_feed_file($url) {
    if(!isset($url)) {
        return 400; // Bad request 
    }
    if(filter_var($url, FILTER_VALIDATE_URL)) {
        $curl_sys = curl_init($url);
        $xml_file = fopen('./xml_files/fetched_file.xml', 'w');
        curl_setopt($curl_sys, CURLOPT_FILE, $xml_file);
        curl_setopt($curl_sys, CURLOPT_HEADER, 0);
        curl_exec($curl_sys);
        if(curl_error($curl_sys)) {
            fwrite($xml_file, curl_error($curl_sys));
            return 401; // Can not write in the file
        }
        curl_close($curl_sys);
        fclose($xml_file);
        return 200;
    } else {
        return 400; // Invalid URL
    }
}

function add_feed($url, $guild_id, $channel_id) {
    $response_fetch_xml = download_feed_file($url);
    if($response_fetch_xml == 200)
    {
        $rss = simplexml_load_file('./xml_files/fetched_file.xml');
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
    } else {
        return $response_fetch_xml;
    }
}
?>