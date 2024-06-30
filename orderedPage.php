<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['CNO'])) { // 세션에 사용자 ID가 없는 경우
    echo "User ID not set in session.";
    exit();
}

$userId = $_SESSION['CNO']; // 사용자 ID

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // CART 테이블과 ORDERDETAIL 테이블을 조인하여 주문 정보를 가져옴
    $query = '
        SELECT TO_CHAR(c.ORDERDATETIME, \'YYYY-MM-DD HH24:MI:SS\') AS ORDERDATETIME, o.FOODNAME, o.QUANTITY, o.TOTALPRICE
        FROM CART c
        JOIN ORDERDETAIL o ON c.ID = o.ID
        WHERE c.CNO = :userId
        ORDER BY c.ORDERDATETIME DESC
    ';
    $stm = $pdo->prepare($query);
    $stm->bindParam(':userId', $userId);
    $stm->execute();
    $orders = $stm->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { // 데이터베이스 오류가 발생한 경우
    echo "Error: " . $e->getMessage();
}

// POST 방식으로 요청이 들어왔을 때
$fromDate = isset($_POST['fromDate']) ? $_POST['fromDate'] : '0000-01-01'; // 시작 날짜
$toDate = isset($_POST['toDate']) ? $_POST['toDate'] : '9999-12-31'; // 끝 날짜

// 주문 정보를 필터링
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fromDate = $_POST['fromDate']; // 시작 날짜
    $toDate = $_POST['toDate']; // 끝 날짜
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />    
    <link rel="stylesheet" href="orderedPage.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        function filterOrders() {
        var fromDate = document.getElementById("fromDate").value;
        var toDate = document.getElementById("toDate").value;

        $.ajax({
            type: 'POST',
            url: 'orderedPage.php',
            data: { fromDate: fromDate, toDate: toDate},
            success: function(response) {
                $('body').html(response);
            }
        });
    }
    </script>
</head>
<body>
<div class="screen">
    <div class="div">
        <div class="overlap">
            <div class="overlap-group">
                <div class="group">
                    <button class="search" style="background: none; border: none; padding: 0;" onclick="filterOrders()">
                        <img src="img/search.svg" alt="Search"/>
                    </button>
                    <div class="group-2">
                        <input type="date" class="calendar-month-wrapper" id="fromDate">
                        <input type="date" class="calendar-month-wrapper2" id="toDate">
                        <div class="text-wrapper">~</div>
                    </div>
                </div>
            </div>
            <div class="group-3">
                <?php if($orders): ?>
                    <!-- 주문 정보 출력 -->
                    <?php $storeDate = 0 ?>
                    <?php $totalPrice = 0 ?>
                    <?php foreach($orders as $order): ?>
                        <?php
                            // 날짜 범위를 벗어나면 continue하여 다음 반복으로 넘어감
                            if ($order['ORDERDATETIME'] < $fromDate || $order['ORDERDATETIME'] > $toDate) {
                                continue;
                            }
                        ?>
                        <div class="order-item">
                            <?php if ($storeDate != $order['ORDERDATETIME']): ?>
                                <!-- 주문 날짜 출력 -->
                                <div class="text-wrapper-2"><br><?php echo htmlspecialchars($order['ORDERDATETIME']); ?></div>
                                <?php $totalPrice = 0; ?>
                            <?php endif; ?>
                            <!-- 총 가격 계산 -->
                            <?php $totalPrice += $order['TOTALPRICE']; ?>
                            <div class="group-4">
                                <!-- 주문 정보 출력 -->
                                <div class="text-wrapper-3"><?php echo htmlspecialchars($order['FOODNAME']); ?></div>
                                <div class="text-wrapper-4"><?php echo htmlspecialchars($order['QUANTITY']); ?></div>
                                <div class="text-wrapper-5"><?php echo number_format($order['TOTALPRICE']); ?>원</div>
                            </div>
                            <div class="group-7">
                                <!-- 주문 총 가격 출력 -->
                                <div class="text-wrapper-3">Total</div>
                                <div class="text-wrapper-6"><?php echo number_format($totalPrice); ?>원</div>
                            </div>
                            <?php $storeDate = $order['ORDERDATETIME']; ?>
                        </div>
                    <?php endforeach;?>
                <?php endif;?>
            </div>
        </div>
        <div class="text-wrapper-7">C - ON</div>
        <div class="overlap-2">
            <div class="group-8">
                <button type="button" class="fastfood" onclick="location.href='menuPage.php';">
                    <img src="img/fastfood.svg" alt="Fast Food" /> 
                </button>
                <button type="button" class="storage" onclick="location.href='orderedPage.php';">
                    <img src="img/storage.svg" alt="Storage" />
                </button>
                <button type="button" class="shopping-cart" onclick="location.href='cartPage.php';">
                    <img src="img/shopping-cart.svg" alt="Shopping Cart" />
                </button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
