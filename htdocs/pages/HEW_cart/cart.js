// ログイン状態のチェック (仮の関数)
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

// モーダルを閉じる処理
document.querySelector('.close-modal').addEventListener('click', function () {
    document.getElementById('login-modal').style.display = 'none';
});

// 画面外をクリックしたときにモーダルを閉じる処理
window.addEventListener('click', function (event) {
    const modal = document.getElementById('login-modal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});

// カートアイテムの削除処理
const deleteButtons = document.querySelectorAll('.item-delete');
deleteButtons.forEach(function (button) {
    button.addEventListener('click', function (event) {
        const cartItem = event.target.closest('.cart-items'); // 修正
        cartItem.remove();
        updateTotalPrice();
    });
});

// カートアイテムの数量変更処理
const quantitySelectors = document.querySelectorAll('.item-quantity-number'); // 修正
quantitySelectors.forEach(function (selector) {
    selector.addEventListener('change', function () {
        updateTotalPrice();
    });
});

// 合計金額を計算して更新する関数
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

// ページロード時に合計金額を計算して表示
window.onload = updateTotalPrice;
