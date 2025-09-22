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
                    <th>ราคา</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stmt = $pdo->prepare("SELECT product_name, price FROM Products     
                WHERE price > (SELECT AVG(price) FROM Products);");
                $stmt->execute();
                while($row = $stmt->fetch()) {
                    echo "<tr>";
                    echo "<td>" . $row["product_name"] . "</td>";
                    echo "<td>" . $row["price"] . "</td>";
                    echo "</tr>\n";
                }
            ?>
            </tbody>
        </table>
    </body>
</html>