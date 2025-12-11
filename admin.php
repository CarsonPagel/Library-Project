<?php
session_start();
include 'database.php';

// Authentication check
if (!isset($_SESSION['userid'])) {
    die("You must log in first.");
}

$userID = $_SESSION['userid'];

// Fetch user information
$userQ = mysqli_query($conn, "SELECT firstname, lastname, is_admin FROM User WHERE UserID = '$userID'");
$userData = mysqli_fetch_assoc($userQ);
$isAdmin = intval($userData['is_admin']) === 1;

// Try to fetch librarian branch
$branchID = null;
$libQ = mysqli_query($conn, "SELECT BranchID FROM Librarian WHERE UserID = '$userID'");
if (mysqli_num_rows($libQ) > 0) {
    $branchID = mysqli_fetch_assoc($libQ)['BranchID'];
}

// Helper function
function h($s) {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Get current view/tab
$currentTab = isset($_GET['tab']) ? $_GET['tab'] : 'docs';
$currentView = isset($_GET['view']) ? $_GET['view'] : 'list';
$messages = [];
$docSearch = '';
$userSearch = '';
$copyStatusDocID = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Search documents
    if ($action === 'search_documents') {
        $docSearch = trim($_POST['q'] ?? '');
        $currentTab = 'docs';
        $currentView = 'list';
    }

    // Add copies
    if ($action === 'add_copies') {
        if ($branchID === null) {
            $messages[] = ['type' => 'error', 'text' => 'No branch assigned.'];
        } else {
            $docID = intval($_POST['docid']);
            $num = intval($_POST['num']);

            if ($num <= 0) {
                $messages[] = ['type' => 'error', 'text' => 'Please enter a valid number'];
            } else {
                // Get max copy number across ALL branches
                $maxQ = mysqli_query($conn, "SELECT MAX(CopyNum) AS m FROM Copy WHERE DocID = '$docID'");
                $max = mysqli_fetch_assoc($maxQ)['m'];
                if ($max === NULL) { $max = 0; }

                // Insert new copies
                $today = date("Y-m-d");
                for ($i = 1; $i <= $num; $i++) {
                    $newCopyNum = $max + $i;
                    mysqli_query($conn, "INSERT INTO Copy (DocID, BranchID, CopyNum, UserID, Date) VALUES ('$docID', '$branchID', '$newCopyNum', NULL, '$today')");
                }

                $messages[] = ['type' => 'success', 'text' => "Added $num copies successfully!"];
            }
        }
        $currentTab = 'docs';
        $currentView = 'list';
    }

    // Search users
    if ($action === 'search_users') {
        $userSearch = trim($_POST['q'] ?? '');
        $currentTab = 'branch';
    }
}

// Handle view copy status (via GET)
if (isset($_GET['view_copies'])) {
    $copyStatusDocID = intval($_GET['view_copies']);
    $currentTab = 'docs';
    $currentView = 'copies';
}

// Fetch documents
$documents = [];
if ($currentTab === 'docs' && $currentView === 'list') {
    $sql = "SELECT d.DocID, d.Title, d.Publisher,
        (SELECT COUNT(*) FROM Copy c 
         WHERE c.DocID = d.DocID AND c.BranchID = '$branchID'
         AND NOT EXISTS (SELECT 1 FROM Borrows b WHERE b.DocID = c.DocID AND b.CopyNum = c.CopyNum)
        ) AS copies_available
        FROM Document d";

    if ($docSearch !== "") {
        $filter = mysqli_real_escape_string($conn, $docSearch);
        $sql = $sql . " WHERE d.Title LIKE '%$filter%' OR d.Publisher LIKE '%$filter%' OR d.DocID LIKE '%$filter%'";
        $sql = $sql . " ORDER BY d.Title LIMIT 300";
    } else {
        $sql = $sql . " ORDER BY d.Title LIMIT 500";
    }
    $res = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($res)) { $documents[] = $row; }
}

// Fetch copy status if viewing copies
$copyStatusData = null;
if ($currentView === 'copies' && $copyStatusDocID > 0) {
    // Get document info
    $docQ = mysqli_query($conn, "SELECT Title, Publisher FROM Document WHERE DocID = '$copyStatusDocID'");
    $docInfo = mysqli_fetch_assoc($docQ);
    
    // Get all copies with their status
    $sql = "SELECT c.CopyNum, c.BranchID, lb.Address AS BranchAddress, b.UserID AS BorrowerID,
            u.firstname, u.lastname, b.BorrowDate, b.ReturnDate
            FROM Copy c
            LEFT JOIN Lib_Branch lb ON c.BranchID = lb.BranchID
            LEFT JOIN Borrows b ON c.DocID = b.DocID AND c.CopyNum = b.CopyNum
            LEFT JOIN User u ON b.UserID = u.UserID
            WHERE c.DocID = '$copyStatusDocID'
            ORDER BY c.BranchID, c.CopyNum";
    
    $res = mysqli_query($conn, $sql);
    $copies = [];
    while ($row = mysqli_fetch_assoc($res)) { $copies[] = $row; }
    
    $copyStatusData = ['docInfo' => $docInfo, 'copies' => $copies, 'docID' => $copyStatusDocID];
}

// Fetch branch info
$branchInfo = null;
if ($currentTab === 'branch' && isset($_GET['subtab']) && $_GET['subtab'] === 'binfo' && $branchID !== null) {
    $bQ = mysqli_query($conn, "SELECT BranchID, Address, PhoneNumber FROM Lib_Branch WHERE BranchID = '$branchID'");
    $branchInfo = mysqli_fetch_assoc($bQ);
}

// Fetch users
$users = [];
if ($currentTab === 'branch' && isset($_GET['subtab']) && $_GET['subtab'] === 'uinfo') {
    $sql = "SELECT * FROM User";

    if ($userSearch !== "") {
        if (ctype_digit($userSearch)) {
            $sql = $sql . " WHERE UserID = '$userSearch'";
            $sql = $sql . " LIMIT 200";
        } else {
            $qEsc = mysqli_real_escape_string($conn, $userSearch);
            $sql = $sql . " WHERE firstname LIKE '%$qEsc%' OR lastname LIKE '%$qEsc%'";
            $sql = $sql . " LIMIT 200";
        }
    } else {
        $sql = $sql . " LIMIT 500";
    }
    $r = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($r)) { $users[] = $row; }
}

// Fetch stats
$topBorrowers = [];
$topBooks = [];
$avgFine = 0;
if ($currentTab === 'stats') {
    // Top borrowers
    $tbR = mysqli_query($conn, "SELECT b.UserID, u.firstname, u.lastname, COUNT(*) AS borrowed
                                 FROM Borrows b JOIN User u ON u.UserID = b.UserID
                                 GROUP BY b.UserID ORDER BY borrowed DESC LIMIT 10");
    while ($row = mysqli_fetch_assoc($tbR)) { $topBorrowers[] = $row; }

    // Top books
    $bookR = mysqli_query($conn, "SELECT b.DocID, d.Title, d.Publisher, COUNT(*) AS times_borrowed
                                   FROM Borrows b JOIN Document d ON d.DocID = b.DocID
                                   GROUP BY b.DocID ORDER BY times_borrowed DESC LIMIT 10");
    while ($row = mysqli_fetch_assoc($bookR)) { $topBooks[] = $row; }

    // Average fine
    $fineR = mysqli_query($conn, "SELECT AVG(Fines) AS avgFine FROM Member");
    $avgFine = mysqli_fetch_assoc($fineR)['avgFine'];
}

// Set default subtab for branch management
$currentSubtab = isset($_GET['subtab']) ? $_GET['subtab'] : 'binfo';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Disruptive Library</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 1200px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .welcome-section {
            padding: 28px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .welcome-section h1 {
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .user-info {
            font-size: 0.95em;
            opacity: 0.95;
        }

        .content {
            padding: 32px 40px;
        }

        .tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 0;
        }

        .tab-link {
            display: inline-block;
            padding: 12px 20px;
            background: transparent;
            color: #666;
            text-decoration: none;
            border-bottom: 3px solid transparent;
            font-size: 0.95em;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .tab-link:hover {
            color: #667eea;
            background: #f5f5f5;
        }

        .tab-link.active {
            color: #667eea;
            border-bottom-color: #667eea;
            font-weight: 600;
        }

        .card {
            background: #fafafa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #eee;
            margin-bottom: 20px;
        }

        .card h3 {
            margin-bottom: 16px;
            color: #333;
            font-size: 1.1em;
        }

        .search-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
        }

        input[type="text"],
        input[type="number"] {
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.95em;
            font-family: inherit;
            flex: 1;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        button {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 0.95em;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        button:hover {
            transform: translateY(-2px);
        }

        button.small {
            padding: 6px 12px;
            font-size: 0.85em;
        }

        button.secondary {
            background: #6c757d;
        }

        a.button {
            display: inline-block;
            padding: 8px 12px;
            background: #2b6cb0;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s ease;
        }

        a.button:hover {
            transform: translateY(-2px);
        }

        a.button.secondary {
            background: #e53e3e;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 0.9rem;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        tr:hover {
            background: #f5f5f5;
        }

        .info-grid {
            display: grid;
            gap: 16px;
        }

        .info-item {
            padding: 12px;
            background: white;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
        }

        .info-label {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 4px;
            font-size: 0.85em;
        }

        .info-value {
            color: #333;
            font-size: 1em;
        }

        .stat-card {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            margin-bottom: 20px;
        }

        .stat-value {
            font-size: 2.5em;
            font-weight: 700;
            color: #667eea;
            margin: 10px 0;
        }

        .stat-label {
            font-size: 0.9em;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 600;
        }

        .status-available {
            background: #e6ffed;
            color: #22863a;
        }

        .status-checked-out {
            background: #ffecec;
            color: #d73a49;
        }

        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .messages {
            margin-bottom: 16px;
        }

        .messages .item {
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 8px;
        }

        .messages .success {
            background: #e6ffed;
            border: 1px solid #b7f0c8;
            color: #22863a;
        }

        .messages .error {
            background: #ffecec;
            border: 1px solid #f1b2b2;
            color: #d73a49;
        }

        .messages .info {
            background: #eef4ff;
            border: 1px solid #c7d9ff;
            color: #0366d6;
        }

        .add-copies-form {
            display: inline-block;
            margin-left: 10px;
        }

        .add-copies-form input {
            width: 80px;
            padding: 4px 8px;
            margin-right: 4px;
        }

        @media(max-width: 900px) {
            .container {
                max-width: 100%;
            }
            
            .tabs {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="welcome-section">
        <h1>Admin Panel</h1>
        <div class="user-info">
            Logged in as: <strong><?= h($userData['firstname'] . " " . $userData['lastname']) ?></strong>
            <?php if ($branchID !== null): ?>
                | Branch: <strong><?= h($branchID) ?></strong>
            <?php endif; ?>
        </div>
    </div>

    <div class="content">
        <!-- Messages -->
        <?php if (!empty($messages)): ?>
            <div class="messages">
                <?php foreach ($messages as $m): ?>
                    <div class="item <?= h($m['type']) ?>"><?= h($m['text']) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- TABS -->
        <div class="tabs">
            <a href="?tab=docs" class="tab-link <?= $currentTab === 'docs' ? 'active' : '' ?>">Document Management</a>
            <a href="?tab=branch&subtab=binfo" class="tab-link <?= $currentTab === 'branch' ? 'active' : '' ?>">Branch Management</a>
            <a href="?tab=stats" class="tab-link <?= $currentTab === 'stats' ? 'active' : '' ?>">Statistics</a>
        </div>

        <!-- DOCUMENT MANAGEMENT -->
        <?php if ($currentTab === 'docs'): ?>
            <?php if ($currentView === 'list'): ?>
                <div class="card">
                    <h3>Document Library</h3>
                    <p style="color: #666; font-size: 0.9em; margin-bottom: 12px;">Click on a document title to view copy status</p>
                    <form method="post" class="search-bar">
                        <input type="hidden" name="action" value="search_documents">
                        <input type="text" name="q" placeholder="Search by title, publisher, or document ID..." value="<?= h($docSearch) ?>">
                        <button type="submit" class="small">Search</button>
                    </form>

                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Publisher</th>
                                <th>Copies Available</th>
                                <th>Add Copies</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($documents)): ?>
                                <tr><td colspan="5" style="text-align:center; color:#999;">No documents found</td></tr>
                            <?php else: ?>
                                <?php foreach ($documents as $d): ?>
                                    <tr>
                                        <td><?= h($d['DocID']) ?></td>
                                        <td><a href="?tab=docs&view=copies&view_copies=<?= h($d['DocID']) ?>" style="color: #667eea; text-decoration: none; font-weight: 500;"><?= h($d['Title']) ?></a></td>
                                        <td><?= h($d['Publisher']) ?></td>
                                        <td><?= h($d['copies_available']) ?></td>
                                        <td>
                                            <form method="post" class="add-copies-form" style="display: inline-flex; align-items: center;">
                                                <input type="hidden" name="action" value="add_copies">
                                                <input type="hidden" name="docid" value="<?= h($d['DocID']) ?>">
                                                <input type="number" name="num" min="1" placeholder="#" required style="width: 60px;">
                                                <button type="submit" class="small">Add</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($currentView === 'copies' && $copyStatusData): ?>
                <div class="card">
                    <a href="?tab=docs" class="button" style="margin-bottom: 16px;">
                        ← Back to Document List
                    </a>
                    <h3>Copy Status: <?= h($copyStatusData['docInfo']['Title']) ?></h3>
                    <div style="color: #666; font-size: 0.9em; margin-bottom: 16px;">
                        Publisher: <?= h($copyStatusData['docInfo']['Publisher']) ?> | Document ID: <?= h($copyStatusData['docID']) ?>
                    </div>

                    <?php if (empty($copyStatusData['copies'])): ?>
                        <p style="color: #999;">No copies found for this document.</p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Copy #</th>
                                    <th>Branch ID</th>
                                    <th>Branch Address</th>
                                    <th>Status</th>
                                    <th>Borrower</th>
                                    <th>Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($copyStatusData['copies'] as $c): ?>
                                    <?php
                                    $isCheckedOut = $c['BorrowerID'] !== null;
                                    $statusBadge = $isCheckedOut 
                                        ? '<span class="status-badge status-checked-out">Checked Out</span>'
                                        : '<span class="status-badge status-available">Available</span>';
                                    $borrower = $isCheckedOut 
                                        ? h($c['firstname'] . ' ' . $c['lastname']) . ' (ID: ' . h($c['BorrowerID']) . ')'
                                        : '-';
                                    $dueDate = $isCheckedOut ? h($c['ReturnDate']) : '-';
                                    ?>
                                    <tr>
                                        <td><?= h($c['CopyNum']) ?></td>
                                        <td><?= h($c['BranchID']) ?></td>
                                        <td><?= h($c['BranchAddress']) ?></td>
                                        <td><?= $statusBadge ?></td>
                                        <td><?= $borrower ?></td>
                                        <td><?= $dueDate ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- BRANCH MANAGEMENT -->
        <?php if ($currentTab === 'branch'): ?>
            <div class="tabs">
                <a href="?tab=branch&subtab=binfo" class="tab-link <?= $currentSubtab === 'binfo' ? 'active' : '' ?>">Branch Information</a>
                <a href="?tab=branch&subtab=uinfo" class="tab-link <?= $currentSubtab === 'uinfo' ? 'active' : '' ?>">User Information</a>
            </div>

            <?php if ($currentSubtab === 'binfo'): ?>
                <div class="card">
                    <h3>Branch Details</h3>
                    <?php if ($branchInfo): ?>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Branch ID</div>
                                <div class="info-value"><?= h($branchInfo['BranchID']) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Address</div>
                                <div class="info-value"><?= h($branchInfo['Address']) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Phone Number</div>
                                <div class="info-value"><?= h($branchInfo['PhoneNumber']) ?></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p style="color: #e53e3e;">No branch assigned to this admin.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($currentSubtab === 'uinfo'): ?>
                <div class="card">
                    <h3>User Directory</h3>
                    <form method="post" class="search-bar">
                        <input type="hidden" name="action" value="search_users">
                        <input type="text" name="q" placeholder="Search by User ID or name..." value="<?= h($userSearch) ?>">
                        <button type="submit" class="small">Search</button>
                    </form>

                    <table>
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr><td colspan="4" style="text-align:center; color:#999;">No users found</td></tr>
                            <?php else: ?>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><?= h($u['UserID']) ?></td>
                                        <td><?= h($u['firstname'] . ' ' . $u['lastname']) ?></td>
                                        <td><?= h($u['Address']) ?></td>
                                        <td><?= $u['is_admin'] == 1 ? 'Yes' : 'No' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- STATISTICS -->
        <?php if ($currentTab === 'stats'): ?>
            <div class="card">
                <h3>Top Borrowers</h3>
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Books Borrowed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topBorrowers)): ?>
                            <tr><td colspan="3" style="text-align:center; color:#999;">No borrowing data available</td></tr>
                        <?php else: ?>
                            <?php foreach ($topBorrowers as $u): ?>
                                <tr>
                                    <td><?= h($u['UserID']) ?></td>
                                    <td><?= h($u['firstname'] . ' ' . $u['lastname']) ?></td>
                                    <td><?= h($u['borrowed']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h3>Top Books</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Doc ID</th>
                            <th>Title</th>
                            <th>Publisher</th>
                            <th>Times Borrowed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topBooks)): ?>
                            <tr><td colspan="4" style="text-align:center; color:#999;">No borrowing data available</td></tr>
                        <?php else: ?>
                            <?php foreach ($topBooks as $b): ?>
                                <tr>
                                    <td><?= h($b['DocID']) ?></td>
                                    <td><?= h($b['Title']) ?></td>
                                    <td><?= h($b['Publisher']) ?></td>
                                    <td><?= h($b['times_borrowed']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="stat-card">
                <div class="stat-label">Average Fine Per Reader</div>
                <div class="stat-value">$<?= number_format($avgFine, 2) ?></div>
            </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="actions">
            <a href="member.php" class="button">Reader Functions</a>
            <a href="logout.php" class="button secondary" style="margin-left: 8px;">Logout</a>
        </div>
    </div>
</div>

</body>
</html>