<?php

if (empty($_GET['story'])) {
  header("HTTP/1.1 400 Bad Request", 400);
  header("Content-type: text/plain");
  die("No parameters.");
}
if (!preg_match('/^\d+$/', $_GET['story'])) {
  if (preg_match('/story\/(\d+)/', $_GET['story'], $match)) {
    header("HTTP/1.1 302 Found", 302);
    header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}?story={$match[1]}");
    exit();
  }
  header("HTTP/1.1 400 Bad Request", 400);
  header("Content-type: text/plain");
  die("Bad request: `{$_GET['story']}` should be an ID.");
}

$id = $_GET['story'];
$limit = !empty($_GET['limit']) ? $_GET['limit'] : 20;

$url = "https://www.fimfiction.net/api/story.php?story=$id";
$story = json_decode(file_get_contents($url))->story;

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

