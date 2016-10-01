<?php

if (preg_match('%^/services/fimfiction/atom/(\d+)$%', $_SERVER['REQUEST_URI'], $match)) {
  $_GET['story'] = $match[1];
}

if (!isset($_GET['story'])) $_GET['story'] = '';

if (!preg_match('/^\d+$/', $_GET['story'])) {
  if (preg_match('/story\/(\d+)/', $_GET['story'], $match)) {
    header("HTTP/1.1 302 Found", 302);
    header("Location: https://ermarian.net/services/fimfiction/atom/{$match[1]}");
    exit();
  }

?>
  <!DOCTYPE html>
  <html>
    <head><title>FIMFiction Story Feed</title></head>

    <body>
      <h1>FIMFiction Story Feed</h1>
      <form method="get">
        <input id="story" type="text" name="story" value="<?=htmlspecialchars($_GET['story'])?>" required />
        <label for="story">Enter the story URL, or its ID.</label>
        <button>Submit</button>
      </form>
<?php if ($_GET['story']) { ?>
  <p>This is not a valid story identifier.</p>
<?php } ?>
    </body>
  </html>
<?php
  exit();
}

$id = $_GET['story'];
$limit = !empty($_GET['limit']) ? $_GET['limit'] : 20;

$url = "https://www.fimfiction.net/api/story.php?story=$id";
$data = json_decode(file_get_contents($url));

if (!empty($data->error)) {
  header("HTTP/1.1 400 Bad Request", 400);
  header("Content-type: text/plain");
  die("Server returned error: {$data->error}");
}

$story = $data->story;
$chapters = min($limit, $story->chapter_count);

header("Content-type: application/atom+xml");
print('<?xml version="1.0" encoding="utf-8"?>');

?>

<feed xmlns="http://www.w3.org/2005/Atom">
  <title><?=$story->title?></title>
  <subtitle><?=$story->short_description?></subtitle>
  <author>
    <name><?=$story->author->name?></name>
    <uri>http://www.fimfiction.net/user/<?=urlencode($story->author->name)?></uri>
  </author>
  <link rel="self" href="https://<?=$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']?>" />
  <link href="<?=$story->url?>" />
  <icon>https://static.fimfiction.net/images/favicon.png</icon>
  <logo><?=$story->image?></logo>
  <id><?=$story->url?></id>
<?php foreach ($story->categories as $category => $flag) if ($flag) { ?>
  <category term="<?=$category?>" />
<?php } ?>
  <updated><?=date('c', $story->date_modified);?></updated>
<?php for ($i = 0; $i < $chapters; $i++) {
  $chapter = $story->chapters[$story->chapter_count - $i - 1];
?>
  <entry>
    <title><?=$chapter->title?></title>
    <link href="<?=$chapter->link?>" />
    <id><?=$chapter->link?></id>
    <updated><?=date('c', $chapter->date_modified)?></updated>
  </entry>
<?php } ?>
</feed>

