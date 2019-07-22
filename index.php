<?php
// 独自関数を作成　エスケープ
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

session_start();

$name = (string)filter_input(INPUT_POST, 'name');
$text = (string)filter_input(INPUT_POST, 'text');
$token = (string)filter_input(INPUT_POST, 'token');

$fp = fopen('data.csv', 'a+b');

//POSTで送信されてきたときのみ実行
if ($_SERVER['REQUEST_METHOD'] === 'POST' && sha1(session_id()) === $token) {
//排他ロック
    flock($fp, LOCK_EX);
    fputcsv($fp, [$name, $text]);
    rewind($fp);
}
//共有ロック
flock($fp, LOCK_SH);
while ($row = fgetcsv($fp)) {
    $rows[] = $row;
}
//ロック解除
flock($fp, LOCK_UN);
fclose($fp);

?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <title>掲示板</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
  </head>
  <body>
    <section>
        <h2>新規投稿</h2>
        <p>投稿してください！</p>
        <form action="" method="post">
            名前: <input type="text" name="name" value=""><br>
            本文: <input type="text" name="text" value=""><br>
            <button type="submit">投稿</button>
            <input type="hidden" name="token" value="<?=h(sha1(session_id()))?>">
        </form>
    </section>
    <section>
        <h2>投稿一覧</h2>
    <?php if (!empty($rows)): ?>
        <ul>
    <?php foreach ($rows as $row): ?>
            <li><?=h($row[1])?> (<?=h($row[0])?>)</li>
    <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>投稿はまだありません</p>
    <?php endif; ?>
    </section>
  </body>
</html>

