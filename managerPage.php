<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="managerPage.css" />
  </head>
  <body>
    <div class="screen">
      <div class="div">
        <div class="overlap-group">
          <div class="overlap"><div class="text-wrapper">관리자페이지</div></div>
          <p class="VIP">
            <?php
            require_once 'db.php';
            session_start();

            try {
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // 카테고리별 판매 횟수와 금액을 구하는 쿼리
                $query = ' 
                    SELECT c.CATEGORYNAME, COUNT(f.FOODNAME) AS FOOD_COUNT, SUM(f.PRICE) AS TOTAL_PRICE
                    FROM CATEGORY c
                    JOIN Contain co ON c.CATEGORYNAME = co.CATEGORYNAME
                    JOIN FOOD f ON co.FOODNAME = f.FOODNAME
                    GROUP BY c.CATEGORYNAME
                ';
                $stm = $pdo->prepare($query);
                $stm->execute();
                $categories = $stm->fetchAll(PDO::FETCH_ASSOC);

                if ($categories) { // 카테고리별 판매 정보가 있는 경우
                    echo '<span class="span">카테고리별 판매 횟수와 금액<br /><br /></span>';
                    foreach ($categories as $category) {
                        // 카테고리별 판매 횟수와 금액 출력
                        echo '<span class="text-wrapper-2"> - ' . htmlspecialchars($category['CATEGORYNAME']) . ': ' . 
                             htmlspecialchars($category['FOOD_COUNT']) . '회, ' . 
                             number_format($category['TOTAL_PRICE']) . '원<br /><br /></span>';
                    }
                } else { // 카테고리별 판매 정보가 없는 경우
                    echo '<span class="text-wrapper-2">카테고리별 판매 정보가 없습니다.<br /></span>';
                }
                // 고객별 총 지출 금액 및 순위를 구하는 쿼리
                $query2 = '
                    SELECT c.NAME, SUM(od.TOTALPRICE) AS TOTAL_PRICE,
                           RANK() OVER (ORDER BY SUM(od.TOTALPRICE) DESC) as RANK_PRICE
                    FROM CUSTOMER c
                    JOIN CART ca ON c.CNO = ca.CNO
                    JOIN ORDERDETAIL od ON ca.ID = od.ID
                    GROUP BY c.NAME
                ';
                $stm2 = $pdo->prepare($query2);
                $stm2->execute();
                $customers = $stm2->fetchAll(PDO::FETCH_ASSOC);

                if ($customers) {
                    // 고객별 총 지출 금액 및 순위 출력
                    echo '<span class="span">고객별 총 지출 금액 및 순위<br /><br /></span>';
                    foreach ($customers as $customer) {
                        // 고객별 총 지출 금액 및 순위 출력
                        echo '<span class="text-wrapper-2"> - ' . htmlspecialchars($customer['NAME']) . ': ' . 
                             number_format($customer['TOTAL_PRICE']) . '<br /><br /></span>';
                    }
                } else { // 고객별 지출 정보가 없는 경우
                    echo '<span class="text-wrapper-2">고객별 지출 정보가 없습니다.<br /></span>';
                }

            } catch (PDOException $e) { // 데이터베이스 오류가 발생한 경우
                echo "Error: " . $e->getMessage();
            }
            ?>
          </p>
        </div>
        <img class="c-ON" src="img/c-ON.svg" />
      </div>
    </div>
  </body>
</html>
