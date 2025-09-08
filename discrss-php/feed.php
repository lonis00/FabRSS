<?php

class Feed {
    public string $name;
    public string $hash;
    public string $channel_id;
    public string $guild_id;
    public string $url;


    public function fetch($name){
        $json_as_string = file_get_contents('./feedfile.json');
        $data = json_decode($json_as_string, true);
        if(count($data["feeds"]) > 0) {
            foreach ($data["feeds"] as $feed_object) {
                if($name == $feed_object["name"]){
                    $this->name = $feed_object["name"];
                    $this->hash = $feed_object["hash"];
                    $this->url = $feed_object["url"];
                    $this->channel_id = $feed_object["channel_id"];
                    $this->guild_id = $feed_object["guild_id"];
                    return 1;
                }
            }
            return -1;
        } else {
            return -1;
        }
    }

    public function check_if_exist() {
        $feed_str = file_get_contents('./feedfile.json');
        $data = json_decode($feed_str, true);
        foreach ($data["feeds"] as $feed_name => $feed_object) {
            if($this->name == $feed_name){
                return 1;
            }
        }
        return -1;
    }

    public function to_array() {
        return array(
            "name" => $this->name,
            "hash" => $this->hash,
            "channel_id" => $this->channel_id,
            "guild_id" => $this->guild_id,
            "url" => $this->url
        );
    }

    public function save($already_exist) {
        $feed_str = file_get_contents('./feedfile.json');
        $data = json_decode($feed_str, true);
        if($already_exist) {
            $new_data = array();
            foreach ($data["feeds"] as $feed_object) {
                if($this->name == $feed_object["name"]){
                    $feed_object["hash"] = $this->hash;
                    $feed_object["channel_id"] = $this->channel_id;
                    $feed_object["guild_id"] = $this->guild_id;
                    $feed_object["url"] = $this->url;
                }
                $new_data["feeds"][] = $feed_object;
            }
            $data = $new_data;
        } else {
            $data["feeds"][] = $this->to_array();
        }
        
        $feed_file = fopen('./feedfile.json', 'w') or die("Unable to open the file for saving");
        $json_data = json_encode($data, JSON_PRETTY_PRINT);
        fwrite($feed_file, $json_data);
        fclose($feed_file);
        return 1;
    }
}
?>