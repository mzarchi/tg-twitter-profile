<?php


class Telegram
{

    private static $tg;
    private static $jsonData;
    private $column = 0;
    private $btnArrayName = array();

    public static function getInstance($json = null)
    {
        if (self::$tg == null) {
            self::$tg = new Telegram($json);
        }
        return self::$tg;
    }

    private function __construct($json = null)
    {
        if ($json != null) {
            self::$jsonData = json_decode($json);
        }
    }

    public function getChatId()
    {
        if (isset(self::$jsonData->callback_query)) {
            return self::$jsonData->callback_query->message->chat->id;
        } else {
            if (isset(self::$jsonData->edited_message->chat->id))
                return self::$jsonData->edited_message->chat->id;
            else
                return self::$jsonData->message->chat->id;
        }
    }

    public function getFirstName()
    {
        if (isset(self::$jsonData->callback_query))
            return self::$jsonData->callback_query->from->first_name;
        elseif (isset(self::$jsonData->message->from->first_name))
            return self::$jsonData->message->from->first_name;
        else
            return null;
    }

    public function getLastName()
    {
        if (isset(self::$jsonData->callback_query))
            return self::$jsonData->callback_query->from->last_name;
        elseif (isset(self::$jsonData->message->from->last_name))
            return self::$jsonData->message->from->last_name;
        else
            return null;
    }

    public function getUsername()
    {
        if (isset(self::$jsonData->callback_query))
            return "@" . self::$jsonData->callback_query->from->username;
        elseif (isset(self::$jsonData->message->from->username))
            return "@" . self::$jsonData->message->from->username;
        else
            return null;
    }

    public function getMessageText()
    {
        if (isset(self::$jsonData->callback_query)) {
            return self::$jsonData->callback_query->data;
        } else {
            if (isset(self::$jsonData->edited_message->text))
                return self::$jsonData->edited_message->text;
            else
                return self::$jsonData->message->text;
        }
    }

    public function getContact()
    {
        if (isset(self::$jsonData->message->contact)) {
            return true;
        } else {
            return false;
        }
    }

    public function getMessageType()
    {
        $result = null;
        if (isset(self::$jsonData->message->audio))
            $result = "audio";
        elseif (isset(self::$jsonData->message->document))
            $result = "document";
        elseif (isset(self::$jsonData->message->photo))
            $result = "photo";
        elseif (isset(self::$jsonData->message->video))
            $result = "video";
        elseif (isset(self::$jsonData->message->voice))
            $result = "voice";
        elseif (isset(self::$jsonData->message->contact))
            $result = "contact";
        elseif (isset(self::$jsonData->message->sticker))
            $result = "sticker";
        elseif (isset(self::$jsonData->message->text) || isset(self::$jsonData->callback_query->data))
            $result = "text";
        return $result;
    }

    public function getFileId($fileName = "photo")
    {
        $fileId = null;
        $message = self::$jsonData->message;
        if ($fileName == "photo") {
            for ($i = 5; $i >= 0; $i--)
                if (isset($message->photo[$i]->file_id)) {
                    $fileId = $message->photo[$i]->file_id;
                    break;
                }
        } else
            $fileId = $message->$fileName->file_id;
        return $fileId;
    }

    public function getContactData()
    {
        $data = array();
        $data[0] = self::$jsonData->message->contact->user_id;
        $data[2] = self::$jsonData->message->contact->first_name;
        $data[3] = self::$jsonData->message->contact->last_name;
        $data[1] = self::$jsonData->message->contact->phone_number;
        $data[1] = strstr($data[1], '+') == true ? $data[1] : "+" . $data[1];
        return $data;
    }

    public function getLocation()
    {
        if (isset(self::$jsonData->message->location)) {
            return true;
        } else {
            return false;
        }
    }

    public function getLocationData()
    {
        $data = array();
        $data['lat'] = self::$jsonData->message->location->latitude;
        $data['lon'] = self::$jsonData->message->location->longitude;
        return $data;
    }

    public function isPhoto()
    {
        if (isset(self::$jsonData->message->photo)) {
            return true;
        } else {
            return false;
        }
    }

    public function isChannelPost()
    {
        $result = false;
        if (isset(self::$jsonData->channel_post))
            $result = true;
        return $result;
    }

    public function isEditChannelPost()
    {
        $result = false;
        if (isset(self::$jsonData->edited_channel_post))
            $result = true;
        return $result;
    }

    public function isPinnedMessage()
    {
        $result = false;
        if (isset(self::$jsonData->message->pinned_message))
            $result = true;
        return $result;
    }

    public function getMessageId($json = null)
    {
        $data = ($json == null ? self::$jsonData : $json);
        if (isset($data->callback_query->message->message_id)) {
            return $data->callback_query->message->message_id;
        } else {
            return $data->result->message_id;
        }
    }

    public function sendMessage($chatId, $message, $replyMarkup = null)
    {
        $message = urlencode($message);
        $url = "https://api.telegram.org/bot" . _BOT_TOKEN;
        $url .= "/sendMessage?chat_id=" . $chatId;
        $url .= "&text=" . $message;
        $url .= "&parse_mode=html";
        $url .= "&disable_web_page_preview=true";
        if ($replyMarkup == "ReplyKeyboardRemove") {
            $removeKeyboard = array('remove_keyboard' => true);
            $removeKeyboardEncoded = json_encode($removeKeyboard);
            $url .= "&reply_markup=" . $removeKeyboardEncoded;
        }
        return file_get_contents($url);
    }

    public function sendVideo($chatId, $fileId, $caption = null)
    {
        $url = "https://api.telegram.org/bot" . _BOT_TOKEN;
        $url .= "/sendVideo?chat_id=" . $chatId;
        $url .= "&video=" . $fileId;
        if ($caption != null) {
            $caption = urlencode($caption);
            $url .= "&caption=" . $caption;
            $url .= "&parse_mode=html";
        }
        return file_get_contents($url);
    }

    public function sendReplyMessage($chatId, $replyId, $message)
    {
        $message = urlencode($message);
        $url = "https://api.telegram.org/bot" . _BOT_TOKEN;
        $url .= "/sendMessage?chat_id=" . $chatId;
        $url .= "&text=" . $message;
        $url .= "&reply_to_message_id=" . $replyId;
        $url .= "&parse_mode=html";
        return file_get_contents($url);
    }

    public function setButtonKeyboard($chatId, $message, $buttons, $vertical)
    {
        $btnArrayName = array();
        for ($i = 0, $a = 0, $b = 0; $i < sizeof($buttons); $i++, $b++) {
            if ($b == $vertical) {
                $b = 0;
                $a++;
            }
            $btnArrayName[$a][$b]['text'] = $buttons[$i]['_name'];
        }

        $keyboard = array("keyboard" => $btnArrayName, "one_time_keyboard" => true, "resize_keyboard" => true);

        $postFields = array('chat_id' => $chatId, 'text' => $message, 'reply_markup' => json_encode($keyboard));

        $url = "https://api.telegram.org/bot" . _BOT_TOKEN . "/sendMessage";

        if (!$curId = curl_init()) {
            exit;
        }

        curl_setopt($curId, CURLOPT_POST, true);
        curl_setopt($curId, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($curId, CURLOPT_URL, $url);
        curl_setopt($curId, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curId);
        curl_close($curId);
    }

    public function deleteMessage($chatId, $messageId)
    {
        $url = "https://api.telegram.org/bot" . _BOT_TOKEN;
        $url .= "/deleteMessage?chat_id=" . $chatId;
        $url .= "&message_id=" . $messageId;
        file_get_contents($url);
    }

    public function sendInlineKeyboard($chatId, $message, $fileType, $fileId, $buttons, $return = false)
    {
        /*
         * row = سطر
         * column = ستون
         * $btnArrayName[ستون][سطر]
         * $btnArrayName[column][row]['text'] = ...
         * $btnArrayName[column][row]['callback_data'] = ...
         */

        /*
         * ╔╗ ╔╗       ╔╗
         * ║║ ║║       ║║
         * ║╚═╝╠══╦══╦═╝╠══╦═╗
         * ║╔═╗║║═╣╔╗║╔╗║║═╣╔╝
         * ║║ ║║║═╣╔╗║╚╝║║═╣║
         * ╚╝ ╚╩══╩╝╚╩══╩══╩╝
         */
        if (isset($buttons['header'])) {
            $this->sendInlineKeyboardHelper($buttons, 'header');
            $this->column += 1;
        }

        /*
         * ╔══╗     ╔╗
         * ║╔╗║     ║║
         * ║╚╝╚╦══╦═╝╠╗ ╔╗
         * ║╔═╗║╔╗║╔╗║║ ║║
         * ║╚═╝║╚╝║╚╝║╚═╝║
         * ╚═══╩══╩══╩═╗╔╝
         *           ╔═╝║
         *           ╚══╝
         */
        if (isset($buttons['body'])) {
            $this->sendInlineKeyboardHelper($buttons, 'body');
            $this->column += 1;
        }

        /*
         * ╔═══╗     ╔╗
         * ║╔══╝    ╔╝╚╗
         * ║╚══╦══╦═╩╗╔╬══╦═╗
         * ║╔══╣╔╗║╔╗║║║║═╣╔╝
         * ║║  ║╚╝║╚╝║╚╣║═╣║
         * ╚╝  ╚══╩══╩═╩══╩╝
         */
        if (isset($buttons['footer'])) {
            $this->sendInlineKeyboardHelper($buttons, 'footer');
        }

        $inlineKeyboard = array("inline_keyboard" => $this->btnArrayName);

        $text = urlencode($message);
        $inlineKeyboard = json_encode($inlineKeyboard);
        $url = "https://api.telegram.org/bot" . _BOT_TOKEN;

        switch ($fileType) {
            case "text":
                $url .= "/sendMessage?chat_id=" . $chatId;
                $url .= "&text=" . $text;
                break;

            case "photo":
                $url .= "/sendPhoto?chat_id=" . $chatId;
                $url .= "&photo=" . $fileId;
                $url .= "&caption=" . $text;
                break;

            case "animation":
                $url .= "/sendAnimation?chat_id=" . $chatId;
                $url .= "&animation=" . $fileId;
                $url .= "&caption=" . $text;
                break;
        }

        $url .= "&reply_markup=" . $inlineKeyboard;
        $url .= "&parse_mode=html";
        $url .= "&disable_web_page_preview=true";
        $result = file_get_contents($url);
        $this->column = 0;

        if ($return)
            return $result;
    }

    private function sendInlineKeyboardHelper($buttons, $part)
    {
        $button = $buttons[$part];
        $buttonVertical = $buttons[$part . 'Vertical'];
        for ($i = 0, $row = 0; $i < sizeof($button); $i++, $row++) {
            $btn = $button[$i];

            if ($row == $buttonVertical) {
                $row = 0;
                $this->column++;
            }

            $this->btnArrayName[$this->column][$row]['text'] = $btn['text'];
            $this->btnArrayName[$this->column][$row]['callback_data'] = $btn['callback_data'];
        }
    }

    public function setChatAction($chatId, $action = "typing")
    {
        /* typing for text messages
         * upload_photo for photos
         * upload_video for videos
         * record_video for video recording
         * upload_audio for audio files
         * record_audio for audio file recording
         * upload_document for general files
         * find_location for location data
         * upload_video_note for video notes
         * record_video_note for video note recording */

        $url = "https://api.telegram.org/bot" . _BOT_TOKEN;
        $url .= "/sendChatAction?chat_id=" . $chatId;
        $url .= "&action=" . $action;
        return file_get_contents($url);
    }

    public function isReplyMessage()
    {
        if (isset(self::$jsonData->message->reply_to_message))
            return true;
        else
            return false;
    }

    public function getReplyMessageId()
    {
        return self::$jsonData->message->reply_to_message->message_id;
    }

    public function setKeyboardData($chatId, $type, $message)
    {
        $url = "https://api.telegram.org/bot" . _BOT_TOKEN . "/sendMessage";
        $data = null;
        $nestedKeyboard = null;
        switch ($type) {
            case "phone":
                $nestedKeyboard = array(array("text" => "ارسال شماره تلفن", "request_contact" => true));
                break;

            case "place":
                $nestedKeyboard = array(array("text" => "موقعیت من", "request_location" => true));
                break;
        }

        $keyboard = array(
            "keyboard" => array($nestedKeyboard),
            "one_time_keyboard" => true,
            "resize_keyboard" => true
        );

        $postFields = array(
            'chat_id' => $chatId,
            'text' => $message,
            'reply_markup' => json_encode($keyboard)
        );

        if (!$curId = curl_init()) {
            exit;
        }

        curl_setopt($curId, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($curId, CURLOPT_URL, $url);
        curl_exec($curId);
        curl_close($curId);
    }

    public function sendLocation($chatId, $latitude, $longitude)
    {
        $url = "https://api.telegram.org/bot" . _BOT_TOKEN;
        $url .= "/sendLocation?chat_id=" . $chatId;
        $url .= "&latitude=" . $latitude;
        $url .= "&longitude=" . $longitude;
        return file_get_contents($url);
    }

    public function sendContact($chatId, $family, $number)
    {
        $url = "https://api.telegram.org/bot" . _BOT_TOKEN;
        $url .= "/sendContact?chat_id=" . $chatId;
        $url .= "&phone_number=" . $number;
        $url .= "&first_name=" . $family;
        return file_get_contents($url);
    }

    public function editMessage($chatId, $messageId, $text)
    {
        $text = urlencode($text);
        $url = "https://api.telegram.org/bot" . _BOT_TOKEN;
        $url .= "/editMessageText?chat_id=" . $chatId;
        $url .= "&message_id=" . $messageId;
        $url .= "&text=" . $text;
        $url .= "&parse_mode=html";
        file_get_contents($url);
    }

    public function sendEditMessage($chatId, $text, $messageId, $username)
    {
        $keyboardArray = array(array(array("text" => "Check Again", "callback_data" => "LimitShadow-" . $username)));

        $inlineKeyboard = array(
            "inline_keyboard" => $keyboardArray
        );
        $text = urlencode($text);
        $url = "https://api.telegram.org/bot" . _BOT_TOKEN;
        $url .= "/editMessageText?chat_id=" . $chatId;
        $url .= "&message_id=" . $messageId;
        $url .= "&text=" . $text;
        $url .= "&disable_web_page_preview=true";
        $url .= "&reply_markup=" . json_encode($inlineKeyboard);
        $url .= "&parse_mode=html";
        return file_get_contents($url);
    }

    public function setDescription($chatId, $text)
    {
        $url = "https://api.telegram.org/bot" . _BOT_TOKEN;
        $url .= "/setChatDescription?chat_id=" . $chatId;
        $url .= "&description=" . urlencode($text);
        file_get_contents($url);
    }

    public function sendPhoto($chatId, $fileId, $caption)
    {
        $text = urlencode($caption);
        $url = "https://api.telegram.org/bot" . _BOT_TOKEN;
        $url .= "/sendPhoto?chat_id=" . $chatId;
        $url .= "&photo=" . $fileId;
        $url .= "&caption=" . $text;
        $url .= "&parse_mode=html";
        return file_get_contents($url);
    }

    public function sendUserInfoMessageButton($chatId, $fileId, $message, $id, $username)
    {
        $keyboardArray = array(
            /*
            array(
                array("text" => "Open Similar Accounts", "url" => "https://twitter.com/i/connect_people?user_id=" . $id),
            ),*/
            array(
                array("text" => "Open Twitter-Id", "url" => "https://twitter.com/intent/user?user_id=" . $id),
                array("text" => "Open Username", "url" => "https://twitter.com/intent/user?screen_name=" . $username)
            ),
            array(
                array("text" => "Check Twitter-Id", "callback_data" => $id),
                array("text" => "Check Username", "callback_data" => $username)
            ),
            array(
                array("text" => "Link Twitter-Id", "callback_data" => "getlink-id-" . $id),
                array("text" => "Link Username", "callback_data" => "getlink-username-" . $username)
            ),
        );

        $inlineKeyboard = array("inline_keyboard" => $keyboardArray);
        $text = urlencode($message);
        $url = "https://api.telegram.org/bot" . _BOT_TOKEN;
        $url .= "/sendPhoto?chat_id=" . $chatId;
        $url .= "&photo=" . $fileId;
        $url .= "&caption=" . $text;


        //$url .= "/sendMessage?chat_id=" . $chatId;
        //$url .= "&text=" . $text;
        $url .= "&disable_web_page_preview=true";
        $url .= "&reply_markup=" . json_encode($inlineKeyboard);
        $url .= "&parse_mode=html";
        return file_get_contents($url);
    }

    public function sendLinkButton($chatId, $text, $url, $next)
    {
        $keyboardArray = array(
            array(array("text" => "Go to Tweet", "url" => $url)),
            array(array("text" => "Next Tweet", "callback_data" => $next))
        );
        $inlineKeyboard = array("inline_keyboard" => $keyboardArray);
        $text = urlencode($text);
        $url = "https://api.telegram.org/bot" . _BOT_TOKEN;
        $url .= "/sendMessage?chat_id=" . $chatId;
        $url .= "&text=" . $text;
        $url .= "&disable_web_page_preview=true";
        $url .= "&reply_markup=" . json_encode($inlineKeyboard);
        $url .= "&parse_mode=html";
        return file_get_contents($url);
    }

    public function sendForwardMessage($chatId, $from, $messageId)
    {
        $url = "https://api.telegram.org/bot" . _BOT_TOKEN;
        $url .= "/forwardMessage?chat_id=" . $chatId;
        $url .= "&from_chat_id=" . $from;
        $url .= "&message_id=" . $messageId;
        return file_get_contents($url);
    }

    public function pinChatMessage($chatId, $messageId)
    {
        $url = "https://api.telegram.org/bot" . _BOT_TOKEN;
        $url .= "/pinChatMessage?chat_id=" . $chatId;
        $url .= "&message_id=" . $messageId;
        $url .= "&disable_notification=true";
        file_get_contents($url);
    }

    public function unpinChatMessage($chatId)
    {
        $url = "https://api.telegram.org/bot" . _BOT_TOKEN;
        $url .= "/unpinAllChatMessages?chat_id=" . $chatId;
        file_get_contents($url);
    }

    public function sendFile($chatId, $fileId, $caption = null)
    {
        $url = "https://api.telegram.org/bot" . _BOT_TOKEN;
        $url .= "/sendDocument?chat_id=" . $chatId;
        $url .= "&document=" . $fileId;
        if ($caption != null) {
            $caption = urlencode($caption);
            $url .= "&caption=" . $caption;
            $url .= "&parse_mode=html";
        }
        return file_get_contents($url);
    }

    public function send_media_group($chatId, $media)
    {
        # $text = urlencode($caption);
        $url = "https://api.telegram.org/bot" . _BOT_TOKEN;
        $url .= "/sendMediaGroup?chat_id=" . $chatId;
        $url .= "&media=" . $media;
        return file_get_contents($url);
    }
}
