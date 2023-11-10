<?php
session_start();
include('includes/config.php');

if (isset($_GET['viewid'])) {
    $viewId = $_GET['viewid'];

    // Fetch the details of the selected homeless user
    $sql = "SELECT h.*, s.sponsor_id, u.name AS sponsor_name, sp.start_date, sp.end_date
            FROM homeless_registration h
            LEFT JOIN sponsorship s ON h.id = s.homeless_person_id
            LEFT JOIN users u ON s.sponsor_id = u.id
            LEFT JOIN sponsorship sp ON h.id = sp.homeless_person_id
            WHERE h.id=:viewId";
    $query = $dbh->prepare($sql);
    $query->bindParam(':viewId', $viewId, PDO::PARAM_INT);
    $query->execute();
    $userDetails = $query->fetch(PDO::FETCH_ASSOC);
}

// Fetch all homeless users
$homelessUsers = array();

$homelessSql = "SELECT h.*, s.sponsor_id, u.name AS sponsor_name
               FROM homeless_registration h
               LEFT JOIN sponsorship s ON h.id = s.homeless_person_id
               LEFT JOIN users u ON s.sponsor_id = u.id";

$homelessQuery = $dbh->prepare($homelessSql);
$homelessQuery->execute();

while ($user = $homelessQuery->fetch(PDO::FETCH_ASSOC)) {
    $homelessUsers[] = $user;
}

// Handle sponsorship confirmation
if (isset($_POST['confirmSponsorship'])) {
    $homelessId = $_POST['homelessId'];
    $sponsorId = $_POST['sponsorId'];
    $startDate = $_POST['startDate']; // Get the start date from the form
    $endDate = $_POST['endDate']; // Get the end date from the form

    // Insert sponsorship record into the database with user-entered start and end dates
    $sponsorshipSql = "INSERT INTO sponsorship (sponsor_id, homeless_person_id, start_date, end_date) VALUES (:sponsorId, :homelessId, :startDate, :endDate)";
    $sponsorshipQuery = $dbh->prepare($sponsorshipSql);
    $sponsorshipQuery->bindParam(':sponsorId', $sponsorId, PDO::PARAM_INT);
    $sponsorshipQuery->bindParam(':homelessId', $homelessId, PDO::PARAM_INT);
    $sponsorshipQuery->bindParam(':startDate', $startDate, PDO::PARAM_STR);
    $sponsorshipQuery->bindParam(':endDate', $endDate, PDO::PARAM_STR);
    $sponsorshipQuery->execute();

    // You can add more logic or redirect the user as needed
    // For now, let's reload the page
    header("Location: homelessList.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en" class="no-js">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="theme-color" content="#3e454c">

    <title>Homeless List</title>

    <!-- Font awesome -->
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <!-- Sandstone Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Bootstrap Datatables -->
    <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
    <!-- Bootstrap social button library -->
    <link rel="stylesheet" href="css/bootstrap-social.css">
    <!-- Bootstrap select -->
    <link rel="stylesheet" href="css/bootstrap-select.css">
    <!-- Bootstrap file input -->
    <link rel="stylesheet" href="css/fileinput.min.css">
    <!-- Awesome Bootstrap checkbox -->
    <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
    <!-- Admin Style -->
    <link rel="stylesheet" href="css/style.css">

    <style>
        .errorWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #dd3d36;
            color: #fff;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
            box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
        }

        .succWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #5cb85c;
            color: #fff;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
            box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
        }

h4 {
text-align: center;
color: #34bcaa;
}

th {
text-align: center;
color: #34bcaa;
}

.table {
    width: 100%;
    max-width: 100%;
    margin-bottom: 20px;
    color: #debf12;
}

.card-section {
    text-align: center;
    margin: 0 auto;
    background: #333;
    color: #debf12;
    border-radius: 6px;
    padding: 20px;
    box-shadow: 0 3px 9px rgba(0, 0, 0, 0.5);
    width: 80%; /* Adjust the width as needed */
}

.card-section h4 {
    font-size: 18px;
    margin-bottom: 10px;
    color: #debf12;
}

.card-section p {
    font-size: 16px;
    margin: 10px 0;
}

.card-section form {
    margin-top: 20px;
}

.card-section .form-group {
    text-align: left;
}

.card-section label {
    display: block;
    font-weight: bold;
    margin: 10px 0;
    color: #debf12;
}

.card-section input[type="date"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #34bcaa;
    border-radius: 5px;
    background: #333;
    color: #debf12;
}

.card-section button {
    padding: 10px 20px;
    font-weight: bold;
    margin: 10px 5px;
    border: none;
    border-radius: 25px;
    cursor: pointer;
}

.card-section .btn-primary {
    background-color: #34bcaa;
    color: #fff;
    box-shadow: 0 0 10px #debf12;
}

.card-section .btn-default {
    background-color: #34bcaa;
    color: #fff;
    box-shadow: 0 0 10px #debf12;
}

.card-section .btn-primary:hover {
    background-color: #debf12;
    box-shadow: 0 0 10px #34bcaa;
    color: #fff;
}

.card-section .btn-default:hover {
    background-color: #debf12;
    box-shadow: 0 0 10px #34bcaa;
    color: #fff;
}

    </style>
</head>

<body>
    <?php include('includes/header.php'); // Include your header ?>
    <div class="ts-main-content">
        <?php include('includes/leftbar.php'); // Include your left sidebar ?>
        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <h3 class="page-title" style="color: #debf12">Manage Homeless Users</h3>
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <h4>Sponsor Today!</h4>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Sponsor</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($homelessUsers as $user) : ?>
                                            <tr>
                                                <td><?php echo $user['name']; ?></td>
                                                <td>
                                                    <?php
                                                    if (!empty($user['sponsor_name'])) {
                                                        echo $user['sponsor_name'];
                                                    } else {
                                                        echo "No Sponsor";
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="homelessList.php?viewid=<?php echo $user['id']; ?>">View Details</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
<?php if (isset($userDetails)) : ?>
    <div class="card-section">
        <h4>Homeless User Details</h4>
        <p><strong>Name:</strong> <?php echo $userDetails['name']; ?></p>
        <p><strong>Gender:</strong> <?php echo $userDetails['gender']; ?></p>
        <p><strong>Age:</strong> <?php echo $userDetails['age']; ?></p>
        <p><strong>Special Requirements:</strong> <?php echo $userDetails['special_requirements']; ?></p>
        <!-- Display Start Date and End Date for sponsorship -->
        <p><strong>Start Date:</strong> <?php echo $userDetails['start_date']; ?></p>
        <p><strong>End Date:</strong> <?php echo $userDetails['end_date']; ?></p>

        <!-- Sponsorship Form -->
<form method="POST" action="homelessList.php">
    <input type="hidden" name="homelessId" value="<?php echo $userDetails['id']; ?>">
    <input type="hidden" name="sponsorId" value="1"> <!-- Replace with the actual sponsor's ID -->

    <!-- Input field for Start Date -->
    <div class="form-group">
        <label for="startDate">Start Date:</label>
        <input type="date" name="startDate" id="startDate" required>
    </div>

    <!-- Input field for End Date -->
    <div class="form-group">
        <label for="endDate">End Date:</label>
        <input type="date" name="endDate" id="endDate" required>
    </div>

    <button type="submit" name="confirmSponsorship" class="btn btn-primary">Confirm Sponsorship</button>
    <button type="button" class="btn btn-default" id="closeDetails">Close</button>

</form>
    </div>
<?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
document.getElementById("closeDetails").addEventListener("click", function() {
  // You can close any details or perform other actions here if needed

  // Redirect to the "homelessList.php" page
  window.location.href = "homelessList.php";
});
</script>

    <!-- Loading Scripts -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap-select.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/dataTables.bootstrap.min.js"></script>
    <script src="js/Chart.min.js"></script>
    <script src="js/fileinput.js"></script>
    <script src="js/chartData.js"></script>
    <script src="js/main.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            setTimeout(function () {
                $('.succWrap').slideUp("slow");
            }, 3000);
        });
    </script>
    <!-- Sponsorship Modal -->
    <?php foreach ($homelessUsers as $user) : ?>
        <div id="sponsorModal_<?php echo $user['id']; ?>" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Sponsor Homeless User</h4>
                    </div>
                    <div class="modal-body">
                        <!-- Add your sponsorship form here -->
                        <p>Form to confirm sponsorship will go here.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="button-default" data-dismiss="modal">Close</button>
                        <!-- Add a button to confirm sponsorship -->
                        <button type="button" class="button-primary" onclick="confirmSponsorship()">Confirm Sponsorship</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</body>

</html>
