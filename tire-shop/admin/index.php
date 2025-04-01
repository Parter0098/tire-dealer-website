<?php
require_once 'config.php';

// ایجاد پوشه آپلود اگر وجود ندارد
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// 1. پردازش فرم ارسال شده
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. دریافت داده‌های فرم
    $title = $_POST['title'];
    $description = $_POST['description'];

    // 3. آپلود تصویر
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // بررسی نوع فایل
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['image']['type'];

        if (!in_array($fileType, $allowedTypes)) {
            die('خطا: فقط فایل‌های JPEG, PNG و GIF مجاز هستند');
        }

        // بررسی حجم فایل (حداکثر 2MB)
        if ($_FILES['image']['size'] > 2097152) {
            die('خطا: حجم فایل نباید بیشتر از 2 مگابایت باشد');
        }

        // تولید نام منحصر به فرد برای فایل
        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $targetPath = "uploads/" . $filename;

        // جابجایی فایل آپلود شده به پوشه مورد نظر
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = $targetPath;
        } else {
            die('خطا در آپلود فایل');
        }
    }

    // 4. ذخیره در دیتابیس
    try {
        $stmt = $conn->prepare("INSERT INTO contents (title, description, image_path) VALUES (:title, :desc, :img)");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':desc', $description);
        $stmt->bindParam(':img', $imagePath);
        $stmt->execute();

        // ریدایرکت برای جلوگیری از ارسال مجدد فرم
        header("Location: index.php?success=1");
        exit();
    } catch (PDOException $e) {
        die("خطای دیتابیس: " . $e->getMessage());
    }
}

// 5. دریافت محتواهای موجود از دیتابیس
try {
    $stmt = $conn->query("SELECT * FROM contents ORDER BY created_at DESC");
    $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطا در دریافت داده‌ها: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت محتوا</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/font-face.css" rel="stylesheet">
    <style>
        body {
            font-family: Vazirmatn, sans-serif;
            line-height: 1.6;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
            background-color: #f5f5f5;
        }

        .header {
            background-color: #2563eb;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        .form-container {
            background-color: white;
            padding: 25px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: Vazirmatn, sans-serif;
        }

        textarea {
            min-height: 150px;
        }

        .btn {
            background-color: #2563eb;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-family: Vazirmatn, sans-serif;
        }

        .btn:hover {
            background-color: #1e40af;
        }

        .content-list {
            margin-top: 30px;
        }

        .content-item {
            background-color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .content-item img {
            max-width: 200px;
            height: auto;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .success-msg {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>پنل مدیریت محتوای لاستیک جان جان</h1>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="success-msg">
            محتوای جدید با موفقیت ذخیره شد!
        </div>
    <?php endif; ?>

    <div class="form-container">
        <h2>افزودن محتوای جدید</h2>
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">عنوان محتوا:</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="description">توضیحات:</label>
                <textarea id="description" name="description" required></textarea>
            </div>

            <div class="form-group">
                <label for="image">تصویر (اختیاری):</label>
                <input type="file" id="image" name="image" accept="image/*">
                <small>حداکثر حجم: 2MB - فرمت‌های مجاز: JPG, PNG, GIF</small>
            </div>

            <button type="submit" class="btn">ذخیره محتوا</button>
        </form>
    </div>

    <div class="content-list">
        <h2>مطالب موجود</h2>

        <?php if (empty($contents)): ?>
            <p>هنوز محتوایی اضافه نشده است.</p>
        <?php else: ?>
            <?php foreach ($contents as $content): ?>
                <div class="content-item">
                    <?php if ($content['image_path']): ?>
                        <img src="<?= htmlspecialchars($content['image_path']) ?>"
                            alt="<?= htmlspecialchars($content['title']) ?>">
                    <?php endif; ?>

                    <h3><?= htmlspecialchars($content['title']) ?></h3>
                    <p><?= nl2br(htmlspecialchars($content['description'])) ?></p>

                    <div style="margin-top: 15px; color: #666; font-size: 0.9em;">
                        تاریخ ایجاد: <?= date('Y/m/d H:i', strtotime($content['created_at'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>

</html>