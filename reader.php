<?php
// Enable full error reporting for debugging (remove in production)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
session_start();
include 'database.php';

if (empty($_SESSION['userid'])) {
    header('Location: index.php');
    exit;
}

$userId = (int) $_SESSION['userid'];
$messages = [];

function h($s)
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

if (!$conn) {
    die('Database connection error');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'search') {
        $type = $_POST['search_type'] ?? 'title';
        $q = trim($_POST['q'] ?? '');

        if ($type === 'id') {
            $did = (int) $q;
            $stmt = mysqli_prepare($conn, 'SELECT DocID, Title, Publisher FROM Document WHERE DocID = ? LIMIT 100');
            mysqli_stmt_bind_param($stmt, 'i', $did);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $search_results = mysqli_fetch_all($res, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
        } else if ($type === 'title') {
            $like = '%' . $q . '%';
            $stmt = mysqli_prepare($conn, 'SELECT DocID, Title, Publisher FROM Document WHERE Title LIKE ? LIMIT 200');
            mysqli_stmt_bind_param($stmt, 's', $like);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $search_results = mysqli_fetch_all($res, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
        } else { // publisher
            $like = '%' . $q . '%';
            $stmt = mysqli_prepare($conn, 'SELECT DocID, Title, Publisher FROM Document WHERE Publisher LIKE ? LIMIT 200');
            mysqli_stmt_bind_param($stmt, 's', $like);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $search_results = mysqli_fetch_all($res, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
        }

    } elseif ($action === 'checkout') {
        $docid = isset($_POST['docid_checkout']) ? (int) $_POST['docid_checkout'] : 0;
        if ($docid <= 0) {
            $messages[] = ['type' => 'error', 'text' => 'Invalid Document ID'];
        } else {
            // find an available copy
            $stmt = mysqli_prepare($conn, 'SELECT c.CopyNum FROM Copy c WHERE c.DocID = ? AND NOT EXISTS (SELECT 1 FROM Borrows b WHERE b.DocID = c.DocID AND b.CopyNum = c.CopyNum) LIMIT 1');
            mysqli_stmt_bind_param($stmt, 'i', $docid);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);

            if (!$row) {
                $messages[] = ['type' => 'error', 'text' => 'No available copies to checkout for this document.'];
            } else {
                $copyNum = (int) $row['CopyNum'];
                $stmt = mysqli_prepare($conn, 'INSERT INTO Borrows (DocID, CopyNum, UserID, BorrowDate, ReturnDate) VALUES (?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY))');
                mysqli_stmt_bind_param($stmt, 'iii', $docid, $copyNum, $userId);
                $ok = mysqli_stmt_execute($stmt);
                if ($ok) {
                    $messages[] = ['type' => 'success', 'text' => "Checked out DocID $docid copy #$copyNum. Due in 14 days."];
                } else {
                    $messages[] = ['type' => 'error', 'text' => 'Failed to checkout (possible borrow limit or DB error).'];
                }
                mysqli_stmt_close($stmt);
            }
        }

    } elseif ($action === 'return') {
        $docid = isset($_POST['docid_return']) ? (int) $_POST['docid_return'] : 0;
        $copyNum = isset($_POST['copynum_return']) ? (int) $_POST['copynum_return'] : 0;
        if ($docid <= 0 || $copyNum < 0) {
            $messages[] = ['type' => 'error', 'text' => 'Invalid Document or Copy number'];
        } else {
            $stmt = mysqli_prepare($conn, 'DELETE FROM Borrows WHERE DocID = ? AND CopyNum = ? AND UserID = ?');
            mysqli_stmt_bind_param($stmt, 'iii', $docid, $copyNum, $userId);
            mysqli_stmt_execute($stmt);
            $affected = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            if ($affected > 0) {
                $messages[] = ['type' => 'success', 'text' => "Returned DocID $docid copy #$copyNum."];
            } else {
                $messages[] = ['type' => 'error', 'text' => 'No matching borrow record found for this user/doc/copy.'];
            }
        }

    } elseif ($action === 'fine') {
        $docid = isset($_POST['docid_fine']) ? (int) $_POST['docid_fine'] : 0;
        $copyNum = isset($_POST['copynum_fine']) ? (int) $_POST['copynum_fine'] : 0;
        $checkUser = $userId;
        if ($docid <= 0 || $copyNum < 0) {
            $messages[] = ['type' => 'error', 'text' => 'Invalid Document or Copy number'];
        } else {
            $stmt = mysqli_prepare($conn, 'SELECT ReturnDate FROM Borrows WHERE DocID = ? AND CopyNum = ? AND UserID = ? LIMIT 1');
            mysqli_stmt_bind_param($stmt, 'iii', $docid, $copyNum, $checkUser);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);
            if (!$row) {
                $messages[] = ['type' => 'error', 'text' => 'No borrow record found for this user/doc/copy.'];
            } else {
                $returnDate = $row['ReturnDate'];
                $stmt = mysqli_prepare($conn, 'SELECT GREATEST(DATEDIFF(CURDATE(), ?), 0) AS overdue');
                mysqli_stmt_bind_param($stmt, 's', $returnDate);
                mysqli_stmt_execute($stmt);
                $res2 = mysqli_stmt_get_result($stmt);
                $r2 = mysqli_fetch_assoc($res2);
                mysqli_stmt_close($stmt);
                $overdue = (int) $r2['overdue'];
                $rate = 0.5; // $0.50 per overdue day
                $fine = $overdue * $rate;
                $messages[] = ['type' => 'info', 'text' => "Overdue days: $overdue. Fine: $" . number_format($fine, 2)];
            }
        }

    } elseif ($action === 'by_publisher') {
        $pub = trim($_POST['publisher_q'] ?? '');
        if ($pub === '') {
            $messages[] = ['type' => 'error', 'text' => 'Provide a publisher name'];
        } else {
            $like = '%' . $pub . '%';
            $stmt = mysqli_prepare($conn, 'SELECT DocID, Title FROM Document WHERE Publisher LIKE ? ORDER BY DocID LIMIT 500');
            mysqli_stmt_bind_param($stmt, 's', $like);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $by_publisher_results = mysqli_fetch_all($res, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
        }

    }
}

// Close DB connection at end of script (will be reopened by other pages as needed)
// mysqli_close($conn);

// Fetch current user's borrows to show 'My Checkouts'
$my_borrows = [];
$stmt = mysqli_prepare($conn, 'SELECT b.DocID, d.Title, b.CopyNum, b.BorrowDate, b.ReturnDate FROM Borrows b JOIN Document d ON b.DocID = d.DocID WHERE b.UserID = ? ORDER BY b.BorrowDate DESC');
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $my_borrows = mysqli_fetch_all($res, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}

// Fetch documents with availability counts to show 'All Documents (availability)'
$available_docs = [];
$stmt = mysqli_prepare($conn, 'SELECT d.DocID, d.Title, d.Publisher, COALESCE((SELECT COUNT(*) FROM Copy c WHERE c.DocID = d.DocID),0) AS total_copies, COALESCE((SELECT COUNT(*) FROM Borrows br WHERE br.DocID = d.DocID),0) AS borrowed FROM Document d ORDER BY d.DocID LIMIT 1000');
if ($stmt) {
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $available_docs = mysqli_fetch_all($res, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reader Functions - Disruptive Library</title>
    <style>
        * {
            box-sizing: border-box
        }

        body {
            font-family: 'Segoe UI', Tahoma, Helvetica, Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center
        }

        .container {
            width: 100%;
            max-width: 1000px;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25)
        }

        .welcome-section {
            padding: 24px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff
        }

        .welcome-section h1 {
            margin: 0;
            font-size: 1.5rem
        }

        .content {
            padding: 22px 28px
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px
        }

        .card {
            background: #fafafa;
            padding: 14px;
            border-radius: 8px;
            border: 1px solid #eee
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600
        }

        input[type=text],
        input[type=number],
        select {
            width: 100%;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ddd
        }

        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 10px 12px;
            border: 0;
            border-radius: 8px;
            cursor: pointer
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px
        }

        th,
        td {
            padding: 6px;
            border: 1px solid #eee;
            text-align: left
        }

        .messages {
            margin-bottom: 12px
        }

        .messages .item {
            padding: 8px;
            border-radius: 6px;
            margin-bottom: 6px
        }

        .messages .success {
            background: #e6ffed;
            border: 1px solid #b7f0c8
        }

        .messages .error {
            background: #ffecec;
            border: 1px solid #f1b2b2
        }

        .messages .info {
            background: #eef4ff;
            border: 1px solid #c7d9ff
        }

        .actions {
            margin-top: 12px
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="welcome-section">
            <h1>Reader Functions</h1>
        </div>
        <div class="content">
            <div class="messages">
                <?php foreach ($messages as $m): ?>
                    <div class="item <?php echo h($m['type']); ?>"><?php echo h($m['text']); ?></div>
                <?php endforeach; ?>
            </div>

            <div class="grid">
                <div class="card">
                    <h3>Search Document</h3>
                    <form method="post">
                        <input type="hidden" name="action" value="search">
                        <label>Search by</label>
                        <select name="search_type">
                            <option value="title">Title</option>
                            <option value="publisher">Publisher</option>
                            <option value="id">Document ID</option>
                        </select>
                        <label style="margin-top:8px">Query</label>
                        <input type="text" name="q" required>
                        <div class="actions"><button type="submit">Search</button></div>
                    </form>

                    <?php if (!empty($search_results)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>DocID</th>
                                    <th>Title</th>
                                    <th>Publisher</th>
                                    <th>Total Copies</th>
                                    <th>Available</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($search_results as $r):
                                    $did = (int) $r['DocID'];
                                    $stmt = mysqli_prepare($conn, 'SELECT COUNT(*) AS cnt FROM Copy WHERE DocID = ?');
                                    mysqli_stmt_bind_param($stmt, 'i', $did);
                                    mysqli_stmt_execute($stmt);
                                    $resC = mysqli_stmt_get_result($stmt);
                                    $tc = mysqli_fetch_assoc($resC)['cnt'];
                                    mysqli_stmt_close($stmt);
                                    $stmt = mysqli_prepare($conn, 'SELECT COUNT(*) AS cnt FROM Borrows WHERE DocID = ?');
                                    mysqli_stmt_bind_param($stmt, 'i', $did);
                                    mysqli_stmt_execute($stmt);
                                    $resB = mysqli_stmt_get_result($stmt);
                                    $bc = mysqli_fetch_assoc($resB)['cnt'];
                                    mysqli_stmt_close($stmt);
                                    $avail = max(0, $tc - $bc);
                                    ?>
                                    <tr>
                                        <td><?php echo h($r['DocID']); ?></td>
                                        <td><?php echo h($r['Title']); ?></td>
                                        <td><?php echo h($r['Publisher']); ?></td>
                                        <td><?php echo h($tc); ?></td>
                                        <td><?php echo h($avail); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h3>Checkout Document</h3>
                    <form method="post">
                        <input type="hidden" name="action" value="checkout">
                        <label>Document ID</label>
                        <input type="number" name="docid_checkout" required>
                        <div class="actions"><button type="submit">Checkout</button></div>
                    </form>

                    <hr>
                    <h3>Return Document</h3>
                    <form method="post">
                        <input type="hidden" name="action" value="return">
                        <label>Document ID</label>
                        <input type="number" name="docid_return" required>
                        <label>Copy Number</label>
                        <input type="number" name="copynum_return" required>
                        <div class="actions"><button type="submit">Return</button></div>
                    </form>

                    <hr>
                    <h3>Compute Fine</h3>
                    <form method="post">
                        <input type="hidden" name="action" value="fine">
                        <label>Document ID</label>
                        <input type="number" name="docid_fine" required>
                        <label>Copy Number</label>
                        <input type="number" name="copynum_fine" required>
                        <div class="actions"><button type="submit">Compute Fine</button></div>
                    </form>
                </div>
            </div>

            <div style="margin-top:18px" class="card">
                <h3>Documents By Publisher</h3>
                <form method="post">
                    <input type="hidden" name="action" value="by_publisher">
                    <label>Publisher name (partial allowed)</label>
                    <input type="text" name="publisher_q" required>
                    <div class="actions"><button type="submit">Show Documents</button></div>
                </form>

                <?php if (!empty($by_publisher_results)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>DocID</th>
                                <th>Title</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($by_publisher_results as $p): ?>
                                <tr>
                                    <td><?php echo h($p['DocID']); ?></td>
                                    <td><?php echo h($p['Title']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div style="margin-top:18px" class="card">
                <h3>My Checkouts</h3>
                <?php if (!empty($my_borrows)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>DocID</th>
                                <th>Title</th>
                                <th>Copy#</th>
                                <th>Borrowed</th>
                                <th>Due</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_borrows as $mb): ?>
                                <tr>
                                    <td><?php echo h($mb['DocID']); ?></td>
                                    <td><?php echo h($mb['Title']); ?></td>
                                    <td><?php echo h($mb['CopyNum']); ?></td>
                                    <td><?php echo h($mb['BorrowDate']); ?></td>
                                    <td><?php echo h($mb['ReturnDate']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="item info">You have no current checkouts.</div>
                <?php endif; ?>
            </div>

            <div style="margin-top:18px" class="card">
                <h3>All Documents (availability)</h3>
                <?php if (!empty($available_docs)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>DocID</th>
                                <th>Title</th>
                                <th>Publisher</th>
                                <th>Total</th>
                                <th>Borrowed</th>
                                <th>Available</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($available_docs as $ad):
                                $total = (int) $ad['total_copies'];
                                $borrowed = (int) $ad['borrowed'];
                                $avail = max(0, $total - $borrowed);
                                ?>
                                <tr>
                                    <td><?php echo h($ad['DocID']); ?></td>
                                    <td><?php echo h($ad['Title']); ?></td>
                                    <td><?php echo h($ad['Publisher']); ?></td>
                                    <td><?php echo h($total); ?></td>
                                    <td><?php echo h($borrowed); ?></td>
                                    <td><?php echo h($avail); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="item info">No documents found.</div>
                <?php endif; ?>
            </div>

            <div style="margin-top:14px;text-align:right">
                <a href="logout.php" style="text-decoration:none"><button>Quit (Log out)</button></a>
            </div>
        </div>
    </div>
</body>

</html>