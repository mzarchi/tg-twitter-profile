<?php


class Methods
{
    private static $m;
    private static $db;
    private static $tg;
    private static $apt;

    public static function getInstance()
    {
        if (self::$m == null) {
            self::$m = new Methods();
        }
        return self::$m;
    }

    public function __construct()
    {
        self::$db = Database::getInstance();
        self::$tg = Telegram::getInstance();
        self::$apt = Apitter::getInstance();
    }

    public function setStartMessage($chat_id, $text)
    {
        self::$tg->setChatAction($chat_id);
        self::$db->insertUserData($chat_id);
        if (strlen($text) > 7) {
            $data = str_replace('/start ', '', $text);
            $type = "un";
            if (is_numeric($data))
                $type = "id";
            $this->userIdLookUp($chat_id, $type, $data);
        } else {
            $text = "Please send param likes:\n";
            $text .= "- 12 (Twitter user ID)\n";
            $text .= "- jack (Twitter username)\n";
            self::$tg->sendMessage($chat_id, $text);
        }
    }

    public function setTwitterTimeArray($unixtime)
    {
        $result = array();
        $age = time() - $unixtime;
        $result['d'] = number_format(floor($age / 86400));
        $result['h'] = $this->setNumber(floor(($age % 86400) / 3600));
        $result['i'] = $this->setNumber(floor(($age % 3600) / 60));
        $result['time'] = date('H:i:s', $unixtime);
        $result['date'] = date('Y-m-d', $unixtime);
        $result['ts'] = $age;
        return $result;
    }

    public function setUserMessageBody($value)
    {
        $result = array();
        $data = json_decode(self::$apt->get_user_data($value));

        if ($data->code == 200) {
            $ud = $data->data;
            $result['status'] = true;
            $part01 = "Twitter ID: <code>" . $ud->user_id . "</code> \n";
            $part01 .= "Username: <code>" . $ud->screen_name . "</code> \n";
            $part01 .= "Name: " . $ud->name . " \n";
            $part01 .= "Bio: " . $ud->bio . "\n";
            $part01 .= "Link: " . $ud->url . "\n";
            $part01 .= "Location: <code>" . $ud->location . "</code> \n";
            $part01 .= "Following: " . number_format($ud->friends_count) . " \n";
            $part01 .= "Followers: " . number_format($ud->followers_count) . " \n";
            $part01 .= "Listed: " . number_format($ud->listed_count) . " \n";
            $part01 .= "Tweets: " . number_format($ud->tweet_count) . " \n";
            $part01 .= "Favorite: " . number_format($ud->fav_count) . "\n";
            $part01 .= "Verified: " . ($ud->blue_verified == true ? "Yes" : "No") . "\n";

            $time = $this->setTwitterTimeArray($ud->created_at);
            $part01 .= "Created at: " . $time['d'] . " days, " . $time['h'] . ":" . $time['i'] . " ago\n";
            $part01 .= "· <code>" . $time['date'] . ", " . $time['time'] . "</code> UTC\n \n";

            $part02 = "Twitter ID: #i" . $ud->user_id . "\n";
            $part02 .= "Check again: <a href='https://t.me/TwitterProfileBot?start=" . $ud->user_id . "'>StartBot</a> · ";
            $part02 .= "Show <a href='https://twitter.com/intent/user?user_id=" . $ud->user_id . "'>Profile</a> \n\n";

            $part03 = "Username: #" . $ud->screen_name . "\n";
            $part03 .= "Check again: <a href='https://t.me/TwitterProfileBot?start=" . $ud->screen_name . "'>StartBot</a> · ";
            $part03 .= "Show <a href='https://twitter.com/intent/user?screen_name=" . $ud->screen_name . "'>Profile</a> \n\n";

            $result['data'][1] = $part01;
            $result['data'][2] = $part02;
            $result['data'][3] = $part03;
            $result['btn']['id'] = $ud->user_id;
            $result['btn']['user'] = $ud->screen_name;
            $result['profile'] = str_replace("_normal", "", $ud->avatar_url);
        } else {
            $result['status'] = false;
            $result['data'] = null;
        }
        return $result;
    }

    public function userIdLookUp($chatId, $text)
    {
        $data = $this->setUserMessageBody($text);
        if ($data['status']) {
            $user_msg = $data['data'][1];
            $user_msg .= $data['data'][2];
            $user_msg .= $data['data'][3];
            $user_msg .= 'Prepared by: @TwitterProfileBot';
            self::$tg->sendUserInfoMessageButton(
                $chatId,
                $data['profile'],
                $user_msg,
                $data['btn']['id'],
                $data['btn']['user']
            );
        } else {
            $message = "Entrance: <code>" . $text . "</code> \n";
            $message .= "User not found!";
            $body[0]['text'] = "Check Again";
            $body[0]['callback_data'] = $text;
            $buttons = array('body' => $body, 'bodyVertical' => 1);
            self::$tg->sendInlineKeyboard($chatId, $message, "text", null, $buttons);
        }
    }

    public function statistic($chat_id)
    {
        $msg = "Bot Name: <b>Twitter Profile</b>\n";
        $msg .= "Username: @TwitterProfileBot\n";
        $msg .= "Members: 2,153\n";
        $msg .= "- Active: 2,153 (100%)\n";
        $msg .= "- Sleep: 0 (0%)\n";
        $msg .= "- Dead: 0 (0%)\n";
        $msg .= "Created: 336 day(s), 4 hour(s) ago.\n";
        $msg .= "- Time: 10:17:39 UTC\n";
        $msg .= "- Date: 2022-01-23\n";
        $msg .= "Channel: @MohammadZarchi (Fa-IR)\n";
    }

    public function setNumber($number)
    {
        if ($number > 9) {
            return $number;
        } else {
            return "0" . $number;
        }
    }
}
