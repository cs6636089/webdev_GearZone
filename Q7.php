<?php include "connect.php" ?>
<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <table border="1">
            <thead>
                <tr>
                    <th>ชื่อสินค้า</th>
                    <th>ชื่อโปรโมชั่น</th>
                    <th>ส่วนลด</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stmt = $pdo->prepare("SELECT Products.product_name, Promotions.promotion_name, Promotions.discount_percentage 
                                        FROM Products JOIN Promotions ON Products.product_id = Promotions.promotion_id;");
                $stmt->execute();
                while($row = $stmt->fetch()) {
                    echo "<tr>";
                    echo "<td>" . $row["product_name"] . "</td>";
                    echo "<td>" . $row["promotion_name"] . "</td>";
                    echo "<td>" . $row["discount_percentage"] . "</td>";
                    echo "</tr>\n";
                }
            ?>
            </tbody>
        </table>
    </body>
</html>