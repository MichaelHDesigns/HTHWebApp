<?php
session_start();
include('includes/config.php');
require_once('lib/pdo_db.php');
require_once('models/Transaction.php');

// Instantiate Transaction
$transaction = new Transaction();

// Get Transaction
$transactions = $transaction->getTransactions();

// Include web3.js
echo '<script src="https://cdn.jsdelivr.net/npm/web3@1.6.0/dist/web3.min.js"></script>';

// Define your contract's address
$contractAddress = '0x5F89e9052b94E86eDd05EDb219CbAd40bA4A8B75';

// Include your contract's ABI
$contractABI = json_decode(file_get_contents('./abi/donations.json'));

// Instantiate a web3 provider
echo '<script>
var web3 = new Web3(Web3.givenProvider || "https://rpc0.altcoinchain.org/rpc");
</script>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

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
            margin: 20px 0;
            background: #dd3d36;
            color: #fff;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
            box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
        }

        .succWrap {
            padding: 10px;
            margin: 20px 0;
            background: #5cb85c;
            color: #fff;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
            box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
        }

        h2 {
            text-align: center;
            color: #debf12;
            margin-top: 20px; /* Add top margin to headings */
            margin-left: 20%;
        }

        h4 {
            text-align: center;
            color: #34bcaa;
            margin-top: 20px; /* Add top margin to headings */
        }

        .page-title {
            padding-bottom: 15px;
            margin-bottom: 20px;
            border-bottom: 1px solid #debf12;
            margin-left: 20%;     
        }

        th {
            text-align: center;
            color: #34bcaa;
        }

        .table {
            width: 80%;
            max-width: 90%; /* Reduce the max-width for tables */
            margin: 20px auto; /* Center-align the tables */
            margin-left: 20%;
            color: #debf12;
            border-color: #34bcaa;
            border: 2px solid #34bcaa;
            box-shadow: 0 0 10px #debf12;
        }

        .card-section {
            text-align: center;
            margin: 20px auto;
            background: #333;
            color: #debf12;
            border-radius: 6px;
            padding: 20px;
            box-shadow: 0 3px 9px rgba(0, 0, 0, 0.5);
            max-width: 80%; /* Adjust the width as needed */
        }

    </style>

    <title>View Transactions</title>
</head>
<body>
    <!-- Include your website header or navigation here -->
    <?php include('includes/header.php'); ?>
    <div class="ts-main-content">
        <!-- Include your left sidebar or any content here -->
        <?php include('includes/leftbar.php'); ?>
    </div>
    <div class="container-fluid">
</br>
</br>
</br>
        <div class="row">
            <div class="col-md-12">
                <h3 class="page-title" style="color: #debf12">HTH Donors List</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($transactions as $t): ?>
                            <tr>
                                <td><?php echo $t->id; ?></td>
                                <td><?php echo $t->customer_id; ?></td>
                                <td><?php echo $t->product; ?></td>
                                <td><?php echo sprintf('%.2f', $t->amount / 100); ?> <?php echo strtoupper($t->currency); ?></td>
                                <td><?php echo $t->created_at; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h3 class="page-title" style="color: #debf12">HTHW Donors List</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Donor Address</th>
                            <th style="text-align: left">Donation Amount (HTHW)</th>
                        </tr>
                    </thead>
                    <tbody id="donorList">
                        <!-- Donation list will be displayed here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Initialize the contract instance
        const contract = new web3.eth.Contract(<?php echo json_encode($contractABI); ?>, '<?php echo $contractAddress; ?>');

        // Function to display the list of donations
        async function displayDonations() {
            const donorList = document.getElementById('donorList');
            donorList.innerHTML = '';

            const donors = await contract.methods.showAllDonators().call();
            for (const donor of donors) {
                const donationAmount = await contract.methods.showDonationSum(donor).call();
                const row = document.createElement('tr');
                const addressCell = document.createElement('td');
                const amountCell = document.createElement('td');

                addressCell.innerText = donor;
                amountCell.innerText = web3.utils.fromWei(donationAmount, 'ether');

                row.appendChild(addressCell);
                row.appendChild(amountCell);

                donorList.appendChild(row);
            }
        }

        // Call the function to display the list of donations when the page loads
        displayDonations();
    </script>
  
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
</body>
</html>
