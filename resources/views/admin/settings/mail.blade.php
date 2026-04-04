@extends('layouts.admin')

@section('title', '郵件設定')

@php
    $breadcrumbs = [
        ['title' => '系統設定', 'url' => '#'],
        ['title' => '郵件設定', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="mb-0">郵件設定</h2>
        <p class="text-muted">配置系統郵件發送設定</p>
    </div>
    <div class="col-md-6 text-md-end">
        <button type="button" class="btn btn-outline-primary" onclick="testMail()">
            <svg class="icon me-2">
                <use xlink:href="/assets/icons/free.svg#cil-send"></use>
            </svg>
            發送測試郵件
        </button>
    </div>
</div>

<form method="POST" action="{{ route('admin.settings.mail.update') }}">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <strong>郵件驅動</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="mail_driver" class="form-label">郵件驅動 <span class="text-danger">*</span></label>
                        <select class="form-select @error('mail_driver') is-invalid @enderror"
                                id="mail_driver"
                                name="mail_driver"
                                onchange="toggleMailSettings(this.value)"
                                required>
                            <option value="smtp" {{ old('mail_driver', env('MAIL_MAILER', 'smtp')) == 'smtp' ? 'selected' : '' }}>SMTP</option>
                            <option value="sendmail" {{ old('mail_driver', env('MAIL_MAILER')) == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                            <option value="mailgun" {{ old('mail_driver', env('MAIL_MAILER')) == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                            <option value="ses" {{ old('mail_driver', env('MAIL_MAILER')) == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                            <option value="postmark" {{ old('mail_driver', env('MAIL_MAILER')) == 'postmark' ? 'selected' : '' }}>Postmark</option>
                            <option value="log" {{ old('mail_driver', env('MAIL_MAILER')) == 'log' ? 'selected' : '' }}>Log (測試)</option>
                        </select>
                        @error('mail_driver')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- SMTP 設定 -->
                    <div id="smtp-settings">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="mail_host" class="form-label">SMTP 主機</label>
                                <input type="text"
                                       class="form-control @error('mail_host') is-invalid @enderror"
                                       id="mail_host"
                                       name="mail_host"
                                       value="{{ old('mail_host', env('MAIL_HOST', '')) }}"
                                       placeholder="例如: smtp.gmail.com">
                                @error('mail_host')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="mail_port" class="form-label">SMTP 埠號</label>
                                <input type="number"
                                       class="form-control @error('mail_port') is-invalid @enderror"
                                       id="mail_port"
                                       name="mail_port"
                                       value="{{ old('mail_port', env('MAIL_PORT', 587)) }}"
                                       placeholder="587">
                                @error('mail_port')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="mail_username" class="form-label">SMTP 用戶名</label>
                            <input type="text"
                                   class="form-control @error('mail_username') is-invalid @enderror"
                                   id="mail_username"
                                   name="mail_username"
                                   value="{{ old('mail_username', env('MAIL_USERNAME', '')) }}">
                            @error('mail_username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mail_password" class="form-label">SMTP 密碼</label>
                            <input type="password"
                                   class="form-control @error('mail_password') is-invalid @enderror"
                                   id="mail_password"
                                   name="mail_password"
                                   value="{{ old('mail_password', env('MAIL_PASSWORD', '')) }}"
                                   placeholder="留空表示不修改">
                            @error('mail_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mail_encryption" class="form-label">加密方式</label>
                            <select class="form-select @error('mail_encryption') is-invalid @enderror"
                                    id="mail_encryption"
                                    name="mail_encryption">
                                <option value="tls" {{ old('mail_encryption', env('MAIL_ENCRYPTION', 'tls')) == 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ old('mail_encryption', env('MAIL_ENCRYPTION')) == 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="" {{ old('mail_encryption', env('MAIL_ENCRYPTION')) == '' ? 'selected' : '' }}>無</option>
                            </select>
                            @error('mail_encryption')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Mailgun 設定 -->
                    <div id="mailgun-settings" style="display: none;">
                        <div class="mb-3">
                            <label for="mailgun_domain" class="form-label">Mailgun Domain</label>
                            <input type="text"
                                   class="form-control"
                                   id="mailgun_domain"
                                   name="mailgun_domain"
                                   value="{{ old('mailgun_domain', env('MAILGUN_DOMAIN', '')) }}">
                        </div>

                        <div class="mb-3">
                            <label for="mailgun_secret" class="form-label">Mailgun Secret</label>
                            <input type="password"
                                   class="form-control"
                                   id="mailgun_secret"
                                   name="mailgun_secret"
                                   value="{{ old('mailgun_secret', env('MAILGUN_SECRET', '')) }}"
                                   placeholder="留空表示不修改">
                        </div>
                    </div>

                    <!-- SES 設定 -->
                    <div id="ses-settings" style="display: none;">
                        <div class="mb-3">
                            <label for="ses_key" class="form-label">AWS Access Key ID</label>
                            <input type="text"
                                   class="form-control"
                                   id="ses_key"
                                   name="ses_key"
                                   value="{{ old('ses_key', env('AWS_ACCESS_KEY_ID', '')) }}">
                        </div>

                        <div class="mb-3">
                            <label for="ses_secret" class="form-label">AWS Secret Access Key</label>
                            <input type="password"
                                   class="form-control"
                                   id="ses_secret"
                                   name="ses_secret"
                                   value="{{ old('ses_secret', env('AWS_SECRET_ACCESS_KEY', '')) }}"
                                   placeholder="留空表示不修改">
                        </div>

                        <div class="mb-3">
                            <label for="ses_region" class="form-label">AWS Region</label>
                            <select class="form-select" id="ses_region" name="ses_region">
                                <option value="us-east-1" {{ old('ses_region', env('AWS_DEFAULT_REGION', 'us-east-1')) == 'us-east-1' ? 'selected' : '' }}>美國東部 (維吉尼亞)</option>
                                <option value="us-west-2" {{ old('ses_region', env('AWS_DEFAULT_REGION')) == 'us-west-2' ? 'selected' : '' }}>美國西部 (俄勒岡)</option>
                                <option value="eu-west-1" {{ old('ses_region', env('AWS_DEFAULT_REGION')) == 'eu-west-1' ? 'selected' : '' }}>歐洲 (愛爾蘭)</option>
                                <option value="ap-southeast-1" {{ old('ses_region', env('AWS_DEFAULT_REGION')) == 'ap-southeast-1' ? 'selected' : '' }}>亞太 (新加坡)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>寄件人資訊</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="mail_from_name" class="form-label">寄件人名稱 <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('mail_from_name') is-invalid @enderror"
                               id="mail_from_name"
                               name="mail_from_name"
                               value="{{ old('mail_from_name', env('MAIL_FROM_NAME', config('app.name'))) }}"
                               required>
                        @error('mail_from_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="mail_from_address" class="form-label">寄件人 Email <span class="text-danger">*</span></label>
                        <input type="email"
                               class="form-control @error('mail_from_address') is-invalid @enderror"
                               id="mail_from_address"
                               name="mail_from_address"
                               value="{{ old('mail_from_address', env('MAIL_FROM_ADDRESS', '')) }}"
                               required>
                        @error('mail_from_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="mail_reply_to" class="form-label">回覆 Email</label>
                        <input type="email"
                               class="form-control @error('mail_reply_to') is-invalid @enderror"
                               id="mail_reply_to"
                               name="mail_reply_to"
                               value="{{ old('mail_reply_to', setting('mail_reply_to', '')) }}">
                        <div class="form-text">留空將使用寄件人 Email</div>
                        @error('mail_reply_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>郵件模板</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="mail_logo" class="form-label">郵件 Logo URL</label>
                        <input type="url"
                               class="form-control @error('mail_logo') is-invalid @enderror"
                               id="mail_logo"
                               name="mail_logo"
                               value="{{ old('mail_logo', setting('mail_logo', '')) }}">
                        <div class="form-text">用於郵件模板頂部，建議使用絕對網址</div>
                        @error('mail_logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="mail_footer" class="form-label">郵件頁尾文字</label>
                        <textarea class="form-control @error('mail_footer') is-invalid @enderror"
                                  id="mail_footer"
                                  name="mail_footer"
                                  rows="3">{{ old('mail_footer', setting('mail_footer', '')) }}</textarea>
                        @error('mail_footer')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <strong>郵件功能</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="mail_enabled"
                                   name="mail_enabled"
                                   value="1"
                                   {{ old('mail_enabled', setting('mail_enabled', true)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="mail_enabled">
                                <strong>啟用郵件發送</strong>
                            </label>
                        </div>
                        <small class="text-muted">關閉後將不會發送任何郵件</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="mail_queue"
                                   name="mail_queue"
                                   value="1"
                                   {{ old('mail_queue', setting('mail_queue', false)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="mail_queue">
                                使用佇列發送
                            </label>
                        </div>
                        <small class="text-muted">提升效能但需要設定 Queue Worker</small>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong">通知設定</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="notify_new_user"
                                   name="notify_new_user"
                                   value="1"
                                   {{ old('notify_new_user', setting('notify_new_user', true)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify_new_user">
                                新用戶註冊通知
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="notify_new_comment"
                                   name="notify_new_comment"
                                   value="1"
                                   {{ old('notify_new_comment', setting('notify_new_comment', true)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify_new_comment">
                                新評論通知
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="notify_contact_form"
                                   name="notify_contact_form"
                                   value="1"
                                   {{ old('notify_contact_form', setting('notify_contact_form', true)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="notify_contact_form">
                                聯絡表單提交通知
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <strong>常用 SMTP 設定</strong>
                </div>
                <div class="card-body">
                    <h6 class="small">Gmail</h6>
                    <ul class="small">
                        <li>主機: smtp.gmail.com</li>
                        <li>埠號: 587</li>
                        <li>加密: TLS</li>
                    </ul>

                    <h6 class="small mt-3">Outlook</h6>
                    <ul class="small">
                        <li>主機: smtp.office365.com</li>
                        <li>埠號: 587</li>
                        <li>加密: TLS</li>
                    </ul>

                    <h6 class="small mt-3">SendGrid</h6>
                    <ul class="small mb-0">
                        <li>主機: smtp.sendgrid.net</li>
                        <li>埠號: 587</li>
                        <li>加密: TLS</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <svg class="icon me-2">
                                <use xlink:href="/assets/icons/free.svg#cil-save"></use>
                            </svg>
                            儲存設定
                        </button>
                        <button type="reset" class="btn btn-light">重置</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    function toggleMailSettings(driver) {
        // 隱藏所有設定區塊
        document.getElementById('smtp-settings').style.display = 'none';
        document.getElementById('mailgun-settings').style.display = 'none';
        document.getElementById('ses-settings').style.display = 'none';

        // 根據選擇的驅動顯示對應設定
        if (driver === 'smtp') {
            document.getElementById('smtp-settings').style.display = 'block';
        } else if (driver === 'mailgun') {
            document.getElementById('mailgun-settings').style.display = 'block';
        } else if (driver === 'ses') {
            document.getElementById('ses-settings').style.display = 'block';
        }
    }

    function testMail() {
        const email = prompt('請輸入測試郵件地址：');
        if (email) {
            // 實作發送測試郵件邏輯
            alert('測試郵件發送功能待實作，將發送至：' + email);
        }
    }

    // 頁面載入時初始化
    document.addEventListener('DOMContentLoaded', function() {
        const driver = document.getElementById('mail_driver').value;
        toggleMailSettings(driver);
    });
</script>
@endpush
@endsection
