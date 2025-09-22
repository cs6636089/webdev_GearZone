<?php include "connect.php" ?>
<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <table border="1">
            <thead>
                <tr>
                    <th>รหัสคำสั่งซื้อ</th>
                    <th>ชื่อลูกค้า</th>
                    <th>จำนวนเงินทั้งหมด</th>
                    <th>สถานะการชำระเงิน</th>
                    <th>สถานะคำสั่งซื้อ</th>
                    <th>สถานะการส่งสินค้า</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stmt = $pdo->prepare("SELECT Orders.order_id, Users.username, Orders.total_amount, Orders.payment_status, Orders.order_status, Shipping_tracking.current_status AS shipping_status FROM Orders JOIN Users ON Orders.user_id = Users.user_id LEFT JOIN Shipping_tracking ON Orders.order_id = Shipping_tracking.order_id ORDER BY Orders.order_id ASC;");
                $stmt->execute();
                while($row = $stmt->fetch()) {
                    echo "<tr>";
                    echo "<td>" . $row["order_id"] . "</td>";
                    echo "<td>" . $row["username"] . "</td>";
                    echo "<td>" . $row["total_amount"] . "</td>";
                    echo "<td>" . $row["payment_status"] . "</td>";
                    echo "<td>" . $row["order_status"] . "</td>";
                    echo "<td>" . $row["shipping_status"] . "</td>";
                    echo "</tr>\n";
                }
            ?>
            </tbody>
        </table>
    </body>
</html>