<?php
require_once 'db.php';
session_start();
$userId = $_SESSION['CNO'];

try {
    // CART 테이블에서 CNO가 현재 사용자 ID이고 ORDERDATETIME이 NULL인 레코드를 가져옴
    $checkCartQuery = 'SELECT ID FROM CART WHERE CNO = :userId AND ORDERDATETIME IS NULL';
    $stm = $pdo->prepare($checkCartQuery);
    $stm->bindParam(':userId', $userId);
    $stm->execute();
    $cart = $stm->fetch(PDO::FETCH_ASSOC);

    if ($cart) { // CART 테이블에 레코드가 있는 경우
        $cartId = $cart['ID'];
    }

} catch (PDOException $e) { // 데이터베이스 오류가 발생한 경우
    echo "Error: " . $e->getMessage();
}
if ($cart) {
    // ORDERDETAIL 테이블에서 CART ID에 해당하는 주문 정보를 가져옴
  try {
    $stm = $pdo->prepare('SELECT QUANTITY, FOODNAME, TOTALPRICE FROM ORDERDETAIL WHERE ID = :cartId'); // ORDERDETAIL 테이블에서 CART ID에 해당하는 주문 정보를 가져옴
    $stm->execute(['cartId' => $cartId]); // CART ID에 해당하는 주문 정보를 가져옴
    $orders = $stm->fetchAll();
  } catch (PDOException $e) {
      $orders = null;
  }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") { // POST 방식으로 요청이 들어왔을 때
    // 주문 처리
    try {        
        $pdo->beginTransaction();
        $currentTimestamp = (new DateTime())->format('Y-m-d H:i:s'); // 현재 시간
        
        // CART 테이블의 ORDERDATETIME을 현재 시간으로 업데이트
        $updateCartQuery = 'UPDATE CART SET ORDERDATETIME = :currentTimestamp WHERE ID = :cartId';
        $stm = $pdo->prepare($updateCartQuery);
        $stm->bindParam(':currentTimestamp', $currentTimestamp);
        $stm->bindParam(':cartId', $cartId);
        $stm->execute();
        $pdo->commit(); // 주문 처리 성공 시 커밋

        // echo "Order processed successfully and cart details cleared.";
    } catch (PDOException $e) {
        $pdo->rollBack(); // 주문 처리 실패 시 롤백
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="cartPage.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="screen">
        <div class="div">
            <div class="overlap">
                <div class="group"></div>
                <div class="text-wrapper" onclick="handleClick()">장바구니</div> <!-- 장바구니 -->
                <div class="group-2">

                    <?php if($cart): ?>
                        <?php $totalPrice = 0; ?> <!-- 총 가격 초기화 -->
                        <?php foreach($orders as $order): ?>
                            <!-- 장바구니에 담긴 메뉴 정보 출력 -->
                            <?php $totalPrice += $order['TOTALPRICE']; ?> <!-- 총 가격 계산 -->
                            <div class="menu-item">
                                <div class="ellipse-3"></div>
                                <div class="text-wrapper-6"><?php echo htmlspecialchars($order['FOODNAME']); ?></div>
                                <div class="group-7">
                                    <div class="group-4">
                                        <div class="text-wrapper-4"><?php echo htmlspecialchars($order['QUANTITY']); ?> 개</div>
                                    </div>
                                    <div class="text-wrapper-5"><?php echo number_format($order['TOTALPRICE']); ?>원</div>
                                </div>
                            </div>            
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
                <div class="group-8"></div>

                <?php if($cart): ?>
                  <div class="text-wrapper-8"><?php echo number_format($totalPrice); ?>원</div>
                <?php endif; ?>
                
                <div class="overlap-group-wrapper">

                    <script>
                        // 주문하기 버튼 클릭 시 주문 처리
                        function overlapGroupClick(event) {
                            event.preventDefault(); // 기본 이벤트 방지
                            
                            $.ajax({
                                type: 'POST',
                                url: 'cartPage.php',
                                success: function(response) { // 주문 처리 성공 시
                                    alert('주문이 완료되었습니다.');
                                    //alert(response);
                                    location.reload(); // 페이지 새로고침
                                },
                                error: function(xhr, status, error) { // 주문 처리 실패 시
                                    console.error('AJAX Error:', status, error);
                                }
                            });
                        }
                    </script>

                    <div class="overlap-group" onclick="overlapGroupClick(event)"> <!-- 주문하기 버튼 -->
                        <div class="text-wrapper-9">주문하기</div>
                    </div>
                </div>
            </div>
            <img class="c-ON" src="img/C-ON.svg" />
            <div class="overlap-2">
                <div class="group-9">
                    <!-- 하단 메뉴 버튼 -->
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
