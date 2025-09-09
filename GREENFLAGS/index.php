<?php
session_start();

// DISABLE DEBUGGING...
// REDIRECT TO LOGIN IF NOT LOGGED IN!!!
// CREDENTIALS OF /login has been sent via Telegram. kindly don't share that. even tho... RCE cannot occur here as PHP upload is forbidden. but still tho. try not to be an asshole and share it as it can lead to Stored XSS...
if (!isset($_SESSION['logged_in'])) {
    header("Location: /login");
    exit;
}

// ---------------- DASHBOARD ----------------
$uploadMsg = "";

// UPLOAD FILE. PREVENTS UPLOADING index.html as NGINX makes it as of / . and index.html is already there on /var/www/html so that will make a defacing mess...
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileName = basename($_FILES['file']['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if ($fileExt === 'php' || strtolower($fileName) === 'index.html') {
        $uploadMsg = "Uploading PHP or index.html files is not allowed!";
    } else {
        $safeName = preg_replace("/[^a-zA-Z0-9-_\.]/", "_", $fileName);
        $targetFile = $_SERVER['DOCUMENT_ROOT'].'/'.$safeName;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
            $uploadMsg = "File uploaded successfully: $safeName";
        } else {
            $uploadMsg = "Error uploading file.";
        }
    }
}

// DELETE FILE!! PREVENTING USERS FROM DELETING THE index.html FILE to prevent defacing since this reads /var/www/html/*
if (isset($_GET['delete'])) {
    $delFile = basename($_GET['delete']);
    $delPath = $_SERVER['DOCUMENT_ROOT'].'/'.$delFile;

    if (strtolower($delFile) !== 'index.html' && strtolower(pathinfo($delFile, PATHINFO_EXTENSION)) !== 'php') {
        $delPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $delFile;
        if (file_exists($delPath)) {
            unlink($delPath);
        }
    }
    header("Location: /");
    exit;
}

// READ FILES!! PREVENTING USERS FROM HAVING THE ABILTY OF READING PHP and INDEX.HTML FILES!! TO PREVENT SUCH ATTACKS INCASE CREDS WERE LEAKED...
$files = array_filter(scandir($_SERVER['DOCUMENT_ROOT']), function ($f) {
    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
    return is_file($_SERVER['DOCUMENT_ROOT'] . '/' . $f) 
        && $ext !== 'php' 
        && strtolower($f) !== 'index.html';
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Internal System</title>
<style>
<?php
?>
body {
  background: #1b1b1b;
  font-family: 'Roboto', sans-serif;
  margin: 0;
  padding: 0;
  color: #ffffff;
}
.navigation {
  background-color: darkgoldenrod;
  position: sticky;
  top: 0;
  width: 100%;
  padding: 18px 50px;
  z-index: 999;
}
a {
  text-decoration: none;
  color: #333;
}
ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  justify-content: flex-start;
}
li {
  color: white;
  padding: 15px 30px;
  font-size: 16px;
  text-transform: uppercase;
  font-weight: 600;
  transition: background-color 0.3s ease;
  cursor: pointer;
}
li:hover {
  background-color: #00000065;
}
li.active {
  background-color: #00000069;
  color: #ffffff;
}
.container {
  display: flex;
  justify-content: space-between;
  max-width: 1200px;
  width: 100%;
  margin: 30px auto;
  padding: 0 20px;
}
.form-container, .logs-container {
  width: 45%;
  max-height: 610px;
  overflow-y: auto;
  padding: 20px;
  background-color: rgb(37, 33, 33);
  border-radius: 5px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
}

.logs-container {
  margin-left: 20px;
}

/* Fixing this stupidasshole bug... */
.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
}

.form-group input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 3px;
  box-sizing: border-box;
}
button[type="submit"] {
  background-color: darkgoldenrod;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 3px;
  cursor: pointer;
  width: 100%;
  font-size: 16px;
  transition: background-color 0.3s ease, transform 0.2s ease;
}
button[type="submit"]:hover {
  background-color: rgb(134, 99, 8);
}
button[type="submit"]:active {
  background-color: darkgoldenrod;
}
#logTable {
  width: 100%;
  border-collapse: collapse;
}
#logTable th, #logTable td {
  padding: 10px;
  text-align: left;
  border: 1px solid #ddd;
}
.search {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.search input {
  width: 80%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 3px;
  margin-right: 10px;
}
.search button {
  padding: 8px 15px;
  font-size: 14px;
  background-color: darkgoldenrod;
  color: white;
  border: none;
  border-radius: 3px;
  cursor: pointer;
}
.search button:hover {
  background-color: rgb(116, 85, 8);
}
footer {
  display: none;
}
</style>
</head>
<body>
<div class="navigation">
    <ul>
        <li class="active">Dashboard</li>
        <li><a href="../login.php?logout=1" style="color:white;">Logout</a></li>
    </ul>
</div>

<div class="container">
    <div class="form-container">
        <h2>Upload File</h2>
        <h5>PHP is forbidden. Even tho i made this for internal use only. Credentials might be leaked. so PHP is Disabled. Sorry abt that</h5>
        <?php if($uploadMsg) echo "<p>$uploadMsg</p>"; ?>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Select file</label>
                <input type="file" name="file" required>
            </div>
            <button type="submit">Upload</button>
        </form>
    </div>

    <div class="logs-container">
        <h2>Files</h2>
        <table id="logTable">
            <tr><th>Filename</th><th>Actions</th></tr>
            <?php foreach($files as $f): ?>
                <tr>
                    <td><a style="color: rgb(116, 85, 8);" href="/<?php echo htmlspecialchars($f); ?>" target="_blank"><?php echo htmlspecialchars($f); ?></a></td>
                    <td>
                        <a href="?delete=<?php echo urlencode($f); ?>" onclick="return confirm('Are you sure??')" style="color: red">Delete</a> <!-- the Confirm will here will return whenever the user really wants to delte the file or not -->
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
</body>
</html>
