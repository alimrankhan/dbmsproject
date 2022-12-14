<?php $page="update"; 
$title="Add New Data"; //page title

require 'includes/connect_db.php';//requires php file that contain code to connect DB
 $conn = getDB();




$error=[];
//-------------------------------Validate input form-------------------------------//

$second_dose="";
if($_SERVER['REQUEST_METHOD']=="POST" and !empty($_POST))
{

          $sql="SELECT * FROM student WHERE id={$_POST['id']}";
          $dat = get_element($conn,$sql);

          if($dat[0]['vaccinated']=='Yes')
          {
              $sql="SELECT * 
                  FROM  student,vaccination_info,side_effect where
                  student.id={$_POST['id']} and vaccination_info.ID=student.ID
                    and side_effect.ID = vaccination_info.ID";
              $dat = get_element($conn,$sql);
          }
           
          foreach($dat[0] as $k=>$v)
              if(!empty($_POST[$k]))
                  $dat[0][$k]=$_POST[$k];
                
            $data=$dat[0];
            
              if($_POST['vaccinated'] == "Yes")
              {

                  $second_dose="";
                  if(isset($_POST['second_dose']) )
                      $second_dose=$_POST['second_dose'];
                  
                  //check if vaccinated but didn't provide vaccination info 
                  foreach($entity as $key => $value) // fetch key from $entity and check if it is found in post
                      if($tables[$key]!='student' and
                        (!isset($_POST[$key]) or $_POST[$key]==""))
                        {
                            if($key=="other_effect" or $key=="second_dose") //other_effect field can be empty
                              continue;
                            else
                              $error[]="Field <strong>{$value}</strong> can't be empty as you're vaccinated. Try again!";
                        }
              }
   }
// --------------------------------End of Validation-----------------------//



  // ------------If there is no error then update to databse-------------------//

  if(!empty($_POST) and empty($error))
      {
          $count=0;
          // $conn=getDB(); 
            //get connection to database from getBD() in connect_db.php file

          $sql="UPDATE student set name=?,department=?,session=?,vaccinated=? WHERE (id=? and email=?);";

          $stmt=prepare_sql($conn,$sql); // call function to prepare sql
          if($stmt) //if $stmt is not false then procced
          {
                  mysqli_stmt_bind_param($stmt,"ssssis",$data['name'],$data['department'],$data['session'],$data['vaccinated'],$data['id'],$data['email']); //update student table
                  $count+=is_success($stmt,$conn);  

          }
          if($_POST['vaccinated']=="No")//if not vaccinated and inserted successfully then redirect
                {
                    $sql="DELETE FROM side_effect WHERE id=?";
                    $stmt=prepare_sql($conn,$sql);
                    if($stmt)
                        {
                            mysqli_stmt_bind_param($stmt,"i",$data['id']);
                            $count+=is_success($stmt,$conn);
                        }
                          
                      $sql="DELETE FROM vaccination_info WHERE id=?";
                      $stmt=prepare_sql($conn,$sql);
                      if($stmt)
                          { 
                            mysqli_stmt_bind_param($stmt,"i",$data['id']);
                            $count+=is_success($stmt,$conn);
                          }
                      if($count==3) //if inserted into 3 tables successfully then redirect
                      {
                          redirect("view.php?entty=id&value={$_POST['id']}");
                          exit;
                      }
                          
                  }
              else if($_POST['vaccinated']=="Yes")
              {
                    $sql = "INSERT INTO vaccination_info VALUES(?,?,?,?,?,?)";
                    $stmt = prepare_sql( $conn , $sql );
                    
                    if($stmt)
                    {
                        mysqli_stmt_bind_param($stmt,"isssss",$_POST['id'],$_POST['vaccination_id'],$_POST['vaccine_name'],$_POST['vaccination_date'],$_POST['first_dose'],$second_dose);
                        $count+=is_success($stmt,$conn);
                    }

                    $sql = "INSERT INTO side_effect VALUES( ?,?,?,?,?,? )";
                    $stmt = prepare_sql($conn , $sql);

                    if( $stmt )
                    {

                        mysqli_stmt_bind_param($stmt,"isssss",$_POST['id'],$_POST['vaccination_id'],$_POST['fever'],$_POST['headache'],$_POST['vomitting'],$_POST['other_effect']);

                        $count+=is_success($stmt,$conn);
                    }
                
                    if($count==3) //if inserted into 3 tables successfully then redirect
                    {
                        redirect("view.php?entty=id&value={$_POST['id']}");
                        exit;
                    }
              }
            
      
                  
  }
    
?>



<?php include 'includes/header&sidebar.php';
 ?>
 


<div class="content">

<?php 



//------------------FIND ID IN DATABASE------------------//
if($_SERVER['REQUEST_METHOD']=="GET" and !empty($_GET) and isset($_GET['id']))
{
        $sql="SELECT * FROM student WHERE id={$_GET['id']}";
        $arr=get_element($conn,$sql);
        if(empty($arr))
            echo "ID is not found!<br>";
     
}
?>
   
<?php if(!empty($arr)):?>  <!---------- if found then print it----------->
   <div class="single">
     <table>
     <?php foreach($arr as $data):?>
      <?php foreach($data as $key=>$value):?>
        <?php if($key=='email')continue;?>
         <tr>
             <th><?=$entity[$key]?>:</th>
             <td><?=$value?></td>
      </tr>
        <?php endforeach; ?>
      <?php endforeach;?>
      
      </table>
      <p> We have found your information.You can update everything except your ID and Vaccination ID.</p>
   </div>
   <button onclick="openForm('id02');">Update</button>
      <?php include 'includes/update_form.php';?> 
 <?php endif;?>
 <!-------------END OF FINDING ID IN DB----------->

      
  <!-- //--------------- FORM TO COLLECT ID TO SEARCH IT IN DATABASE--------------// -->
        <div class="find_std">
            <form action="" method="get">
                <!-- <label for="id"></label> -->
                <input type="number" id='id' name='id' placeholder="Enter Your ID to Update Data" min=10000000 max=999999999 required>
                <button type="submit" onclick="document.getElementById('find_std').style.display='none';">Find</button>
            </form>
        </div>

       
    <!---------If there is error found in validation print errors ---->
    <?php if(!empty($error)):?>
                <?php foreach($error as $vul):?> 
                        <li><strong>Error:</strong> <?=$vul;?></li>
                <?php endforeach;?>
            <?php endif;?>

<!---------If there is error found in validation print errors ---->


 </div> <!-- end of content div -->


<?php include 'includes/footer.php';?>
