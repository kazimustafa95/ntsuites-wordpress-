<?php

/*template name: search*/



get_header();

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Office RND</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<style>
    body{
         color: #1a202c;
         text-align: left;
         background-color: #e2e8f0;    
         display:block;
         }
         .main-body {
         padding: 15px;
         }
         .card {
         box-shadow: 0 1px 3px 0 rgba(0,0,0,.1), 0 1px 2px 0 rgba(0,0,0,.06);
         }
    .form_box{
        position: relative;
    }
   
    .card {
        height: 500px; /* Adjust the height of the card */
    }
    .card-img-top {
        object-fit: cover;
        height: 300px;
        width: 100%; /* Set the width to 100% to maintain the aspect ratio */
    }
</style>
<div class="container">
    <div class="row mt-3 shadow-sm bg-light">
        <div class="col-md-12 p-4 form_box">
            <form action="" method="get">
                <div class="row">
                    <dvi class="col-md-5">
                        <select id="" class="form-control p-2" name="office" required="">
                            <option value="">Select Location</option>
                            <option value="all">All Locations</option>
                            <option value="5d1bcda0dbd6e40010479eec">NTsuites NRH</option>
                            <option value="63fe3d3f8534e20007fb7f50">NTsuites Southlake</option>
                        </select>
                    </dvi>
                    <dvi class="col-md-5">
                        <select id="" class="form-control p-2" name="cat" required="">
                            <option value="">Select Category</option>
                            <option value="all">All Catagories</option>
                            <option value="Office/Counselling">Office/Counselling</option>
                            <option value="Cosmetology/Massage">Cosmetology/Massage</option>
                            <option value="Health/Medical">Health/Medical</option>
                        </select>
                    </dvi>
                    <dvi class="col-md-2">
                        <button type="submit" class="btn btn-secondary form-control p-2"><span class="fa fa-search search_icon"></span> Search</button>
                        
                    </dvi>
                </div>
            </form>
        </div>
    </div>

    <div class="row mt-5">

        <?php
        // Create database connection
        $conn = new mysqli("localhost", "office_rnd", "office_rnd", "office_rnd");
        // Check connection
        if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
        }
    //echo "Connected successfully";
        $cat = $_GET['cat'];
        $off = $_GET['office'];
        if($off == "all" && $cat != "all" ){
            $sql = "SELECT * FROM `members_info` WHERE (`p_email` != '' OR `p_phone` != '') AND (`p_description` != '' OR `c_description` != '') AND `p_name` != 'SANDEEP' AND `status` = 'active' AND `category` = '$cat'";
            //echo "<script>alert('office all working');</script>";
        }else if($cat == "all" && $off != "all"){
            $sql = "SELECT * FROM `members_info` WHERE (`p_email` != '' OR `p_phone` != '') AND (`p_description` != '' OR `c_description` != '') AND `p_name` != 'SANDEEP' AND `status` = 'active' AND `p_office` = '$off'";
            //echo "<script>alert('cat all working');</script>";
        }else if($off == "all" && $cat == "all"){
            $sql = "SELECT * FROM `members_info` WHERE (`p_email` != '' OR `p_phone` != '') AND (`p_description` != '' OR `c_description` != '') AND `p_name` != 'SANDEEP' AND `status`='active'";
          //  echo "<script>alert('both all working');</script>";
        }else{
            $sql = "SELECT * FROM `members_info` WHERE (`p_email` != '' OR `p_phone` != '') AND (`p_description` != '' OR `c_description` != '') AND `p_name` != 'SANDEEP' AND `status`='active' AND `category` = '$cat' AND `p_office` = '$off'";
        }
        
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $p_email = $row['p_email'];
                $p_description = $row['p_description'];
                $p_number = $row['p_phone'];
                $p_img = $row['p_image'];
                if(empty($p_img)){
                    $p_img = 'https://ndic.gov.ng/wp-content/uploads/2020/04/placeholder.png';
                }else{
                    
                }
                
                    ?>
                        <div class="col-md-3 mt-4">
                            <div class="card">
                                <img src="<?php echo $p_img; ?>" style="height: 280px !important;" alt="...">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo $row['c_name'];?></h5>
                                    <p class="card-text"><?php echo substr($p_description, 0, 50);?>...</p>
                                    <a href="/view-profile?team=<?php echo $row['p_team'];?>" class="btn btn-dark mt-auto">View Profile &nbsp;<i class="fa fa-external-link" aria-hidden="true"></i></a>
                                </div>
                            </div>
                        </div>
                    <?php
            }
        }else{
            ?>
                <div class="alert alert-danger" role="alert">
                    No Record Found.
                </div>
            <?php
        }      
        ?>
    </div>
    <br><br><br>
</div>
<script src="https://kit.fontawesome.com/cb8da9e24a.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
