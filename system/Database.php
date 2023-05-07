<?php


class Database
{

  private $connection;
  private static $db;

  public static function getInstance($option = null)
  {
    if (self::$db == null) {
      self::$db = new Database($option);
    }
    return self::$db;
  }

  private function __construct($option = null)
  {
    if ($option != null) {
      $host = $option['host'];
      $user = $option['user'];
      $pass = $option['pass'];
      $name = $option['name'];
    } else {
      global $config;
      $host = $config['host'];
      $user = $config['user'];
      $pass = $config['pass'];
      $name = $config['name'];
    }

    $this->connection = new mysqli($host, $user, $pass, $name);
    if ($this->connection->connect_error) {
      echo "Connection failed: " . $this->connection->connect_error;
      exit;
    }

    $this->connection->query("SET NAMES 'ut8'");
  }

  public function query($sql)
  {
    return $this->connection->query($sql);
  }

  public function insertUserData($chatId)
  {
    $this->query("INSERT INTO `_user_login` VALUES (NULL, '" . time() . "', '" . $chatId . "', 'Empty', '0')");
  }

  public function insert_tweet_json($id, $json)
  {
    $this->query("INSERT INTO `_tweet_json` VALUES ('" . $id . "', '" . $json . "')");
  }
}
