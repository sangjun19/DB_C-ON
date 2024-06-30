<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $price = $_POST['price']; // POST로 받은 가격
        $menuName = $_POST['menuName']; // POST로 받은 메뉴 이름
        $userId = $_SESSION['CNO']; // 세션에 저장된 사용자 ID
        $cartId = null;

        // CART에서 CNO랑 같으면서 ORDERDATETIME이 NULL인 CART가 있는지 확인
        $checkCartQuery = 'SELECT ID, ORDERDATETIME FROM CART WHERE CNO = :userId';
        $stm = $pdo->prepare($checkCartQuery);
        $stm->bindParam(':userId', $userId);
        $stm->execute();
        $rows = $stm->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) { // CART에서 CNO랑 같으면서 ORDERDATETIME이 NULL인 CART가 있으면 그 CART ID를 가져옴
            if ($row['ORDERDATETIME'] === null) {
                $cartId = $row['ID'];
                break;
            }
        }

        if ($cartId === null) { // CART에서 CNO랑 같으면서 ORDERDATETIME이 NULL인 CART가 없으면 새로운 CART 생성
            echo "Cart not found. Creating a new cart...";
            do {
                $newCartId = rand(1, 9999); // CART ID는 1부터 9999까지의 랜덤한 숫자
                $checkCartIdQuery = 'SELECT COUNT(*) FROM CART WHERE ID = :newCartId'; // CART ID가 이미 있는지 확인
                $stm = $pdo->prepare($checkCartIdQuery);
                $stm->bindParam(':newCartId', $newCartId);
                $stm->execute();
                $cartIdExists = $stm->fetchColumn();
            } while ($cartIdExists > 0); // CART ID가 이미 있으면 다시 랜덤한 숫자를 생성

            $insertCartQuery = 'INSERT INTO CART (ID, ORDERDATETIME, CNO) VALUES (:newCartId, NULL, :userId)'; // CART 생성
            $stm = $pdo->prepare($insertCartQuery);
            $stm->bindParam(':newCartId', $newCartId);
            $stm->bindParam(':userId', $userId);
            $stm->execute();

            $cartId = $newCartId;
        }

        $checkQuery = 'SELECT QUANTITY, TOTALPRICE FROM ORDERDETAIL WHERE ID = :cartId AND FOODNAME = :menuName'; // CART에 이미 같은 메뉴가 있는지 확인
        $stm = $pdo->prepare($checkQuery);
        $stm->bindParam(':cartId', $cartId); // CART ID
        $stm->bindParam(':menuName', $menuName); // 메뉴 이름
        $stm->execute();
        $orderDetail = $stm->fetch(PDO::FETCH_ASSOC);

        if ($orderDetail) {

            $newQuantity = $orderDetail['QUANTITY'] + 1; // 이미 있는 메뉴면 수량을 1 증가
            $newTotalPrice = $orderDetail['TOTALPRICE'] + $price; // 이미 있는 메뉴면 총 가격을 증가
            $updateQuery = 'UPDATE ORDERDETAIL SET QUANTITY = :newQuantity, TOTALPRICE = :newTotalPrice WHERE ID = :cartId AND FOODNAME = :menuName'; // 수량과 총 가격 업데이트
            $stm = $pdo->prepare($updateQuery);
            $stm->bindParam(':newQuantity', $newQuantity); // 새로운 수량
            $stm->bindParam(':newTotalPrice', $newTotalPrice); // 새로운 총 가격
            $stm->bindParam(':cartId', $cartId); // CART ID
            $stm->bindParam(':menuName', $menuName); // 메뉴 이름
            $stm->execute();
            echo "Order updated successfully.";
        } else {

            do {
                $itemNo = rand(1, 99999); // ITEMNO는 1부터 99999까지의 랜덤한 숫자
                $checkItemNoQuery = 'SELECT COUNT(*) FROM ORDERDETAIL WHERE ITEMNO = :itemNo'; // ITEMNO가 이미 있는지 확인
                $stm = $pdo->prepare($checkItemNoQuery); // ITEMNO가 이미 있으면 다시 랜덤한 숫자를 생성
                $stm->bindParam(':itemNo', $itemNo); // 새로운 ITEMNO
                $stm->execute();
                $itemNoExists = $stm->fetchColumn();
            } while ($itemNoExists > 0);


            $insertQuery = 'INSERT INTO ORDERDETAIL (ITEMNO, ID, QUANTITY, TOTALPRICE, FOODNAME) VALUES (:itemNo, :cartId, 1, :price, :menuName)'; // 새로운 메뉴 추가
            $stm = $pdo->prepare($insertQuery); // ITEMNO, CART ID, 수량, 총 가격, 메뉴 이름
            $stm->bindParam(':itemNo', $itemNo);
            $stm->bindParam(':cartId', $cartId);
            $stm->bindParam(':price', $price);
            $stm->bindParam(':menuName', $menuName);
            $stm->execute();
            echo "Order added successfully.";
        }
    } catch (PDOException $e) { // 에러 발생 시 에러 메시지 출력
        echo "Error: " . $e->getMessage();
    }
}
?>
