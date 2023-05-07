  <?php
  require_once("config.php");

  $json = file_get_contents('php://input');
  $tg = Telegram::getInstance($json);
  $md = Methods::getInstance();
  $chat_id = $tg->getChatId();
  $text = $tg->getMessageText();

  if (strpos($text, "start") !== false) {
    // Start bot
    $md->setStartMessage($chat_id, $text);
  } elseif (strpos($text, "getlink-") !== false) {
    // No start bot
    $tg->setChatAction($chat_id);
    $data = explode("-", $text);
    $msg = "Twitter profile link: 👇🏽\n\n";
    if ($data[1] == 'id') {
      $msg .= "<code>https://twitter.com/intent/user?user_id=" . $data[2] . "</code>";
    } else {
      $msg .= "<code>https://twitter.com/intent/user?screen_name=" . $data[2] . "</code>";
    }
    $tg->sendMessage($chat_id, $msg);
  } else {
    $tg->setChatAction($chat_id);
    $md->userIdLookUp($chat_id, $text);
  }
