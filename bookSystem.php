<?php

// 1. DATABASE CONNECTION 
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'bookdb';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// 2. VARIABLES
$title = $author = $year = $price = '';
$update = false;
$book_id = 0;
$message = '';

// 3. CREATE OPERATION
if (isset($_POST['save'])) {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $year = trim($_POST['year']);
    $price = trim($_POST['price']);

    if ($title == '' || $author == '' || $year == '' || $price == '') {
        $message = 'All fields are required!';
    } else {
        $stmt = $conn->prepare('INSERT INTO books (title, author, year, price) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssii', $title, $author, $year, $price);
        if ($stmt->execute()) {
            $message = '✅ Book added successfully!';
        } else {
            $message = '❌ Error: ' . $stmt->error;
        }
        $stmt->close();
    }
}

// 4. DELETE OPERATION
if (isset($_GET['delete'])) {
    $book_id = $_GET['delete'];
    $stmt = $conn->prepare('DELETE FROM books WHERE book_id = ?');
    $stmt->bind_param('i', $book_id);
    $stmt->execute();
    $stmt->close();
    $message = '🗑️ Book deleted!';
}

// 5. THIS IS SELECT
if (isset($_GET['edit'])) {
    $book_id = $_GET['edit'];
    $stmt = $conn->prepare('SELECT * FROM books WHERE book_id = ?');
    $stmt->bind_param('i', $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $title = $row['title'];
        $author = $row['author'];
        $year = $row['year'];
        $price = $row['price'];
        $update = true;
    }
    $stmt->close();
}

// 6. UPDATE OPERATION 
if (isset($_POST['update'])) {
    $book_id = $_POST['book_id'];
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $year = trim($_POST['year']);
    $price = trim($_POST['price']);

    if ($title == '' || $author == '' || $year == '' || $price == '') {
        $message = 'All fields are required!';
    } else {
        $stmt = $conn->prepare('UPDATE books SET title=?, author=?, year=?, price=? WHERE book_id=?');
        $stmt->bind_param('ssiii', $title, $author, $year, $price, $book_id);
        if ($stmt->execute()) {
            $message = '✅ Book updated successfully!';
        } else {
            $message = '❌ Error: ' . $stmt->error;
        }
        $stmt->close();
        $update = false;
        $title = $author = $year = $price = '';
    }
}

// 7. SELECTING OPERATION 
$result = $conn->query('SELECT * FROM books');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book CRUD System</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f6f8;
        color: #333;
        margin: 0;
        padding: 20px;
    }
    h2, h3 {
        text-align: center;
    }
    .container {
        width: 80%;
        max-width: 800px;
        margin: 0 auto;
        background: #fff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    form {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 20px;
    }
    input[type=text], input[type=number] {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        width: 100%;
    }
    button {
        background-color: #85afd9ff;
        color: white;
        padding: 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
    }
    button:hover {
        background-color: #0056b3;
    }
    a.button {
        text-decoration: none;
        color: #fff;
        background: #6c757d;
        padding: 8px 12px;
        border-radius: 5px;
    }
    a.button:hover {
        background: #5a6268;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        border: 1px solid #ccc;
        padding: 8px;
        text-align: center;
    }
    th {
        background-color: #85afd9ff;
        color: white;
    }
    .message {
        text-align: center;
        margin-bottom: 10px;
        font-weight: bold;
    }
</style>
</head>
<body>
<div class="container">
<h2>📚 Book Management System</h2>

<?php if ($message != '') echo '<p class="message">' . htmlspecialchars($message) . '</p>'; ?>

<form method="post" action="">
    <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
    <label>Title:</label>
    <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>">

    <label>Author:</label>
    <input type="text" name="author" value="<?php echo htmlspecialchars($author); ?>">

    <label>Year:</label>
    <input type="number" name="year" value="<?php echo htmlspecialchars($year); ?>">

    <label>Price:</label>
    <input type="number" name="price" value="<?php echo htmlspecialchars($price); ?>">

    <?php if ($update): ?>
        <button type="submit" name="update">Update Book</button>
        <a href="?" class="button">Cancel</a>
    <?php else: ?>
        <button type="submit" name="save">Add Book</button>
    <?php endif; ?>
</form>

<h3>Book List</h3>
<table>
    <tr>
        <th>Book ID</th>
        <th>Title</th>
        <th>Author</th>
        <th>Year</th>
        <th>Price</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['book_id']; ?></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo htmlspecialchars($row['author']); ?></td>
            <td><?php echo htmlspecialchars($row['year']); ?></td>
            <td><?php echo htmlspecialchars($row['price']); ?></td>
            <td>
                <a href="?edit=<?php echo $row['book_id']; ?>" class="button">Edit</a>
                <a href="?delete=<?php echo $row['book_id']; ?>" class="button" style="background:#dc3545;" onclick="return confirm('Are you sure?');">Delete</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
</div>
</body>
</html>

<?php $conn->close(); ?>
