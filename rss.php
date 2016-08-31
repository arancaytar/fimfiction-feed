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

header("Content-type: application/rss+xml");
print('<?xml version="1.0" encoding="utf-8"?>');

?>

<rss version="2.0">
<channel>
  <title><?=$story->title?></title>
  <description><?=$story->short_description?></description>
  <link><?=$story->url?></link>
  <image>
    <url><?=$story->image?></url>
    <title><?=$story->title?></title>
    <link><?=$story->url?></link>
  </image>
<?php foreach ($story->categories as $category => $flag) if ($flag) { ?>
  <category><?=$category?></category>
<?php } ?>
  <pubDate><?=date('r', $story->date_modified);?></pubDate>
  <lastBuildDate><?=date('r', $story->date_modified);?></lastBuildDate>
<?php for ($i = 0; $i < $chapters; $i++) {
  $chapter = $story->chapters[$story->chapter_count - $i - 1];
?>
  <item>
    <title><?=$chapter->title?></title>
    <link><?=$chapter->link?></link>
    <pubDate><?=date('r', $chapter->date_modified)?></pubDate>
  </item>
<?php } ?>
</channel>
</rss>
