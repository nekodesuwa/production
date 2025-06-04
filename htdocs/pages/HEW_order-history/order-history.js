document.addEventListener('DOMContentLoaded', () => {
    console.log('注文履歴ページが読み込まれました');

    const orderHistoryList = document.querySelector('.order-history-list');
    orders.forEach(order => {
        // 注文ブロック作成
        const orderBlock = document.createElement('div');
        orderBlock.classList.add('order-block');

        // 注文番号
        const orderNumber = document.createElement('h2');
        orderNumber.classList.add('order-number');
        orderNumber.textContent = `注文番号: ${order.orderNumber}`;
        orderBlock.appendChild(orderNumber);

        // 注文日付
        const orderDate = document.createElement('p');
        orderDate.classList.add('order-date');
        orderDate.textContent = order.orderDate;
        orderBlock.appendChild(orderDate);

        // 商品リスト
        let totalAmount = 0; // 合計金額を初期化
        order.items.forEach(item => {
            const orderItem = document.createElement('div');
            orderItem.classList.add('order-item');

            // 商品左側
            const orderItemLeft = document.createElement('div');
            orderItemLeft.classList.add('order-item-left');

            const productImage = document.createElement('img');
            productImage.classList.add('product-image');
            productImage.src = item.image;
            productImage.alt = item.name;

            const itemName = document.createElement('p');
            itemName.classList.add('item-name');
            itemName.textContent = item.name;

            orderItemLeft.appendChild(productImage);
            orderItemLeft.appendChild(itemName);

            // 商品右側
            const orderItemRight = document.createElement('div');
            orderItemRight.classList.add('order-item-right');

            const itemQuantity = document.createElement('p');
            itemQuantity.classList.add('item-quantity');
            itemQuantity.textContent = `個数: ${item.quantity}`;

            const itemPrice = document.createElement('p');
            itemPrice.classList.add('item-price');
            itemPrice.textContent = `¥${item.price}`;

            orderItemRight.appendChild(itemQuantity);
            orderItemRight.appendChild(itemPrice);

            // 商品全体
            orderItem.appendChild(orderItemLeft);
            orderItem.appendChild(orderItemRight);

            orderBlock.appendChild(orderItem);

            // 合計金額を計算
            totalAmount += item.price * item.quantity;
        });

        // 合計金額を表示
        const totalAmountElement = document.createElement('p');
        totalAmountElement.classList.add('order-total-amount');
        totalAmountElement.textContent = `合計金額: ¥${totalAmount}`;
        orderBlock.appendChild(totalAmountElement);

        // 注文ブロックをリストに追加
        orderHistoryList.appendChild(orderBlock);
    });
});
