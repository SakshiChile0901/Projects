<?php
$showAlert=false;
if ($_SERVER["REQUEST_METHOD"]=="POST") {
    include "dbconnect.php";
    $Name=$_POST['Name'];
    $Email=$_POST['Email'];
    $table=$_POST['table'];
    $Contact_No=$_POST['Contact_No'];
    $Date=$_POST['Date'];
    $Time=$_POST['Time'];
    
    $exists=false;

    $sql="INSERT INTO `reservation` (`id`, `Name`, `Email`, `table`, `Date`, `Time`, `Contact_No`) VALUES (NULL, '$Name', '$Email', '$table', '$Date', '$Time','$Contact_No');";
    $result= mysqli_query($conn,$sql);
        
      //   if(!$result || mysqli_num_rows($result) == 0){
      //     $numExistrows = mysqli_num_rows($result);
      // }
        // print_r(mysqli_num_rows($result)); exit;
        // $numExistrows=mysqli_num_rows($result);
        
        if ($result){
            $showAlert = true;
        }
    }
    
    
?>
 



<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
      crossorigin="anonymous"
      
    />
    <title>Book Now</title>
  </head>
  <style>
    body {
      margin: 50px;
      text-align: center;
      border: 1px solid black;
      border-radius: 20px;
      padding: 20px;
      background-color: #ffe667;
    }
    .img-logo {
      width: 100px;
      height: 100px;
      margin-bottom: 10px;
    }
    textarea {
      background-color: #ffe667;
      border-radius: 20px;
      border: none;
    }
    input {
      padding: 10px;
      margin: 10px;
      border: none;
      border-radius: 20px;
      background-color: #ffe667;
    }
    .btn {
      padding: 10px 25px;
      margin: 10px;
      background-color: #fcba03;
    }
    .btn:hover {
      background-color: #ffeb78;
    }
    .form {
      background-color: #ffea82;
      border-radius: 20px;
    }
    #table {
      background-color: #ffe667;
      border-radius: 20px;
      border: none;
      padding: 5px;
    }
  </style>
     <?php 
  if ($showAlert) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
  <strong>  SUCCESS  </strong> Your Table is Book......
  <a href="Website.php"><button type="button" class="btn btn-primary float-right">Thank You</button></a>
</div>';} 


?>
  <body>
    <section>
      <div class="container">
        <img
          class="img-logo"
          src="images/foodfusion.png"
          class="img-responsive"
        />
        <h1>Book A Table</h1>
        <hr />
        <form class="form" class="form-control" action="" method="post" >
          Name:
          <input
            type="text"
            placeholder="Enter name"
            name="Name"
            pattern="[A-Za-z]{2-10}"
            required
          /><br />
          Email:
          <input
            type="email"
            placeholder="Enter email"
            name="Email"
            pattern="^[a-zA-Z0-9+_.-]+@[a-zA-Z0-9.-]+$"
            required
          /><br />
          Contact no:
          <input
            type="text"
            placeholder="123-456-7890"
            name="Contact_No"
            pattern="[7-9]{1}[0-9]{9}"
            required
          />
          <hr />
          <label for="table">Select A Table</label>

          <select name="table" id="table" required>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
            <option value="7">7</option>
            <option value="8">8</option>
            <option value="9">9</option>
            <option value="10">10</option></select>
			<br/>
			<br/>
          Reservation Date & Time:<br />
          <input type="date" name="Date" min="2024-01-01" required />
          <input type="time" name="Time" min="09:00" max="21:00 PM" required />
          <br /><br />
        
          <input class="btn" type="submit" value="Reserve" />
        </form>
      </div>
    </section>
  </body>
</html>