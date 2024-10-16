<?php
require __DIR__ . '/../config/config.php'; // 資料庫連線設置

// 取得文章 ID
if (!isset($_GET['id'])) {
    $_SESSION['error'] = '無效的文章 ID';
    header('Location: index.php');
    exit();
}

$articleId = $_GET['id'];

// 查詢文章資料
$stmt = $pdo->prepare('SELECT * FROM articles WHERE id = :id AND publish_start <= CURRENT_DATE() AND publish_end >= CURRENT_DATE()');
$stmt->execute([':id' => $articleId]);
$article = $stmt->fetch();

if (!$article) {
    $_SESSION['error'] = '文章不存在或已下架';
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['Title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2><?= htmlspecialchars($article['Title']) ?></h2>
        <p class="text-muted">
            發佈於：<?= date('Y-m-d', strtotime($article['Publish_Start'])) ?> | 
            文章有效期至：<?= date('Y-m-d', strtotime($article['Publish_End'])) ?>
        </p>

        <?php if ($article['Is_Member_Only'] && !isset($_SESSION['user_id'])): ?>
            <div class="alert alert-warning" role="alert">
                此文章僅限會員觀看。請<a href="login.php">登入</a>後繼續閱讀。
            </div>
        <?php else: ?>
            <div class="content">
                <?php echo htmlspecialchars_decode($article['Content']); ?>
            </div>
        <?php endif; ?>

        <a href="index.php" class="btn btn-primary mt-3">返回文章列表</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 當頁面加載完成後，等待 3 秒後增加觀看次數
        document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            const articleId = <?= json_encode($articleId) ?>;
            fetch('../src/actions/increment_view_count.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + encodeURIComponent(articleId)
            }).then(response => response.json())  // 解析 JSON 回應
              .then(data => {
                  if (data.error) {
                      console.error('Error:', data.error);
                  } else {
                      console.log(data.success || data.info);  // 成功或資訊訊息
                  }
              }).catch(error => {
                  console.error('Error:', error);
              });
        }, 3000); // 3 秒後才發送請求
    });
    </script>
</body>
</html>
