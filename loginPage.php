<?php
require_once 'db.php'; // db.php 파일을 불러옴
session_start(); // 세션 시작
$stm = $pdo->query('SELECT CNO, PHONENO, PASSWD from CUSTOMER'); // CUSTOMER 테이블에서 CNO, PHONENO, PASSWD를 가져옴
$rows = $stm->fetchAll();
$isLogin = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") { // POST 방식으로 요청이 들어왔을 때
    $userPhone = $_POST['phone']; // 전화번호
    $userPassword = $_POST['password']; // 비밀번호

    foreach($rows as $row) {
        if ($row['PHONENO'] == $userPhone && $row['PASSWD'] == $userPassword) { // 전화번호와 비밀번호가 일치하는 경우
            if (strpos($row['CNO'], 'c') === 0) {
                // CNO가 'c'로 시작하면 managerPage.php로 리다이렉션
                $_SESSION['CNO'] = $row['CNO'];
                header("Location: managerPage.php");
            } else {
                // 그 외의 경우 menuPage.php로 리다이렉션
                $_SESSION['CNO'] = $row['CNO'];
                header("Location: menuPage.php");
            }
            $isLogin = true; // 로그인이 성공적으로 이루어졌음을 표시
            exit;
        }
    }
    if (!$isLogin) { // 로그인이 실패한 경우
        $_SESSION['error'] = '전화번호 또는 비밀번호가 일치하지 않습니다. 다시 시도해주세요.';
        header('Location: loginPage.php'); // 로그인 페이지로 다시 리다이렉트
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="loginPage.css">
</head>
<body>
    <div class="container">
        <div class="COn">C - ON</div>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"> <!-- 현재 페이지로 POST 방식으로 데이터 전송 -->
            <div class="Group76">
                <input type="text" name="phone" class="Rectangle5" placeholder="전화번호" required>
            </div>
            <div class="Group77">
                <input type="password" name="password" class="Rectangle6" placeholder="비밀번호" required>
            </div>
            <div class="Group44">
                <button type="submit" class="LogIn">Log-in</button>
            </div>
        </form>
        <?php
        if (isset($_SESSION['error'])) { // 에러 메시지가 있는 경우
            echo '<script>alert("' . $_SESSION['error'] . '");</script>';
            unset($_SESSION['error']); // 에러 메시지 출력 후 세션에서 제거
        }
        ?>
    </div>
</body>
</html>
