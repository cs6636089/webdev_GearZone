<?php include "connect.php" ?>
<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <table border="1">
            <thead>
                <tr>
                    <th>รหัสการรายงาน</th>
                    <th>ชื่อลูกค้า</th>
                    <th>ชื่อสินค้า</th>
                    <th>ประเภทของรายงาน</th>
                    <th>คำอธิบาย</th>
                    <th>สถานะคำสั่งซื้อ</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stmt = $pdo->prepare("SELECT Reports.report_id, Users.username, Products.product_name, Reports.report_type, Reports.description,
                        Orders.order_status FROM Reports JOIN Users ON Reports.user_id = Users.user_id JOIN Products ON Reports.product_id = Products.product_id
                        JOIN Orders ON Reports.order_id = Orders.order_id ORDER BY Reports.report_id ASC;");
                $stmt->execute();
                while($row = $stmt->fetch()) {
                    echo "<tr>";
                    echo "<td>" . $row["report_id"] . "</td>";
                    echo "<td>" . $row["username"] . "</td>";
                    echo "<td>" . $row["product_name"] . "</td>";
                    echo "<td>" . $row["report_type"] . "</td>";
                    echo "<td>" . $row["description"] . "</td>";
                    echo "<td>" . $row["order_status"] . "</td>";
                    echo "</tr>\n";
                }
            ?>
            </tbody>
        </table>
    </body>
</html>