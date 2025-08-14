<?php
include 'condb.php';
$id=$_GET['id'];
$sql="SELECT * FROM parcel_db WHERE id='$id' ";
$result=mysqli_query($conn,$sql);
$row=mysqli_fetch_array($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Member</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-sm-6 offset-sm-3">
                <div class="h2 text-center alert alert-success mb-4 mt-4" role="alert">
                    แก้ไขข้อมูลพัสดุ
                </div>
                <form method="POST" action="update_parcel.php">

                    <label>ID</label>
                    <input type="text" id="id" name="id_p" class="form-control" readonly value=<?=$row['id']?> > <br>
                    
                    <label>ประเภทพัสดุ</label>
                    <input type="text" id="type_p" name="type_p" class="form-control" value=<?=$row['type_p']?> > <br>
                
                    <label>ชื่อพัสดุ</label>
                    <input type="text" id="name" name="name" class="form-control" value=<?=$row['name']?> > <br>

                    <label>หน่วยนับ</label>
                    <input type="text" id="unit" name="unit" class="form-control" value=<?=$row['unit']?> > <br>

                    <label>ราคาต่อหน่วย</label>
                    <input type="text" id="price" name="price" class="form-control" value=<?=$row['price']?> > <br>

                    <label>จำนวน</label>
                    <input type="text" id="qty" name="qty" class="form-control" value=<?=$row['qty']?> > <br>

                    <label>Minimum Stock</label>
                    <input type="text" id="limit_value" name="limit_value" class="form-control" value=<?=$row['limit_value']?> > <br>

                    <input type="submit" value="Update" class="btn btn-success">
                    <a href="home.php" class="btn btn-danger">ยกเลิก</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
