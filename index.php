<?php
// index.php

$results = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['keyword'])) {
    $keyword = trim($_GET['keyword']);

    if (!empty($keyword)) {
        $query = $keyword;
        
        // iTunes Search API エンドポイント
        $api_url = 'https://itunes.apple.com/search';

        // パラメータ設定
        $params = [
            'term' => $query,
            'media' => 'music',
            'entity' => 'song',
            'limit' => 20,
            'lang' => 'ja_jp',
            'country' => 'JP'
        ];

        $url = $api_url . '?' . http_build_query($params);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'YourAppName/1.0 (your-email@example.com)');

        $response = curl_exec($ch);

        if ($response === false) {
            $error = 'APIリクエストに失敗しました: ' . curl_error($ch);
        } else {
            $data = json_decode($response, true);
            if (isset($data['resultCount']) && $data['resultCount'] > 0) {
                $results = $data['results'];
            } else {
                $error = '該当するトラックが見つかりませんでした。';
            }
        }

        curl_close($ch);
    } else {
        $error = 'キーワードを入力してください。';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>iTunes 音楽検索</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            color: #333;
            text-align: center;
            padding: 50px;
        }
        h1 {
            color: #1DB954;
            margin-bottom: 40px;
        }
        .search-form {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            display: inline-block;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            width: 100%;
            max-width: 500px;
        }
        .search-form label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
            text-align: left;
        }
        .search-form input[type="text"], .search-form input[type="submit"] {
            padding: 10px;
            margin: 5px 0 15px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .search-form input[type="submit"] {
            background-color: #1DB954;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .search-form input[type="submit"]:hover {
            background-color: #17a44c;
        }
        .results {
            margin-top: 30px;
        }
        .track {
            background-color: #fff;
            padding: 20px;
            margin: 15px auto;
            border-radius: 10px;
            width: 100%;
            max-width: 800px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: left;
            display: flex;
            align-items: center;
            transition: transform 0.2s;
        }
        .track:hover {
            transform: scale(1.02);
        }
        .track img {
            width: 100px;
            height: 100px;
            margin-right: 20px;
            border-radius: 5px;
            flex-shrink: 0;
        }
        .track-info {
            flex-grow: 1;
        }
        .track-info strong {
            display: block;
            font-size: 18px;
            margin-bottom: 5px;
            color: #1DB954;
        }
        .track-info p {
            margin: 5px 0;
        }
        .track audio {
            width: 100%;
            margin-top: 10px;
            border-radius: 5px;
        }
        .track a {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 12px;
            background-color: #1DB954;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .track a:hover {
            background-color: #17a44c;
        }
        .error {
            color: red;
            font-weight: bold;
            margin-top: 20px;
        }
        @media (max-width: 600px) {
            .track {
                flex-direction: column;
                align-items: flex-start;
            }
            .track img {
                margin-bottom: 15px;
                width: 80px;
                height: 80px;
            }
            .search-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <h1>iTunes 音楽検索</h1>
    <div class="search-form">
        <form action="index.php" method="GET">
            <label for="keyword">キーワードを入力:</label>
            <input type="text" name="keyword" id="keyword" placeholder="アーティスト名、曲名、アルバム名など" required>
            <input type="submit" value="検索">
        </form>
    </div>

    <?php if (!empty($error)): ?>
        <div class="error">
            <p><?= htmlspecialchars($error) ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($results)): ?>
        <div class="results">
            <?php foreach ($results as $track): ?>
                <div class="track">
                    <img src="<?= htmlspecialchars($track['artworkUrl100']) ?>" alt="アルバムアート">
                    <div class="track-info">
                        <strong><?= htmlspecialchars($track['trackName']) ?></strong>
                        <p>アーティスト: <?= htmlspecialchars($track['artistName']) ?></p>
                        <p>アルバム: <?= htmlspecialchars($track['collectionName']) ?></p>
                        <audio controls>
                            <source src="<?= htmlspecialchars($track['previewUrl']) ?>" type="audio/mpeg">
                            お使いのブラウザはaudio要素をサポートしていません。
                        </audio>
                        <br>
                        <a href="<?= htmlspecialchars($track['trackViewUrl']) ?>" target="_blank">iTunesで購入</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['keyword']) && empty($error)): ?>
        <p>該当するトラックが見つかりませんでした。</p>
    <?php endif; ?>
</body>
</html>