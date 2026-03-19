import './bootstrap';
import * as coreui from '@coreui/coreui';

// 初始化 CoreUI 組件
document.addEventListener('DOMContentLoaded', function () {
    // 初始化 Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-coreui-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new coreui.Tooltip(tooltipTriggerEl);
    });

    // 初始化 Popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-coreui-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new coreui.Popover(popoverTriggerEl);
    });

    // 側邊欄切換
    const sidebar = document.querySelector('.sidebar');
    const wrapper = document.querySelector('.wrapper');
    const MOBILE_BP = 992;

    if (sidebar && wrapper) {
        // 監聽側邊欄 class 變化 → 同步 wrapper margin（僅桌面版）
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    if (window.innerWidth < MOBILE_BP) return;
                    const isNarrow = sidebar.classList.contains('sidebar-narrow') ||
                                   sidebar.classList.contains('sidebar-narrow-unfoldable');
                    wrapper.style.marginLeft = isNarrow ? '4rem' : '16rem';
                }
            });
        });
        observer.observe(sidebar, { attributes: true });
    }

    // 側邊欄切換（桌面：收合/展開；手機：CoreUI Sidebar show/hide）
    const toggleSidebar = function() {
        if (!sidebar) return;
        if (window.innerWidth < MOBILE_BP) {
            // 手機版：用 CoreUI Sidebar API 滑出/收回
            const instance = coreui.Sidebar.getOrCreateInstance(sidebar);
            instance.toggle();
        } else {
            // 桌面版：收合為窄條
            sidebar.classList.toggle('sidebar-narrow-unfoldable');
        }
    };

    // header 的 menu icon
    const headerToggle = document.querySelector('#header-sidebar-toggle');
    if (headerToggle) {
        headerToggle.addEventListener('click', toggleSidebar);
    }

    // sidebar 底部的 toggler 按鈕
    const sidebarToggler = document.querySelector('.sidebar-toggler');
    if (sidebarToggler) {
        sidebarToggler.addEventListener('click', toggleSidebar);
    }

    // 確認刪除對話框
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('確定要刪除嗎？此操作無法復原。')) {
                e.preventDefault();
            }
        });
    });

    // 自動隱藏 Alert
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new coreui.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // 檔案上傳預覽
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector(input.dataset.preview);
                    if (preview) {
                        preview.src = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // 表單提交確認
    const confirmForms = document.querySelectorAll('form[data-confirm]');
    confirmForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const message = form.dataset.confirm || '確定要提交嗎？';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // 字數統計
    const textareas = document.querySelectorAll('textarea[data-counter]');
    textareas.forEach(textarea => {
        const counter = document.querySelector(textarea.dataset.counter);
        if (counter) {
            const updateCounter = () => {
                const count = textarea.value.length;
                const max = textarea.getAttribute('maxlength');
                counter.textContent = max ? `${count}/${max}` : count;

                if (max && count > max * 0.9) {
                    counter.classList.add('text-warning');
                } else {
                    counter.classList.remove('text-warning');
                }
            };

            textarea.addEventListener('input', updateCounter);
            updateCounter();
        }
    });

    // 全選/取消全選
    const selectAllCheckbox = document.querySelector('input[data-select-all]');
    if (selectAllCheckbox) {
        const target = selectAllCheckbox.dataset.selectAll;
        const checkboxes = document.querySelectorAll(target);

        selectAllCheckbox.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
    }

    // 側邊欄：自動捲動到當前選取的頁面項目
    const activeNavLink = document.querySelector('.sidebar-nav .nav-link.active');
    if (activeNavLink) {
        // 等待 CoreUI Navigation 展開 nav-group 後再捲動
        setTimeout(() => {
            activeNavLink.scrollIntoView({ block: 'center', behavior: 'instant' });
        }, 150);
    }

    // 動態搜尋（延遲執行）
    const searchInputs = document.querySelectorAll('input[data-search]');
    searchInputs.forEach(input => {
        let timeout;
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                const form = input.closest('form');
                if (form) {
                    form.submit();
                }
            }, 500);
        });
    });
});

// 全域工具函數
window.showToast = function(message, type = 'success') {
    const toastHTML = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-coreui-dismiss="toast"></button>
            </div>
        </div>
    `;

    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(container);
    }

    container.insertAdjacentHTML('beforeend', toastHTML);
    const toast = new coreui.Toast(container.lastElementChild);
    toast.show();
};

// 載入指示器
window.showLoading = function() {
    const loader = document.createElement('div');
    loader.id = 'global-loader';
    loader.innerHTML = `
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
             style="background: rgba(0,0,0,0.5); z-index: 9999;">
            <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">載入中...</span>
            </div>
        </div>
    `;
    document.body.appendChild(loader);
};

window.hideLoading = function() {
    const loader = document.getElementById('global-loader');
    if (loader) {
        loader.remove();
    }
};

// 匯出 CoreUI
window.coreui = coreui;
