<?php include "connect.php" ?>
<html>
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <table border="1">
            <thead>
                <tr>
                    <th>ชื่อลูกค้า</th>
                    <th>ชื่อสินค้า</th>
                    <th>จำนวน</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stmt = $pdo->prepare("SELECT Users.username, Products.product_name, Cart.quantity
                                        FROM Cart JOIN Users ON Cart.user_id = Users.user_id JOIN Products ON Cart.product_id = Products.product_id
                                        ORDER BY Users.username;
");
                $stmt->execute();
                while($row = $stmt->fetch()) {
                    echo "<tr>";
                    echo "<td>" . $row["username"] . "</td>";
                    echo "<td>" . $row["product_name"] . "</td>";
                    echo "<td>" . $row["quantity"] . "</td>";
                    echo "</tr>\n";
                }
            ?>
            </tbody>
        </table>
    </body>
</html>