<?php 
session_start();
require_once "database.php";

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;// Redirect to login if not logged in
}

// Handle book reservation
if (isset($_POST['reserve']) && isset($_SESSION['username'])) {
    $isbn = $conn->real_escape_string($_POST['isbn']);
    $user = $_SESSION['username'];
    
    $check = $conn->query("SELECT * FROM Reservations WHERE ISBN='$isbn'");
    if ($check->num_rows == 0) { //checks if the book is already reserved by finding if there are any rows with that ISBN as ISBN is the primary key
        $conn->query("INSERT INTO Reservations (ISBN, Username, ReservedDate) 
                     VALUES ('$isbn', '$user', CURDATE())");
    }
    header("Location: dashboard.php?search=1&title=" . ($_GET['title'] ?? '') . "&author=" . ($_GET['author'] ?? '') . "&category=" . ($_GET['category'] ?? ''));
    exit;
}

// Handle reservation cancellation 
if (isset($_POST['cancel']) && isset($_SESSION['username'])) {
    $isbn = $conn->real_escape_string($_POST['isbn']);
    $user = $_SESSION['username'];
    $conn->query("DELETE FROM Reservations WHERE ISBN='$isbn' AND Username='$user'"); //sql to delete the reservation and therefore allows other to reserve that book
    header("Location: dashboard.php");
    exit;
}

// Search books - 
$books = [];
if (isset($_GET['search'])) {
    $title = $conn->real_escape_string($_GET['title'] ?? '');// used ?? to provide a default value if the parameter is not set
    $author = $conn->real_escape_string($_GET['author'] ?? '');
    $category = $conn->real_escape_string($_GET['category'] ?? '');
    
    
    $sql = "SELECT b.*, c.CategoryDescription FROM Books b 
            LEFT JOIN Categories c ON b.CategoryID = c.CategoryID  where 1=1";
    
    if (!empty($title)) $sql .= " AND b.BookTitle LIKE '%$title%'";
    if (!empty($author)) $sql .= " AND b.Author LIKE '%$author%'";
    if (!empty($category)) $sql .= " AND b.CategoryID = '$category'";
    
    $result = $conn->query($sql);
    if ($result) {
        $books = $result->fetch_all(MYSQLI_ASSOC);//the fetch_all gets all the rows returned by the query as an associative array
    }
}

// Get user's reservations
$reservations = [];
if (isset($_SESSION['username'])) {
    $user = $_SESSION['username'];
    $result = $conn->query("SELECT r.*, b.BookTitle, b.Author, c.CategoryDescription  
                           FROM Reservations r 
                           JOIN Books b ON r.ISBN = b.ISBN 
                           JOIN Categories c ON b.CategoryID = c.CategoryID  
                           WHERE r.Username = '$user'"); //inner join to get book and category details for user's reservations
    if ($result) {
        $reservations = $result->fetch_all(MYSQLI_ASSOC);
    }
}

// Pagination logic, this is to limit the number of books shown per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 5;
$total_books = count($books);
$total_pages = ceil($total_books / $per_page);
$page = max(1, min($page, $total_pages));
$start = ($page - 1) * $per_page;
$paginated_books = array_slice($books, $start, $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library System - Find Your Next Read</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header"> <!-- Header (top bit), this will show the username and beside it there will be a button to log out-->
        <div class="header-container">
            <a href="dashboard.php" class="logo">
                <span class="logo-icon">📚</span>
                <span class="logo-text">Library System</span>

            </a>
            <?php if (isset($_SESSION['username'])): ?> <!-- isset here checks whether the variable is set and not null-->
                <div>
                    Welcome, <strong><?php echo $_SESSION['username']; ?></strong> | 
                    <a href="logout.php" style="color: var(--cpl-red); text-decoration: none;">Logout</a>
                </div>
            <?php endif; ?>
        </div>
    </header>
    <!-- this is like a motivational qoute to encourage them to read books -->
    <section class="hero">
        <div class="container">
            <h1>Find Your Next Great Read</h1>
            <p>Discover, reserve, and enjoy books from our extensive collection</p>
        </div>
    </section>

    <div class="container">
        <nav class="main-nav">
            <ul class="nav-links">
                <li><a href="#search">Search Books</a></li>
                <li><a href="#reservations">My Reservations</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>

        <section id="search" class="form-section">
            <h2>Search Our Collection</h2>
            <form method="get">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Book Title</label>
                        <input type="text" name="title" class="form-control" value="<?php echo $_GET['title'] ?? ''; ?>" placeholder="Enter book title...">
                    </div>
                    <div class="form-group">
                        <label>Author</label>
                        <input type="text" name="author" class="form-control" value="<?php echo $_GET['author'] ?? ''; ?>" placeholder="Enter author name...">
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" class="form-control">
                            <option value="">All Categories</option>
                            <?php
                            $cats = $conn->query("SELECT * FROM Categories");
                            while ($cat = $cats->fetch_assoc()) { //fetch_assoc gets each row
                                $selected = ($_GET['category'] ?? '') == $cat['CategoryID'] ? 'selected' : '';
                                echo "<option value='{$cat['CategoryID']}' $selected>{$cat['CategoryDescription']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <button type="submit" name="search" class="btn btn-primary">Search Books</button>
            </form>

            <?php if (isset($_GET['search'])): ?>
                <div class="mt-3"> <!-- mt-3 is margin top 3 to give some space -->
                    <h3>Search Results (<?php echo $total_books; ?> books found)</h3>
                    
                    <?php if ($total_pages > 0): ?>
                        <div style="display: flex; justify-content: center; margin: 20px 0; gap: 10px;">
                            <?php if ($page > 1): ?>
                                <a href="?search=1&title=<?php echo $_GET['title'] ?? ''; ?>&author=<?php echo $_GET['author'] ?? ''; ?>&category=<?php echo $_GET['category'] ?? ''; ?>&page=<?php echo $page - 1; ?>" class="btn">Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="btn btn-primary"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?search=1&title=<?php echo $_GET['title'] ?? ''; ?>&author=<?php echo $_GET['author'] ?? ''; ?>&category=<?php echo $_GET['category'] ?? ''; ?>&page=<?php echo $i; ?>" class="btn"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?search=1&title=<?php echo $_GET['title'] ?? ''; ?>&author=<?php echo $_GET['author'] ?? ''; ?>&category=<?php echo $_GET['category'] ?? ''; ?>&page=<?php echo $page + 1; ?>" class="btn">Next</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="books-grid">
                        <?php foreach($paginated_books as $book): 
                            $reserved = $conn->query("SELECT * FROM Reservations WHERE ISBN='{$book['ISBN']}'");
                            $isReserved = $reserved->num_rows > 0;
                            $isMyReservation = false; //assumes false by default
                            
                            if ($isReserved) {  //if the book is reserved check if it is reserved by the logged in user
                                $myRes = $conn->query("SELECT * FROM Reservations WHERE ISBN='{$book['ISBN']}' AND Username='{$_SESSION['username']}'");
                                $isMyReservation = $myRes->num_rows > 0;
                            }
                        ?>
                        <div class="book-card">
                            <div class="book-title"><?php echo $book['BookTitle']; ?></div>
                            <div class="book-meta">
                                <strong>Author:</strong> <?php echo $book['Author']; ?><br>
                                <strong>Category:</strong> <?php echo $book['CategoryDescription']; ?><br>
                                <strong>Published:</strong> <?php echo $book['Year']; ?><br>
                                <strong>ISBN:</strong> <?php echo $book['ISBN']; ?>
                            </div>
                            <div class="book-actions">
                                <?php if ($isReserved): ?> 
                                    <?php if ($isMyReservation): ?>
                                        <span class="status-badge status-reserved">✅ Reserved by You</span>
                                    <?php else: ?>
                                        <span class="status-badge status-unavailable">❌ Already Reserved</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="status-badge status-available">✓ Available</span>
                                    <form method="post" class="mt-3">
                                        <input type="hidden" name="isbn" value="<?php echo $book['ISBN']; ?>">
                                        <button type="submit" name="reserve" class="btn btn-success">Reserve This Book</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (empty($books)): ?> <!-- if no books found it will output a message to the user -->
                        <div class="text-center" style="padding: 2rem; color: var(--cpl-gray);">
                            <p>No books found matching your search criteria.</p>
                            <p>Try adjusting your search terms or browse all categories.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>

        <section id="reservations" class="form-section">
            <h2>My Reserved Books</h2>
            <?php if (empty($reservations)): ?>
                <div class="text-center" style="padding: 2rem; color: var(--cpl-gray);">
                    <p>You haven't reserved any books yet.</p><!-- if no reservations found it will output a message to the user -->
                    <p>Search our collection to find your next great read!</p>
                </div>
            <?php else: ?>
                <div class="books-grid"><!-- reusing the same books-grid class for layout -->
                    <?php foreach($reservations as $res): ?>
                    <div class="book-card"><!-- reusing the same book-card class for styling -->
                        <div class="book-title"><?php echo $res['BookTitle']; ?></div>
                        <div class="book-meta">
                            <strong>Author:</strong> <?php echo $res['Author']; ?><br>
                            <strong>Category:</strong> <?php echo $res['CategoryDescription']; ?><br>
                            <strong>ISBN:</strong> <?php echo $res['ISBN']; ?><br>
                            <strong>Reserved:</strong> <?php echo $res['ReservedDate']; ?>
                        </div>
                        <div class="book-actions">
                            <form method="post">
                                <input type="hidden" name="isbn" value="<?php echo $res['ISBN']; ?>">
                                <button type="submit" name="cancel" class="btn btn-danger">Cancel Reservation</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?> 
                </div>
            <?php endif; ?>
        </section>
    </div>
    <!--javascript to handle tab switching  -->
    <script>
    // This makes links with #scroll smoothly instead of jumping
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            // Stop the normal link jump
            e.preventDefault();
            // Find the section we want to scroll to
            // Example: href="#search" finds <div id="search">
            var targetSection = document.querySelector(this.getAttribute('href'));
            // Scroll to it smoothly with animation
            targetSection.scrollIntoView({
                behavior: 'smooth' // Makes it animated instead of instant
            });
        });
    });
    </script>
    <footer class="footer">
        <div class="container">
            <p>Library System &copy; 2025</p>
        </div>
    </footer>
</body>
</html>