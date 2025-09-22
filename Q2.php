<?php include "connect.php" ?>
<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <table border="1">
            <thead>
                <tr>
                    <th>วันที่สั่งซื้อ</th>
                    <th>ยอดขายรวม</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stmt = $pdo->prepare("SELECT DATE(Orders.order_day) AS order_day, SUM(Orders.total_amount) AS total_sales FROM Orders
                        GROUP BY order_day ORDER BY order_day DESC;");
                $stmt->execute();
                while($row = $stmt->fetch()) {
                    echo "<tr>";
                    echo "<td>" . $row["order_day"] . "</td>";
                    echo "<td>" . $row["total_sales"] . "</td>";
                    echo "</tr>\n";
                }
            ?>
            </tbody>
        </table>
    </body>
</html>