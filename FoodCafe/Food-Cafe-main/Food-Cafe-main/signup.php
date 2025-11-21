<?php
$login=false;
$showError=false;
if($_SERVER["REQUEST_METHOD"]=="POST") {
    include "dbconnect.php";
    $username=$_POST['username'];
    $password=$_POST['password'];

    $sql="SELECT * FROM `customer_login` WHERE username='$username' AND password='$password'";
    $result= mysqli_query($conn,$sql);
    $num=mysqli_num_rows($result);

    if ($num == 1) {
      $login =true;
      session_start();
      $_SESSION['loggedin']=true;
      $_SESSION['username']=$username;
      //header("location:welcome.php");
    }
    else {
      $showError= "Invalid Credentials";
    }
  }


    
    
?>

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>signup</title>

  
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
                
            </ul>
            
        </div>
    </nav>
  

  <?php 
  if ($login) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
  <strong>Success!</strong> You are logged in.
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
  <a href="reservation.php"><button type="button" class="btn btn-primary float-right">BOOKING</button></a>

</div>';} 
if ($showError) {
  echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <strong>Error!</strong> Either username OR password incorrect.
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>';

  
}
?>
  <div class="container">
    <h1 class="text-center ">Sign Up</h1>

   <form action="" method="post">
  <div class="form-group">
    <label for="username">User Name</label>
    <input type="username" class="form-control" id="username" pattern="[A-Za-z]{2-10}" name="username"aria-describedby="emailHelp" placeholder="Enter Your Name">
    
  </div>
  <div class="form-group">
    <label for="exampleInputPassword1">Password</label>
    <input type="password" class="form-control" id="password" name="password" placeholder=" Enter The Password" required>
  </div>
    
  
  <button type="submit" class="btn btn-primary">Login</button>
</form>
</div>

 

 
 
    

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>
</html>