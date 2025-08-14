<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

function callAPI($action, $data = []) {
    $url = "https://api.mandbox.com/apitest/v1/contact.php?action=$action";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($curl);
    curl_close($curl);

    $response = str_replace(["<pre>", "</pre>"], "", $response);
    return json_decode($response, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        callAPI('add', [
            'fullname' => $_POST['fullname'],
            'address' => $_POST['address'],
            'contact_no' => $_POST['contact_no']
        ]);
    }

    if (isset($_POST['delete'])) {
        callAPI('delete', [
            'record_id' => $_POST['record_id']
        ]);
    }

    if (isset($_POST['save_update'])) {
        callAPI('update', [
            'record_id' => $_POST['record_id'],
            'fullname' => $_POST['fullname'],
            'address' => $_POST['address'],
            'contact_no' => $_POST['contact_no']
        ]);
    }
}

$edit = null;
if (isset($_POST['edit'])) {
    $edit = [
        'id' => $_POST['record_id'],
        'fullname' => $_POST['fullname'],
        'address' => $_POST['address'],
        'contact_no' => $_POST['contact_no']
    ];
}

$search_key = $_POST['search_key'] ?? $_GET['search_key'] ?? '';
$result = callAPI("view");
$contacts = isset($result['data']) && is_array($result['data']) ? $result['data'] : [];

if (!empty($search_key)) {
    $contacts = array_filter($contacts, function($contact) use ($search_key) {
        $search_key = strtolower($search_key);
        return strpos(strtolower($contact['fullname']), $search_key) !== false ||
               strpos(strtolower($contact['address']), $search_key) !== false ||
               strpos(strtolower($contact['contact_no']), $search_key) !== false;
    });
}

// Pagination | Show Filter
// $limit = 9;

$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int) $_GET['limit'] : 5;

$totalContacts = count($contacts);
$totalPages = ceil($totalContacts / $limit);
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max(1, min($page, $totalPages));
$offset = ($page - 1) * $limit;
$currentPageContacts = array_slice($contacts, $offset, $limit);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AniWorld | AnimeWorld</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
    html {
        margin: 0;
        padding: 0;
        height: 100vh;
        background-color: black;
    }

    body {
        background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('1.jpg');
        /* background-size: cover;   */
        background-position: center;
        background-repeat: no-repeat;
    }

    .show_filter, h2 {
        color: #fff;
        font-family: "Courier New", Courier, "Lucida Console", Monaco, monospace;
    }

    h2 {
        font-size: 50px;
        font-weight: bold;
    }

</style>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Welcome to AniWorld</h2>

    <div class="card mb-4">
        <div class="card-header"><?= $edit ? 'Update Contact' : 'Add New Aniname' ?></div>
        <div class="card-body">

            <!-- Search Form -->
            <form method="post" class="mb-3 d-flex" style="gap: 10px;">
                <input type="text" name="search_key" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($search_key) ?>">
                <button type="submit" class="btn btn-info">Search</button>
            </form>

            <!-- Contact Form -->
            <form method="post">
                <?php if ($edit): ?>
                    <input type="hidden" name="record_id" value="<?= $edit['id'] ?>">
                <?php endif; ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label>Full Name</label>
                        <input type="text" name="fullname" class="form-control" required value="<?= $edit['fullname'] ?? '' ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Address</label>
                        <input type="text" name="address" class="form-control" required value="<?= $edit['address'] ?? '' ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Contact No.</label>
                        <input type="text" name="contact_no" class="form-control" required value="<?= $edit['contact_no'] ?? '' ?>">
                    </div>
                </div>
                <div class="mt-3">
                    <?php if ($edit): ?>
                        <button name="save_update" class="btn btn-primary">Update</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    <?php else: ?>
                        <button name="add" class="btn btn-success">Submit</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Pagination Form -->

      <!-- <?php 
            for ($i = 1; $i <= $totalPages; $i++) {
    $active = ($i == $page) ? 'active' : '';
    echo "<a href='?page=$i&limit=$limit' class='$active'>$i</a> ";
}

            ?> -->
    <?php if ($totalPages > 1): ?>
        <nav class="mt-5 ">
            <ul class="pagination justify-content-center">
                <?php
                $queryStr = http_build_query(['search_key' => $search_key]);
                ?>

                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= $queryStr ?>&page=<?= $page - 1 ?>">Previous</a>
                </li>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= $queryStr ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= $queryStr ?>&page=<?= $page + 1 ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>


    <!-- Filter Form -->
    <!-- this is the table for filteration -->
            <form method="get" id="limitForm" class="show_filter">
                <b>Show Filter</b> 
                <select name="limit" onchange="document.getElementById('limitForm').submit()">
                    <option value="5" <?= (isset($_GET['limit']) && $_GET['limit']==5)?'selected':'' ?>>5</option>
                    <option value="10" <?= (isset($_GET['limit']) && $_GET['limit']==10)?'selected':'' ?>>10</option>
                    <option value="15" <?= (isset($_GET['limit']) && $_GET['limit']==15)?'selected':'' ?>>15</option>
                </select> entries
            </form>


    <!-- Data List Forms -->
    <div class="card">
        <div class="card-header">Anime List</div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Address</th>
                        <th>Contact No.</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($currentPageContacts)): ?>
                        <?php foreach ($currentPageContacts as $contact): ?>
                            <tr>
                                <td><?= htmlspecialchars($contact['id']) ?></td>
                                <td><?= htmlspecialchars($contact['fullname']) ?></td>
                                <td><?= htmlspecialchars($contact['address']) ?></td>
                                <td><?= htmlspecialchars($contact['contact_no']) ?></td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="record_id" value="<?= $contact['id'] ?>">
                                        <input type="hidden" name="fullname" value="<?= htmlspecialchars($contact['fullname']) ?>">
                                        <input type="hidden" name="address" value="<?= htmlspecialchars($contact['address']) ?>">
                                        <input type="hidden" name="contact_no" value="<?= htmlspecialchars($contact['contact_no']) ?>">

                                        <button name="edit" class="btn btn-warning btn-sm">Edit</button>
                                        <button name="delete" class="btn btn-danger btn-sm" onclick="return confirm('Delete this contact?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No contacts found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Form -->
    <?php if ($totalPages > 1): ?>
        <nav class="mt-5 ">
            <ul class="pagination justify-content-center">
                <?php
                $queryStr = http_build_query(['search_key' => $search_key]);
                ?>

                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= $queryStr ?>&page=<?= $page - 1 ?>">Previous</a>
                </li>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= $queryStr ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= $queryStr ?>&page=<?= $page + 1 ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>
</body>
</html>
