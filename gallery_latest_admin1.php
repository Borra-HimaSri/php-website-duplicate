<?php
ob_start();
include 'admin_common.php'; // $conn is now PDO
require 'vendor/autoload.php';
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

// Cloudinary Configuration
Configuration::instance([
    'cloud' => [
        'cloud_name' => 'dyvs4ugkk',
        'api_key'    => '567619791139426',
        'api_secret' => 'ZmSo5zZoMgkr7LcGz_QHPRm7vVI'
    ],
    'url' => ['secure' => true]
]);

$cloudinary = new Cloudinary(Configuration::instance());

// Upload Image
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    $category = 'gallery-latest';

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        try {
            $upload = $cloudinary->uploadApi()->upload($_FILES['image']['tmp_name'], [
                'folder' => 'smartkids/gallery-latest'
            ]);
            $imageUrl = $upload['secure_url'];

            $stmt = $conn->prepare("INSERT INTO images (image_path, category, news_name, news_description, news_date) VALUES (:image_path, :category, :news_name, :news_description, :news_date)");
            $stmt->execute([
                ':image_path' => $imageUrl,
                ':category' => $category,
                ':news_name' => $_POST['news_name'],
                ':news_description' => $_POST['news_description'],
                ':news_date' => $_POST['news_date']
            ]);

            header("Location: gallery_latest_admin.php");
            exit;
        } catch (Exception $e) {
            echo "Image upload error: " . $e->getMessage();
        }
    } else {
        echo "Image upload failed!";
    }
}

// Delete Image
if (isset($_POST['delete'])) {
    $imageId = intval($_POST['delete']);

    $stmt = $conn->prepare("SELECT image_path FROM images WHERE id = :id");
    $stmt->execute([':id' => $imageId]);
    $imagePathToDelete = $stmt->fetchColumn();

    if ($imagePathToDelete) {
        // Optional: delete from Cloudinary if needed
        $stmtDel = $conn->prepare("DELETE FROM images WHERE id = :id");
        $stmtDel->execute([':id' => $imageId]);
    }
}

// Edit News
$editRow = null;
if (isset($_POST['edit'])) {
    $editId = intval($_POST['edit']);
    $stmt = $conn->prepare("SELECT * FROM images WHERE id = :id");
    $stmt->execute([':id' => $editId]);
    $editRow = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Update News
if (isset($_POST['update'])) {
    $updateId = intval($_POST['update']);
    $stmt = $conn->prepare("UPDATE images SET news_name=:news_name, news_description=:news_description, news_date=:news_date WHERE id=:id");
    $stmt->execute([
        ':news_name' => $_POST['news_name'],
        ':news_description' => $_POST['news_description'],
        ':news_date' => $_POST['news_date'],
        ':id' => $updateId
    ]);
    header("Location: gallery_latest_admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Gallery Latest</title>
    <link rel="icon" type="image/png" href="img/logo.png">
    <link rel="stylesheet" href="css/news.css">
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this image?");
        }
    </script>
</head>
<body>
<div class="admin-nav">
    <a href="gallery_photo_admin.php"><button>Gallery</button></a>
    <a href="gallery_event_admin.php"><button> Events</button></a>
    <a href="gallery_latest_admin.php"><button>News</button></a>
    <a href="admin.php"><button>Admin Page</button></a>
</div>

<h2>Upload to Gallery Latest</h2>
<form action="gallery_latest_admin.php" method="post" enctype="multipart/form-data">
    <input type="file" name="image" required>
    <input type="text" name="news_name" placeholder="News Title" required>
    <textarea name="news_description" placeholder="News Description" required></textarea>
    <input type="date" name="news_date" required>
    <button type="submit" name="upload">Upload Image</button>
</form>

<?php if ($editRow) { ?>
    <h2>Edit News</h2>
    <form action="gallery_latest_admin.php" method="post">
        <input type="hidden" name="update" value="<?= $editRow['id'] ?>">
        <input type="text" name="news_name" value="<?= htmlspecialchars($editRow['news_name']) ?>" required>
        <textarea name="news_description" required><?= htmlspecialchars($editRow['news_description']) ?></textarea>
        <input type="date" name="news_date" value="<?= $editRow['news_date'] ?>" required>
        <button type="submit">Update News</button>
    </form>
<?php } ?>

<div class="gallery-container">
    <?php
    $stmt = $conn->query("SELECT * FROM images WHERE category='gallery-latest'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<div>
                <img src="' . htmlspecialchars($row['image_path']) . '" style="width: 150px; height: 150px; object-fit: cover;">
                <form action="gallery_latest_admin.php" method="post">
                    <button type="submit" name="edit" value="' . $row['id'] . '">Edit</button>
                </form>
                <form action="gallery_latest_admin.php" method="post" onsubmit="return confirmDelete();">
                    <button type="submit" name="delete" value="' . $row['id'] . '">Delete</button>
                </form>
              </div>';
    }
    ?>
</div>
</body>
</html>
