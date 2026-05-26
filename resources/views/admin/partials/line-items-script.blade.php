{{-- 共用：報價單／合約的項目列管理 + 金額計算。
     需頁面具備 #itemsBody、#addItem，選用 #discount #taxRate #subtotal #taxAmount #totalAmount。
     對外暴露 window.LineItems 供「快速建立」面板呼叫。 --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    var itemsBody = document.getElementById('itemsBody');
    if (!itemsBody) return;

    // 以現有列數推算下一個索引（create=1、edit=N 皆適用）
    var itemIndex = itemsBody.querySelectorAll('.item-row').length || 0;

    function rowHtml(index, description, qty, unit, price) {
        var sel = function (u) { return unit === u ? ' selected' : ''; };
        var desc = (description || '').replace(/"/g, '&quot;');
        return '<td class="align-middle text-center item-number"></td>' +
            '<td><input type="text" class="form-control form-control-sm" name="items[' + index + '][description]" value="' + desc + '" placeholder="項目名稱，例如：首頁設計" required></td>' +
            '<td><input type="number" class="form-control form-control-sm item-qty" name="items[' + index + '][quantity]" value="' + (qty || 1) + '" min="0.01" step="0.01" required></td>' +
            '<td><select class="form-select form-select-sm" name="items[' + index + '][unit]">' +
                '<option value="項"' + sel('項') + '>項</option>' +
                '<option value="小時"' + sel('小時') + '>小時</option>' +
                '<option value="天"' + sel('天') + '>天</option>' +
                '<option value="月"' + sel('月') + '>月</option>' +
                '<option value="年"' + sel('年') + '>年</option>' +
                '<option value="頁"' + sel('頁') + '>頁</option>' +
                '<option value="個"' + sel('個') + '>個</option>' +
            '</select></td>' +
            '<td><input type="number" class="form-control form-control-sm item-price" name="items[' + index + '][unit_price]" value="' + (price || 0) + '" min="0" step="0.01" required></td>' +
            '<td class="item-amount text-end align-middle">NT$ ' + ((qty || 1) * (price || 0)).toLocaleString() + '</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger remove-item" data-coreui-toggle="tooltip" title="移除此項目">✕</button></td>';
    }

    function addItemRow(description, qty, unit, price) {
        var row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML = rowHtml(itemIndex, description, qty, unit, price);
        itemsBody.appendChild(row);
        itemIndex++;
        bindCalculation();
        updateRemoveButtons();
        updateRowNumbers();
        return row;
    }

    function updateRowNumbers() {
        document.querySelectorAll('.item-row').forEach(function (row, index) {
            var cell = row.querySelector('.item-number');
            if (cell) cell.textContent = index + 1;
        });
    }

    function updateRemoveButtons() {
        var rows = itemsBody.querySelectorAll('.item-row');
        rows.forEach(function (row) {
            var btn = row.querySelector('.remove-item');
            if (btn) btn.disabled = rows.length <= 1;
        });
    }

    function bindCalculation() {
        document.querySelectorAll('.item-qty, .item-price').forEach(function (input) {
            input.removeEventListener('input', calculateTotal);
            input.addEventListener('input', calculateTotal);
        });
    }

    function calculateTotal() {
        var subtotal = 0;
        document.querySelectorAll('.item-row').forEach(function (row) {
            var qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            var price = parseFloat(row.querySelector('.item-price').value) || 0;
            var amount = qty * price;
            row.querySelector('.item-amount').textContent = 'NT$ ' + amount.toLocaleString();
            subtotal += amount;
        });
        var discountEl = document.getElementById('discount');
        var taxRateEl = document.getElementById('taxRate');
        var discount = discountEl ? (parseFloat(discountEl.value) || 0) : 0;
        var taxRate = taxRateEl ? (parseFloat(taxRateEl.value) || 0) : 0;
        var taxable = subtotal - discount;
        var tax = Math.round(taxable * (taxRate / 100));
        var total = taxable + tax;
        var set = function (id, val) {
            var el = document.getElementById(id);
            if (el) el.textContent = 'NT$ ' + val.toLocaleString();
        };
        set('subtotal', subtotal);
        set('taxAmount', tax);
        set('totalAmount', total);
    }

    // 「新增項目」按鈕
    var addBtn = document.getElementById('addItem');
    if (addBtn) {
        addBtn.addEventListener('click', function () {
            var row = addItemRow('', 1, '項', '');
            calculateTotal();
            var input = row.querySelector('input[name$="[description]"]');
            if (input) input.focus();
        });
    }

    // 移除項目（事件委派）
    itemsBody.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('tr').remove();
            updateRemoveButtons();
            updateRowNumbers();
            calculateTotal();
        }
    });

    // 初始化
    bindCalculation();
    updateRemoveButtons();
    var d = document.getElementById('discount'); if (d) d.addEventListener('input', calculateTotal);
    var t = document.getElementById('taxRate'); if (t) t.addEventListener('input', calculateTotal);
    calculateTotal();

    // 對外 API（供「快速建立」面板呼叫）
    window.LineItems = {
        addItemRow: addItemRow,
        calculateTotal: calculateTotal,
        reset: function () { itemsBody.innerHTML = ''; itemIndex = 0; },
        refresh: function () { updateRemoveButtons(); updateRowNumbers(); calculateTotal(); }
    };
});
</script>
