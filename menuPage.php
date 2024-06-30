<?php
require_once 'db.php';
session_start();
#error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
#ini_set('display_errors', 0);

// CONTAIN 테이블과 FOOD 테이블을 조인하여 FOODNAME, CATEGORYNAME, PRICE를 가져옴
$stm = $pdo->query('SELECT CONTAIN.FOODNAME, CONTAIN.CATEGORYNAME, FOOD.PRICE FROM CONTAIN JOIN FOOD ON CONTAIN.FOODNAME = FOOD.FOODNAME'); 
$menus = $stm->fetchAll();

$category = isset($_SESSION['category']) ? $_SESSION['category'] : '버거'; // 기본 카테고리는 '버거'

// POST 방식으로 요청이 들어왔을 때
if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $category = $_POST['category'];
    $_SESSION['category'] = $category;
}

$filteredMenus = $menus; // 필터링된 메뉴
#$_SESSION['CNO'] = 'c0';
#echo $_SESSION['CNO'];

if ($_SERVER["REQUEST_METHOD"] == "GET") { // GET 방식으로 요청이 들어왔을 때
    $price1 = isset($_GET['price1']) ? $_GET['price1'] : 0; // 가격 범위의 시작 값
    $price2 = isset($_GET['price2']) ? $_GET['price2'] : PHP_INT_MAX; // 가격 범위의 끝 값
    $searchName = isset($_GET['search_name']) ? $_GET['search_name'] : ''; // 검색할 메뉴명

    // 가격 범위와 메뉴명이 일치하는 경우 필터링
    $filteredMenus = array_filter($menus, function($menu) use ($price1, $price2, $searchName) {
        // 가격 범위 시작이 없는 경우
        if ($price1 == '') {
            $price1 = 0;
        }
        // 가격 범위의 끝 값이 없는 경우
        if ($price2 == '') {
            $price2 = PHP_INT_MAX;
        }
        $isWithinPriceRange = $menu['PRICE'] >= $price1 && $menu['PRICE'] <= $price2; // 가격 범위 내에 있는지 확인
        $isMatchingName = empty($searchName) || stripos($menu['FOODNAME'], $searchName) !== false; // 메뉴명이 일치하는지 확인
        
        return $isWithinPriceRange && $isMatchingName; // 가격 범위와 메뉴명이 일치하는 경우 필터링
    });
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="menuPage.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="screen">
    <div class="div">
        <div class="overlap">
            <div class="group">
                <div class="overlap-group">
                    <div class="rectangle"></div>
                      <script>
                        // 장바구니에 메뉴 추가
                        function addToCart(menuName, price) {
                            console.log(menuName, price);
                          $.ajax({
                                type: 'POST',
                                url: 'updateCart.php',
                                data: { menuName: menuName, price: price }, // 메뉴명과 가격 전송
                                success: function(response) {
                                    //alert(response);
                                    alert(menuName + '이(가) 장바구니에 추가되었습니다.');
                                }
                          });
                        }
                      </script>
                    <div class="group-2">
                        <!-- 카테고리별 메뉴 출력 -->
                        <?php foreach($filteredMenus as $menu): ?>
                            <?php if($menu['CATEGORYNAME'] == $category): ?> <!-- 카테고리가 일치하는 경우 -->
                                <div class="menu-item">
                                    <div class="ellipse"></div>
                                    <div class="text-wrapper">
                                        <!-- 메뉴명과 가격 출력 -->
                                        <?php echo htmlspecialchars($menu['FOODNAME']); ?><br><br>
                                        <?php echo number_format($menu['PRICE']); ?>원
                                    </div>
                                    <div class="div-wrapper">
                                        <!-- 장바구니에 메뉴 추가 버튼 -->
                                        <form method="post" action="updateCart.php" onsubmit="return addToCart(this)">
                                            <!-- 메뉴명과 가격 전송 -->
                                            <button type="button" class="text-wrapper-2" onclick="addToCart('<?php echo htmlspecialchars($menu['FOODNAME']); ?>', '<?php echo htmlspecialchars($menu['PRICE']); ?>')">담기</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
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
        <div class="text-wrapper-4">C - ON</div>
        <div class="overlap-2">
            <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"> <!-- 현재 페이지로 GET 방식으로 데이터 전송 -->
                <div class="overlap-wrapper">
                    <div class="search-container">
                        <!-- 메뉴명 검색 -->
                            <input type="search" name="search_name" placeholder="메뉴명" class="rectangle-2">
                            <button type="submit" class="search-button">
                                <img src="img/search.svg" alt="검색" />
                            </button>
                    </div>
                </div>
                <div class="group-11">
                    <div class="overlap-5">
                        <div class="group-12">
                            <!-- 가격 범위 검색 -->
                                <button type="submit" class="search-button2">
                                    <img src="img/search.svg" alt="검색2"/>
                                </button>
                                <div class="group-13">
                                    <input type="search" name="price1" placeholder="가격" class="calendar-month-wrapper">
                                    <input type="search" name="price2" placeholder="가격" class="rectangle-3">
                                    <div class="text-wrapper-9">~</div>
                                </div>
                        </div>
                        <div class="text-wrapper-10">₩</div>
                        <div class="text-wrapper-11">₩</div>
                    </div>
                </div>
            </form>
            <div class="group-10">
                <div class="overlap-4">
                    <script>
                        // 카테고리별 메뉴 출력
                        function openTab(event, tabName) {
                            $.ajax({
                                type: 'POST',
                                url: 'menuPage.php',
                                data: { category: tabName }, // 카테고리명 전송
                                success: function(response) {
                                    $('body').html(response);
                                }
                            });
                        }
                    </script>
                    <div class="tab">
                        <button class="tablinks" onclick="openTab(event, '버거')">버거</button>
                        <button class="tablinks" onclick="openTab(event, '사이드')">사이드</button>
                        <button class="tablinks" onclick="openTab(event, '음료')">음료</button>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
</body>
</html>
