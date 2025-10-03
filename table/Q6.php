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
                    <th>ยอดรวมคำสั่งซื้อ</th>
                    <th>ยอดรวมค่าใช้จ่าย</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $stmt = $pdo->prepare("SELECT Users.username, COUNT(Orders.order_id) AS total_orders, SUM(Orders.total_amount) AS total_spent
                            FROM Users JOIN Orders  ON Users.user_id = Orders.user_id GROUP BY Users.user_id
                            ORDER BY total_spent DESC LIMIT 5;");
                $stmt->execute();
                while($row = $stmt->fetch()) {
                    echo "<tr>";
                    echo "<td>" . $row["username"] . "</td>";
                    echo "<td>" . $row["total_orders"] . "</td>";
                    echo "<td>" . $row["total_spent"] . "</td>";
                    echo "</tr>\n";
                }
            ?>
            </tbody>
        </table>
    </body>
</html>