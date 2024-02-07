<?php

/*template name: View Profile*/



get_header();

?>

<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>Office RND</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
   </head>


        <?php
    // Create database connection
    $conn = new mysqli("localhost", "office_rnd", "office_rnd", "office_rnd");
    // Check connection
    if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    }
    //echo "Connected successfully";
        $p_team = $_GET['team'];
        $sql = "SELECT * FROM `members_info` WHERE `p_team`='$p_team'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $p_name = $row['p_name'];
                $p_email = $row['p_email'];
                $p_num = $row['p_phone'];
                $p_company = $row['c_name'];
                $p_description =  $row['p_description'];
                $c_description =  $row['c_description'];
                $p_cat = $row['category'];
                $p_office = $row['ex1'];
                
                $facebook = $row['s_fb'];
                $instagram = $row['s_int'];
                $twitter = $row['s_tw'];
                $website = $row['s_web'];
                $linkedin = $row['s_in'];
                if(empty($facebook)){
                    $facebook = "-";
                }else{
                    $facebook ='<a href="'.$facebook.'" target="_blank" class="fa fa-external-link text-dark"></a>';
                }
                if(empty($instagram)){
                    $instagram = "-";
                }else{
                    $instagram ='<a href="'.$instagram.'" target="_blank" class="fa fa-external-link text-dark"></a>';
                }
                if(empty($twitter)){
                    $twitter = "-";
                }else{
                    $twitter ='<a href="'.$twitter.'" target="_blank" class="fa fa-external-link text-dark"></a>';
                }
                if(empty($linkedin)){
                    $linkedin = "-";
                }else{
                    $linkedin ='<a href="'.$linkedin.'" target="_blank" class="fa fa-external-link text-dark"></a>';
                }
                if(empty($website)){
                    $website = "-";
                }else{
                  if (strpos($website, 'https:') == false ) {
                     $website ='<a href="'.$website.'" target="_blank" class="fa fa-external-link text-dark"></a>';
                  }else{
                    $website ='<a href="https://'.$website.'" target="_blank" class="fa fa-external-link text-dark"></a>';
                  }
                }
               
                $p_img = $row['p_image'];
                if(empty($p_img)){
                    $p_img = 'https://ndic.gov.ng/wp-content/uploads/2020/04/placeholder.png';
                }else{
                    
                }
                
                if($p_office == "5d1bcda0dbd6e40010479eec"){
                    $office_name = "NTsuites NRH";
                    $office_address = "8813 N Tarrant Pkwy, North Richland Hills, TX 76182, USA";
                }else if($p_office == "63fe3d3f8534e20007fb7f50"){
                    $office_name = "NTsuites Southlake";
                    $office_address = "771 E Southlake Blvd, Southlake, TX 76092, USA";
                }
                  
            }
        }
    ?>
   
   <body>
      <style>
         body{

         color: #1a202c;
         text-align: left;
         background-color: #e2e8f0;    
         }
         .main-body {
         padding: 15px;
         }
         .card {
         box-shadow: 0 1px 3px 0 rgba(0,0,0,.1), 0 1px 2px 0 rgba(0,0,0,.06);
         }
         .card {
         position: relative;
         display: flex;
         flex-direction: column;
         min-width: 0;
         word-wrap: break-word;
         background-color: #fff;
         background-clip: border-box;
         border: 0 solid rgba(0,0,0,.125);
         border-radius: .25rem;
         }
         .card-body {
         flex: 1 1 auto;
         min-height: 1px;
         padding: 1rem;
         }
         .gutters-sm {
         margin-right: -8px;
         margin-left: -8px;
         }
         .gutters-sm>.col, .gutters-sm>[class*=col-] {
         padding-right: 8px;
         padding-left: 8px;
         }
         .mb-3, .my-3 {
         margin-bottom: 1rem!important;
         }
         .bg-gray-300 {
         background-color: #e2e8f0;
         }
         .h-100 {
         height: 100%!important;
         }
         .shadow-none {
         box-shadow: none!important;
         }
      </style>
      <div class="container mt-5 mb-5">
         <div class="main-body">
          
            <div class="row gutters-sm">
               <div class="col-md-4 mb-3">
                  <div class="card">
                     <div class="card-body">
                        <div class="d-flex flex-column align-items-center text-center">
                           <img src="<?php echo $p_img; ?>" alt="Admin" class="" width="100%">
                        </div>
                     </div>
                  </div>
                  <div class="card mt-3">
                     <ul class="list-group list-group-flush">
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                           <h6 class="mb-0">
                              <i class="fa fa-globe"></i>
                              Website
                           </h6>
                           <span class="text-secondary">
                               <?php echo $website;?>
                           </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                           <h6 class="mb-0">
                              <i class="fa fa-linkedin"></i>
                              LinkedIn
                           </h6>
                           <span class="text-secondary">
                               <?php echo $linkedin;?>
                           </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                           <h6 class="mb-0">
                            <i class="fa fa-twitter"></i>
                              Twitter
                           </h6>
                           <span class="text-secondary">
                               <?php echo $twitter;?>
                           </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                           <h6 class="mb-0">
                            <i class="fa fa-instagram"></i>
                              Instagram
                           </h6>
                           <span class="text-secondary">
                               <?php echo $instagram;?>
                           </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                           <h6 class="mb-0">
                            <i class="fa fa-facebook"></i>
                              Facebook
                           </h6>
                           <span class="text-secondary">
                               <?php echo $facebook;?>
                           </span>
                        </li>
                     </ul>
                  </div>
               </div>
               <div class="col-md-8">
                  <div class="row gutters-sm">
                     <div class="col-sm-12 mb-3">
                        <div class="card h-100">
                           <div class="card-body">
                              <h4 class="d-flex align-items-center mb-3"><?php echo $p_company; ?></h4>
                              <p><?php echo $c_description; ?></p>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="card mb-3">
                     <div class="card-body">
                        <div class="row">
                           <div class="col-sm-3">
                              <h6 class="mb-0">Name</h6>
                           </div>
                           <div class="col-sm-9 text-secondary">
                            <?php echo $p_name; ?>
                           </div>
                        </div>
                        <hr>
                        <div class="row">
                           <div class="col-sm-3">
                              <h6 class="mb-0">Email</h6>
                           </div>
                           <div class="col-sm-9 text-secondary">
                           <?php echo $p_email; ?>
                           </div>
                        </div>
                        <hr>
                        <div class="row">
                           <div class="col-sm-3">
                              <h6 class="mb-0">Phone</h6>
                           </div>
                           <div class="col-sm-9 text-secondary">
                           <?php echo $p_num; ?>
                           </div>
                        </div>
                        <!--<hr>-->
                        <!--<div class="row">-->
                        <!--   <div class="col-sm-3">-->
                        <!--      <h6 class="mb-0">Address</h6>-->
                        <!--   </div>-->
                        <!--   <div class="col-sm-9 text-secondary">-->
                        <!--   <?php // echo $p_add; ?>-->
                        <!--   </div>-->
                        <!--</div>-->
                        <hr>
                        <div class="row">
                           <div class="col-sm-3">
                              <h6 class="mb-0">Category</h6>
                           </div>
                           <div class="col-sm-9 text-secondary">
                           <?php echo $p_cat; ?>
                           </div>
                        </div>
                        <hr>
                        <div class="row">
                           <div class="col-sm-3">
                              <h6 class="mb-0">Office</h6>
                           </div>
                           <div class="col-sm-9 text-secondary">
                           <?php echo $office_name; ?>
                           </div>
                        </div>
                        <hr>
                        <div class="row">
                           <div class="col-sm-3">
                              <h6 class="mb-0">Address</h6>
                           </div>
                           <div class="col-sm-9 text-secondary">
                           <?php echo $office_address; ?>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="row gutters-sm">
                     <div class="col-sm-12 mb-3">
                        <div class="card h-100">
                           <div class="card-body">
                              <h6 class="d-flex align-items-center mb-3">Description</h6>
                              <p><?php echo $p_description; ?></p>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">
                    <a href="/directory" class="btn btn-dark px-4"><i class="fa fa-arrow-left"></i> &nbsp; Go Back</a>
                </div>
            </div>
         </div>
      </div>
      <script src="https://kit.fontawesome.com/cb8da9e24a.js" crossorigin="anonymous"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
   </body>
</html>


<?php

get_footer();

?>
