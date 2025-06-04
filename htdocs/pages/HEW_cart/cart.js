// ログイン状態のチェック
function isLoggedIn() {
    return true; // 仮のfalse、Trueにしたらログイン判定になるからテストのときは変えてね
}

// 購入ボタンをクリックしたときの処理
document.getElementById('checkout-button').addEventListener('click', function () {
    if (!isLoggedIn()) {
        document.getElementById('login-modal').style.display = 'block';
    } else {
        window.location.href = '/pages/HEW_shipping-method/shipping-method.php';
    }
});

// モーダルを閉じるよ
document.querySelector('.close-modal').addEventListener('click', function () {
    document.getElementById('login-modal').style.display = 'none';
});

// 画面外でも閉じる
window.addEventListener('click', function (event) {
    const modal = document.getElementById('login-modal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});

// 削除
const deleteButtons = document.querySelectorAll('.item-delete');
deleteButtons.forEach(function (button) {
    button.addEventListener('click', function (event) {
        const cartItem = event.target.closest('.cart-items'); // 修正
        cartItem.remove();
        updateTotalPrice();
    });
});

// 数量変更
const quantitySelectors = document.querySelectorAll('.item-quantity-number'); // 修正
quantitySelectors.forEach(function (selector) {
    selector.addEventListener('change', function () {
        updateTotalPrice();
    });
});

// 合計金額を計算してリアタイ更新
function updateTotalPrice() {
    let totalPrice = 0;
    const cartItems = document.querySelectorAll('.cart-items');

    cartItems.forEach(function (item) {
        const priceElement = item.querySelector('.item-price');
        const quantityElement = item.querySelector('.item-quantity-number'); // 修正
        const price = parseInt(priceElement.textContent.replace('¥', '').replace(',', ''), 10);
        const quantity = parseInt(quantityElement.value, 10);
        totalPrice += price * quantity;
    });

    document.querySelector('.total-price').textContent = `¥${totalPrice}`;
}

// 合計金額表示
window.onload = updateTotalPrice;
