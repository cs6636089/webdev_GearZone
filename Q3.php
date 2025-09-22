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
                    <th>ยอดขายรวม</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stmt = $pdo->prepare("SELECT Products.product_name,  SUM(Order_items.quantity) AS total_sold
                                FROM Order_items JOIN Products ON Order_items.product_id = Products.product_id
                                GROUP BY Products.product_id ORDER BY total_sold DESC; ");
                $stmt->execute();
                while($row = $stmt->fetch()) {
                    echo "<tr>";
                    echo "<td>" . $row["product_name"] . "</td>";
                    echo "<td>" . $row["total_sold"] . "</td>";
                    echo "</tr>\n";
                }
            ?>
            </tbody>
        </table>
    </body>
</html>