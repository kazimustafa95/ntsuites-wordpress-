<?php

/*template name: Home - Recent Work */



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
    
</style>
<div class="container">
    <?php
        
    // Create database connection
    $conn = new mysqli("localhost", "office_rnd", "office_rnd", "office_rnd");
    // Check connection
    if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    }
    //echo "Connected successfully";

        $records_per_page = 12;
        $current_page = isset($_GET['page']) ? $_GET['page'] : 1;

        // Query to get total number of records
        $count_query = "SELECT COUNT(*) as `total` FROM `members_info` WHERE `p_email`!='' AND `c_description`!='' AND `p_phone`!='' AND `status`='active'";
        $count_result = $conn->query($count_query);
        $count_row = $count_result->fetch_assoc();
        $total_records = $count_row['total'];

        // Calculate total pages
        $total_pages = ceil($total_records / $records_per_page);

        // Calculate the starting point for the slice
        $start = ($current_page - 1) * $records_per_page;
    ?>
    <div class="row mt-5 shadow-sm bg-light">
        <div class="col-md-12 p-4 form_box">
            <form action="/search" method="get">
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
        // $sql = "SELECT * FROM `members_info` WHERE `p_email`!='' AND `p_description`!='' AND `p_phone`!='' AND `status`='active' LIMIT $start, $records_per_page";
        $sql = "SELECT * FROM `members_info` WHERE (`p_email` != '' OR `p_phone` != '') AND (`p_description` != '' OR `c_description` != '') AND `p_name` != 'SANDEEP' AND `p_name` != 'Admin User' AND `status` = 'active'";


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
                        <div class="col-xl-3 col-lg-4 col-md-6 mt-4">
                            <div class="card">
                                 <img src="<?php echo $p_img; ?>" style="height: 280px !important;" alt="...">

                                <div class="card-body d-flex flex-column">
                                    <?php if($row['c_name'] == "HIKEY YA GORGEOUS LLC SALON/NNA NOTARY NSA N121"){$num=20;}else{$num=40;}?>
                                    <h5 class="card-title"><?php echo mb_substr($row['c_name'], 0, $num);?>...</h5>
                                    <p class="card-text"><?php echo mb_substr($p_description, 0, 40);?>...</p>
                                    <a href="/view-profile?team=<?php echo $row['p_team'];?>" class="btn btn-dark mt-auto">View Profile &nbsp;<i class="fa fa-external-link" aria-hidden="true"></i></a>
                                </div>
                            </div>
                        </div>
                    <?php
            }
        }
        ?>
    </div>
    <div class="row mt-4">
        <nav class="d-flex justify-content-center mt-4" aria-label="Page navigation example">
            <ul class="pagination p-2">
                <?php
                    // if($current_page == 1){
                    //     echo '<li class="page-item"><a style="background-color:#f9f9f9; cursor:no-drop;" class="page-link py-3 px-4 h4 text-dark">Previous</a></li>';
                    // }else{
                    //     echo '<li class="page-item"><a class="page-link py-3 px-4 h4 text-dark" href="/test-page?page='.($current_page-1).'">Previous</a></li>';
                    // }

                    // for ($i = 1; $i <= $total_pages; $i++) {
                    //     if ($i == $current_page) {
                    //         echo '<li class="page-item"><a class="page-link py-3 px-4 h4 text-lgiht active" href="/test-page?page='.$i.'">'.$i.'</a></li>';
                    //     } else {
                    //         echo '<li class="page-item"><a class="page-link py-3 px-4 h4 text-dark" href="/test-page?page='.$i.'">'.$i.'</a></li>';
                    //     }
                    // }

                    // if($current_page == $total_pages){
                    //     echo '<li class="page-item"><a style="background-color:#f9f9f9; cursor:no-drop;" class="page-link py-3 px-4 h4 text-dark">Next</a></li>';
                    // }else{
                    //     echo '<li class="page-item"><a class="page-link py-3 px-4 h4 text-dark" href="/test-page?page='.($current_page+1).'">Next</a></li>';
                    // }
                ?>
                
            
            </ul>
        </nav>
    </div>
    <br><br><br>
</div>
<script src="https://kit.fontawesome.com/cb8da9e24a.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php

get_footer();

?>