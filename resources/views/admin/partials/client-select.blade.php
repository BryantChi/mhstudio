{{--
    客戶選擇元件（報價單／合約／發票的 create + edit 共用）

    用法：
        @include('admin.partials.client-select', [
            'clients'  => $clients,                                   // 必要
            'selected' => old('client_id', $selectedClientId ?? null), // 選用，預選 id
            'required' => true,                                        // 選用，預設 true
        ])

    行為：
    1. 可打字搜尋既有客戶
    2. 打不存在的名稱 → 下拉出現「＋ 用「xxx」開單（暫定客戶）」，點了立刻建檔
    3. 「填資料建立」→ 小視窗補聯絡資料後建檔

    關鍵設計：選到新客戶時「立即建檔並把真實 id 換回 select」，所以送出表單時
    client_id 永遠是合法 id，QuoteController / ContractController / InvoiceController
    的 exists:clients,id 驗證完全不用改。
--}}
@php
    $clientSelectRequired = $required ?? true;
    $clientSelectValue = $selected ?? null;
@endphp

<div class="mb-3">
    <label for="client_id" class="form-label d-flex justify-content-between align-items-center">
        <span>客戶 @if($clientSelectRequired)<span class="text-danger">*</span>@endif</span>
        <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" id="quickClientOpenBtn"
                data-coreui-toggle="modal" data-coreui-target="#quickClientModal">
            <svg class="icon icon-sm me-1"><use xlink:href="/assets/icons/free.svg#cil-user-plus"></use></svg>
            填資料建立
        </button>
    </label>

    <select class="form-select @error('client_id') is-invalid @enderror"
            id="client_id" name="client_id" data-client-select
            @if($clientSelectRequired) required @endif>
        <option value="">選擇客戶</option>
        @foreach($clients as $client)
            <option value="{{ $client->id }}" {{ $clientSelectValue == $client->id ? 'selected' : '' }}>
                {{ $client->name }}{{ $client->is_provisional ? '（暫定）' : '' }}
            </option>
        @endforeach
    </select>

    @error('client_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    <div class="invalid-feedback d-none" id="clientSelectError"></div>
</div>

{{-- 快速建立客戶 --}}
<div class="modal fade" id="quickClientModal" tabindex="-1" aria-labelledby="quickClientModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickClientModalLabel">
                    <svg class="icon me-2"><use xlink:href="/assets/icons/free.svg#cil-user-plus"></use></svg>
                    快速建立客戶
                </h5>
                <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="關閉"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="quickClientError"></div>

                <div class="mb-3">
                    <label for="qc_name" class="form-label">客戶名稱 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="qc_name" autocomplete="off">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="qc_contact_person" class="form-label">聯絡人</label>
                        <input type="text" class="form-control" id="qc_contact_person" autocomplete="off">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="qc_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="qc_email" autocomplete="off">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="qc_phone" class="form-label">電話</label>
                        <input type="text" class="form-control" id="qc_phone" autocomplete="off">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="qc_company" class="form-label">公司</label>
                        <input type="text" class="form-control" id="qc_company" autocomplete="off">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="qc_tax_id" class="form-label">統一編號</label>
                        <input type="text" class="form-control" id="qc_tax_id" autocomplete="off">
                    </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="qc_is_provisional" checked>
                    <label class="form-check-label" for="qc_is_provisional">
                        標記為暫定客戶
                        <small class="text-muted d-block">尚未確定合作。之後可在客戶管理轉為正式客戶。</small>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="quickClientSubmit">建立並選取</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<style>
    /* Select2 預設高度比 CoreUI 的 form-select 矮，對齊同列其他欄位 */
    .select2-container--bootstrap-5 .select2-selection { min-height: calc(1.5em + 0.75rem + 2px); }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function () {
    const QUICK_STORE_URL = '{{ route('admin.clients.quick-store') }}';
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    $(function () {
        const $select = $('#client_id');
        const $fieldError = $('#clientSelectError');
        let lastSearchTerm = '';
        let _quickModal = null;

        // 記住最後輸入的搜尋字：下拉一關閉 select2 就會清掉 search field，屆時取不到值
        $select.on('select2:open', function () {
            $('.select2-search__field').off('input.clientSelect').on('input.clientSelect', function () {
                lastSearchTerm = $.trim(this.value);
            });
        });

        // modal 實例取得方式沿用 admin/media/partials/picker-modal.blade.php 的相容寫法
        function getQuickModal() {
            if (_quickModal) return _quickModal;

            const el = document.getElementById('quickClientModal');
            if (!el) return null;

            if (window.coreui && window.coreui.Modal) {
                _quickModal = window.coreui.Modal.getOrCreateInstance(el);
            } else if (window.bootstrap && window.bootstrap.Modal) {
                _quickModal = window.bootstrap.Modal.getOrCreateInstance(el);
            }

            return _quickModal;
        }

        $select.select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '搜尋或輸入客戶名稱',
            allowClear: {{ $clientSelectRequired ? 'false' : 'true' }},
            tags: true,
            language: {
                noResults: () => '找不到客戶，直接打名字即可建立',
                searching: () => '搜尋中…',
            },
            // 打字找不到時，在下拉最後放一個「建立」項目；真正建檔在 select2:select 時才做
            createTag: function (params) {
                const term = $.trim(params.term);
                if (!term) return null;

                return { id: '__new__', text: '＋ 用「' + term + '」開單（暫定客戶）', isNew: true, term: term };
            },
        });

        $select.on('select2:select', function (e) {
            const data = e.params.data;
            if (!data.isNew) return;

            quickCreateClient({ name: data.term, is_provisional: true })
                .catch(showFieldError);
        });

        // 「填資料建立」：把已輸入的關鍵字帶進視窗
        $('#quickClientOpenBtn').on('click', function () {
            resetQuickForm();
            if (lastSearchTerm) $('#qc_name').val(lastSearchTerm);

            // 按鈕本身帶 data-coreui-toggle，這裡是取得實例時的補強（重複 show 無副作用）
            const modal = getQuickModal();
            if (modal) modal.show();
        });

        $('#quickClientSubmit').on('click', function () {
            const $btn = $(this);
            const name = $.trim($('#qc_name').val());

            if (!name) {
                showModalError('請輸入客戶名稱');
                return;
            }

            $btn.prop('disabled', true).text('建立中…');

            quickCreateClient({
                name: name,
                contact_person: $('#qc_contact_person').val(),
                email: $('#qc_email').val(),
                phone: $('#qc_phone').val(),
                company: $('#qc_company').val(),
                tax_id: $('#qc_tax_id').val(),
                is_provisional: $('#qc_is_provisional').is(':checked'),
            })
            .then(() => {
                const modal = getQuickModal();
                if (modal) {
                    modal.hide();
                } else {
                    document.querySelector('#quickClientModal [data-coreui-dismiss="modal"]').click();
                }
                resetQuickForm();
            })
            .catch(showModalError)
            .finally(() => $btn.prop('disabled', false).text('建立並選取'));
        });

        /**
         * 建立客戶並把真實 id 寫回 select。
         * 必須在送出表單前完成，否則 client_id 會是使用者打的字串而非合法 id。
         */
        function quickCreateClient(payload) {
            return fetch(QUICK_STORE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                },
                body: JSON.stringify(payload),
            })
            .then(res => res.json().then(body => res.ok ? body : Promise.reject(body)))
            .then(client => {
                clearErrors();
                $select.find('option[value="__new__"]').remove();

                const label = client.name + (client.is_provisional ? '（暫定）' : '');
                $select.append(new Option(label, client.id, true, true)).trigger('change');

                return client;
            })
            .catch(body => {
                // 建立失敗就把暫時選項清掉，避免使用者誤以為已經建立
                $select.find('option[value="__new__"]').remove();
                $select.val(null).trigger('change');

                const message = body && body.errors
                    ? Object.values(body.errors).flat().join('、')
                    : '建立客戶失敗，請稍後再試';

                return Promise.reject(message);
            });
        }

        function resetQuickForm() {
            ['#qc_name', '#qc_contact_person', '#qc_email', '#qc_phone', '#qc_company', '#qc_tax_id']
                .forEach(sel => $(sel).val(''));
            $('#qc_is_provisional').prop('checked', true);
            $('#quickClientError').addClass('d-none').text('');
        }

        function showModalError(message) {
            $('#quickClientError').removeClass('d-none').text(message);
        }

        function showFieldError(message) {
            $fieldError.removeClass('d-none').text(message);
        }

        function clearErrors() {
            $fieldError.addClass('d-none').text('');
            $('#quickClientError').addClass('d-none').text('');
        }
    });
})();
</script>
@endpush
