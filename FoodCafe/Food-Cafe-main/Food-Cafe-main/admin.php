<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS (version 5) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Admin</title>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#Welcome">
            <img src="images/foodfusion.png" width="50" height="50" class="d-inline-block" alt=""> Food Fusion
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ml-auto ">
                <li class="nav-item active">
                    <a class="nav-link" href="website.php#Welcome">Welcome</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="website.php#AboutUs">About Us</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link disabled" href="website.php#Menu">Menu</a>
                </li>
                <li class="nav-item">
					<a class="nav-link" href="website.php#Location">Location</a>
				</li>
                <li class="nav-item">
					<a class="nav-link" href="delete.php">Delete Entry</a>
				</li>
            </ul>
            
        </div>
    </nav>

    <div class="container">
        <h3 class="text-center mt-3">Booking Details</h3>
        <?php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database = "customer_records";

        $conn = mysqli_connect($servername, $username, $password, $database);

        if (!$conn) {
            die("Sorry we failed to connect: " . mysqli_connect_error());
        } 

        $sql = "SELECT * FROM reservation";
        $result = mysqli_query($conn, $sql);

        $num = mysqli_num_rows($result);
        echo $num . " Records in database<br>";
        ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Contact_No</th>
                    <th scope="col">Table_No</th>
                    <th scope="col">Date</th>
                    <th scope="col">Time</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php if ($num > 0) {
                    while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?php echo $row['Name'] ?></td>
                            <td><?php echo $row['Email'] ?></td>
                            <td><?php echo $row['Contact_No'] ?></td>
                            <td><?php echo $row['table'] ?></td>
                            <td><?php echo $row['Date'] ?></td>
                            <td><?php echo $row['Time'] ?></td>
                            
                            

                        </tr>
                <?php }
                } ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS (version 5) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>

</body>
</html>
