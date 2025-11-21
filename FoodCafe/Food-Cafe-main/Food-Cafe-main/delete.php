<?php
$result = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "dbconnect.php";

    // Check if 'Name' and 'Email' are set in the $_POST array
    $Name = isset($_POST['Name']) ? $_POST['Name'] : '';
    $Email = isset($_POST['Email']) ? $_POST['Email'] : '';

    if ($Name && $Email) {
        $sql = "DELETE FROM `reservation` WHERE `Name`='$Name' AND `Email`='$Email';";
        $result = mysqli_query($conn, $sql);

        if ($result == true) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> Your Account is now logged out.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <a href="admin.php"><button type="button" class="btn btn-primary float-right">OK</button></a>
            </div>';
        } 
    } 
    else {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> Unable to log out. Please try again.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>';
    }
}
?>
<!-- ... rest of your HTML code ... -->


<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    
    <title>Entry Delete</title>
  </head>
  
  <body>
    

    
     <div class="container" class="center" >
    <h2>Delete the Entry</h2>
    <br>

    <form action="" method="post" id="Delete">
  <div class="form-group">
    <label for="Name">Username</label>
    <input type="text" class="form-control" id="Name" name="Name" aria-describedby="EmailHelp" placeholder="Enter Email">
    
  </div>
  <div class="form-group">
    <label for="Email">Email</label>
    <input type="email" class="form-control" id="Email" name="Email" placeholder="Email">
  </div>
  
  
  <button type="submit" class="btn btn-primary">Signout</button>
</form>
  </div>
     
  

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>
</html>