<?php
require_once 'admin/config.php';

try {
    $stmt = $conn->query("SELECT * FROM contents ORDER BY created_at DESC");
    $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطا در دریافت محتواها: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لاستیک جان جان</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/font-face.css" rel="stylesheet">
    <style>
        /* استایل‌های صفحه اصلی */
        .content-card {
            border: 1px solid #eee;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
        }

        .content-card img {
            max-width: 100%;
            height: auto;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

    <!-- بخش نمایش محتواها -->
    <section class="contents-section">
        <h2>آخرین مطالب</h2>

        <?php foreach ($contents as $content): ?>
            <div class="content-card">
                <?php if ($content['image_path']): ?>
                    <img src="admin/<?= htmlspecialchars($content['image_path']) ?>"
                        alt="<?= htmlspecialchars($content['title']) ?>">
                <?php endif; ?>

                <h3><?= htmlspecialchars($content['title']) ?></h3>
                <p><?= nl2br(htmlspecialchars($content['description'])) ?></p>
            </div>
        <?php endforeach; ?>
    </section>

</body>

</html>