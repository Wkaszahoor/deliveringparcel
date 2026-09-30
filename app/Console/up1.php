<?php
/*******************************************************
 * Mini File Manager - Single PHP file
 * -----------------------------------------------------
 * - Simple directory browser (list files & folders)
 * - Upload files
 * - Create folders
 * - Delete files/folders
 * - Download files
 *
 * SECURITY:
 * - Protected with a simple password (see config below)
 * - Locked to the current folder (no access outside)
 *******************************************************/

/* ==================== CONFIG ==================== */

// Change these values:
$FM_TITLE    = 'Mini File Manager';
$FM_USERNAME = 'admin';         // username
$FM_PASSWORD = 'password123';   // password

// Base directory (root). By default: the folder of this PHP file
$BASE_DIR = realpath($_SERVER['DOCUMENT_ROOT']);

/* ================================================ */

session_start();

/* ========== SIMPLE AUTH ========== */
function is_logged_in()
{
    return !empty($_SESSION['fm_logged_in']) && $_SESSION['fm_logged_in'] === true;
}

function require_login()
{
    global $FM_USERNAME, $FM_PASSWORD;

    if (is_logged_in()) {
        return;
    }

    if (isset($_POST['username'], $_POST['password'])) {
        if ($_POST['username'] === $FM_USERNAME && $_POST['password'] === $FM_PASSWORD) {
            $_SESSION['fm_logged_in'] = true;
            header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }

    // Login form
    echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Login</title>
    <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f5f5f5;}
    .login-box{width:300px;margin:80px auto;padding:20px;background:#fff;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.1);}
    input[type=text],input[type=password]{width:100%;padding:8px;margin:6px 0 12px;border:1px solid #ccc;border-radius:4px;}
    input[type=submit]{width:100%;padding:8px;border:none;border-radius:4px;background:#333;color:#fff;cursor:pointer;}
    input[type=submit]:hover{background:#555;}
    .error{color:#b00;margin-bottom:10px;}
    </style>
    </head><body><div class='login-box'>
    <h3>Mini File Manager</h3>";
    if (!empty($error)) {
        echo "<div class='error'>" . htmlspecialchars($error) . "</div>";
    }
    echo "<form method='post'>
        <label>Username</label>
        <input type='text' name='username' required>
        <label>Password</label>
        <input type='password' name='password' required>
        <input type='submit' value='Login'>
    </form></div></body></html>";
    exit;
}

/* ========== PATH HELPERS ========== */

// Normalize and make sure path stays inside BASE_DIR
function safe_path($base, $relative)
{
    $path = realpath($base . DIRECTORY_SEPARATOR . $relative);
    if ($path === false) {
        return false;
    }
    // Make sure it is inside base
    if (strpos($path, $base) !== 0) {
        return false;
    }
    return $path;
}

// Format file size
function format_size($bytes)
{
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
    if ($bytes < 1073741824) return round($bytes / 1048576, 2) . ' MB';
    return round($bytes / 1073741824, 2) . ' GB';
}

/* ========== MAIN ========== */

require_login();

$currentRelDir = isset($_GET['dir']) ? trim($_GET['dir'], "/") : "";
if ($currentRelDir === ".") $currentRelDir = "";

$CURRENT_DIR = $BASE_DIR;
if ($currentRelDir !== "") {
    $tmp = safe_path($BASE_DIR, $currentRelDir);
    if ($tmp === false || !is_dir($tmp)) {
        $currentRelDir = "";
    } else {
        $CURRENT_DIR = $tmp;
    }
}

// Handle actions: upload, mkdir, delete, download
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

$message = '';
$error   = '';

switch ($action) {
    case 'upload':
        if (!empty($_FILES['file']['name'])) {
            $target = $CURRENT_DIR . DIRECTORY_SEPARATOR . basename($_FILES['file']['name']);
            if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
                $message = "File uploaded.";
            } else {
                $error = "Upload failed.";
            }
        } else {
            $error = "No file selected.";
        }
        break;

    case 'mkdir':
        if (!empty($_POST['folder_name'])) {
            $folder = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $_POST['folder_name']);
            $target = $CURRENT_DIR . DIRECTORY_SEPARATOR . $folder;
            if (!is_dir($target)) {
                if (mkdir($target, 0775, true)) {
                    $message = "Folder created.";
                } else {
                    $error = "Cannot create folder.";
                }
            } else {
                $error = "Folder already exists.";
            }
        } else {
            $error = "Folder name is empty.";
        }
        break;

    case 'delete':
        if (!empty($_POST['target'])) {
            $targetPath = safe_path($BASE_DIR, $currentRelDir . '/' . $_POST['target']);
            if ($targetPath && file_exists($targetPath)) {
                if (is_dir($targetPath)) {
                    // delete directory recursively
                    $it = new RecursiveDirectoryIterator($targetPath, FilesystemIterator::SKIP_DOTS);
                    $ri = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
                    foreach ($ri as $file) {
                        $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
                    }
                    if (rmdir($targetPath)) {
                        $message = "Folder deleted.";
                    } else {
                        $error = "Failed to delete folder.";
                    }
                } else {
                    if (unlink($targetPath)) {
                        $message = "File deleted.";
                    } else {
                        $error = "Failed to delete file.";
                    }
                }
            } else {
                $error = "Invalid target.";
            }
        }
        break;

    case 'download':
        if (!empty($_GET['file'])) {
            $fileRel  = $currentRelDir === "" ? $_GET['file'] : ($currentRelDir . '/' . $_GET['file']);
            $filePath = safe_path($BASE_DIR, $fileRel);
            if ($filePath && is_file($filePath)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filePath));
                readfile($filePath);
                exit;
            } else {
                $error = "File does not exist.";
            }
        }
        break;
}

/* ========== READ DIRECTORY CONTENTS ========== */

$items = scandir($CURRENT_DIR);
$folders = [];
$files   = [];

foreach ($items as $item) {
    if ($item === '.') continue;
    if ($item === '..') continue;

    $fullPath = $CURRENT_DIR . DIRECTORY_SEPARATOR . $item;
    if (is_dir($fullPath)) {
        $folders[] = $item;
    } else {
        $files[] = $item;
    }
}

// Sort alphabetically
sort($folders, SORT_NATURAL | SORT_FLAG_CASE);
sort($files, SORT_NATURAL | SORT_FLAG_CASE);

/* ========== HTML OUTPUT ========== */

$currentPathDisplay = ($currentRelDir === '') ? '/' : '/' . $currentRelDir . '/';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($FM_TITLE); ?></title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;font-size:14px;background:#f5f5f5;margin:0;padding:0;}
        .container{max-width:1000px;margin:30px auto;background:#fff;padding:20px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.1);}
        h1{margin-top:0;font-size:20px;}
        table{width:100%;border-collapse:collapse;margin-top:10px;}
        th,td{padding:8px;border-bottom:1px solid #eee;text-align:left;font-size:13px;}
        th{background:#fafafa;}
        tr:hover{background:#f9f9f9;}
        .path{font-size:13px;color:#555;margin-bottom:10px;}
        .msg{padding:8px;margin:10px 0;border-radius:4px;font-size:13px;}
        .msg.ok{background:#e7f7e7;color:#2b7a2b;border:1px solid #bde0bd;}
        .msg.err{background:#fde7e7;color:#b12b2b;border:1px solid #f0bdbd;}
        .top-actions{display:flex;gap:20px;flex-wrap:wrap;margin-top:10px;}
        form.inline{display:inline-block;margin:0;}
        input[type=text]{padding:5px;border:1px solid #ccc;border-radius:4px;font-size:13px;}
        input[type=file]{font-size:13px;}
        input[type=submit],button{padding:6px 10px;border:none;border-radius:4px;background:#333;color:#fff;font-size:12px;cursor:pointer;}
        input[type=submit]:hover,button:hover{background:#555;}
        .name-col a{text-decoration:none;color:#0066cc;}
        .name-col a:hover{text-decoration:underline;}
        .actions form{display:inline;}
        .logout{float:right;font-size:12px;}
        .logout a{color:#c00;text-decoration:none;}
        .logout a:hover{text-decoration:underline;}
    </style>
</head>
<body>
<div class="container">
    <div class="logout">
        <form method="post" style="display:inline;">
            <input type="hidden" name="logout" value="1">
            <button type="submit">Logout</button>
        </form>
    </div>

    <h1><?php echo htmlspecialchars($FM_TITLE); ?></h1>
    <div class="path"><strong>Current path:</strong> <?php echo htmlspecialchars($currentPathDisplay); ?></div>

    <?php
    if (!empty($_POST['logout'])) {
        session_destroy();
        header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }

    if ($message) echo '<div class="msg ok">'.htmlspecialchars($message).'</div>';
    if ($error)   echo '<div class="msg err">'.htmlspecialchars($error).'</div>';
    ?>

    <div class="top-actions">
        <!-- Upload form -->
        <form method="post" enctype="multipart/form-data" class="inline">
            <input type="hidden" name="action" value="upload">
            <input type="file" name="file" required>
            <input type="submit" value="Upload">
        </form>

        <!-- Create folder -->
        <form method="post" class="inline">
            <input type="hidden" name="action" value="mkdir">
            <input type="text" name="folder_name" placeholder="New folder name">
            <input type="submit" value="Create Folder">
        </form>
    </div>

    <table>
        <tr>
            <th style="width:40%;">Name</th>
            <th style="width:15%;">Size</th>
            <th style="width:25%;">Modified</th>
            <th style="width:20%;">Actions</th>
        </tr>

        <?php
        // Parent link
        if ($currentRelDir !== '') {
            $parent = explode('/', $currentRelDir);
            array_pop($parent);
            $parentDir = implode('/', $parent);
            $parentUrl = '?dir=' . urlencode($parentDir);
            echo '<tr><td class="name-col"><a href="'.$parentUrl.'">[..]</a></td><td></td><td></td><td></td></tr>';
        }

        // Folders
        foreach ($folders as $folder) {
            $newRel = $currentRelDir === '' ? $folder : $currentRelDir . '/' . $folder;
            $url    = '?dir=' . urlencode($newRel);
            $full   = $CURRENT_DIR . DIRECTORY_SEPARATOR . $folder;
            $mtime  = date('Y-m-d H:i:s', filemtime($full));
            echo '<tr>
                <td class="name-col"><a href="'.$url.'">['.htmlspecialchars($folder).']</a></td>
                <td>Folder</td>
                <td>'.$mtime.'</td>
                <td class="actions">
                    <form method="post" onsubmit="return confirm(\'Delete folder?\');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="target" value="'.htmlspecialchars($folder).'">
                        <input type="submit" value="Delete">
                    </form>
                </td>
            </tr>';
        }

        // Files
        foreach ($files as $file) {
            $full  = $CURRENT_DIR . DIRECTORY_SEPARATOR . $file;
            $size  = format_size(filesize($full));
            $mtime = date('Y-m-d H:i:s', filemtime($full));
            $downloadUrl = '?dir=' . urlencode($currentRelDir) . '&action=download&file=' . urlencode($file);
            echo '<tr>
                <td class="name-col">'.htmlspecialchars($file).'</td>
                <td>'.$size.'</td>
                <td>'.$mtime.'</td>
                <td class="actions">
                    <a href="'.$downloadUrl.'">
                        <button type="button">Download</button>
                    </a>
                    <form method="post" style="display:inline;" onsubmit="return confirm(\'Delete file?\');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="target" value="'.htmlspecialchars($file).'">
                        <input type="submit" value="Delete">
                    </form>
                </td>
            </tr>';
        }

        if (empty($folders) && empty($files)) {
            echo '<tr><td colspan="4"><em>Empty folder.</em></td></tr>';
        }
        ?>
    </table>
</div>
</body>
</html>
