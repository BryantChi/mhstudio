{{-- TinyMCE 富文字編輯器（本地版，不使用 CDN） --}}
<script src="/vendor/tinymce/tinymce.min.js"></script>
<script>
tinymce.init({
    selector: '#{{ $selector ?? "content" }}',
    language: 'zh_TW',
    language_url: '/vendor/tinymce/langs/zh_TW.js',
    height: 500,
    menubar: 'file edit view insert format tools table',
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount', 'codesample'
    ],
    toolbar: 'undo redo | blocks | bold italic underline strikethrough | ' +
             'forecolor backcolor | alignleft aligncenter alignright alignjustify | ' +
             'bullist numlist outdent indent | link image media codesample | ' +
             'table | removeformat code fullscreen | help',
    content_style: 'body { font-family: "Noto Sans TC", sans-serif; font-size: 16px; line-height: 1.8; }',
    branding: false,
    promotion: false,
    relative_urls: false,
    remove_script_host: true,
    convert_urls: true,
    image_advtab: true,
    image_caption: true,
    // 圖片上傳 — 透過媒體庫
    file_picker_types: 'image',
    file_picker_callback: function(callback, value, meta) {
        if (meta.filetype === 'image' && typeof openMediaPicker === 'function') {
            // 建立隱藏 input 讓 media picker 回填
            let tmpInput = document.getElementById('_tinymce_media_tmp');
            if (!tmpInput) {
                tmpInput = document.createElement('input');
                tmpInput.type = 'hidden';
                tmpInput.id = '_tinymce_media_tmp';
                document.body.appendChild(tmpInput);
            }
            // 監聽值變化
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(m) {
                    if (m.type === 'attributes' && m.attributeName === 'value') {
                        const url = tmpInput.value;
                        if (url) {
                            callback(url, { alt: '' });
                            tmpInput.value = '';
                        }
                        observer.disconnect();
                    }
                });
            });
            observer.observe(tmpInput, { attributes: true });

            // 用 input event fallback
            tmpInput.addEventListener('input', function handler() {
                const url = tmpInput.value;
                if (url) {
                    callback(url, { alt: '' });
                    tmpInput.value = '';
                }
                tmpInput.removeEventListener('input', handler);
            }, { once: true });

            openMediaPicker('_tinymce_media_tmp');
        }
    },
    setup: function(editor) {
        // 確保表單提交時同步內容
        editor.on('change', function() {
            editor.save();
        });
    }
});
</script>
