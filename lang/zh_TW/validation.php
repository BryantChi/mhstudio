<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'accepted' => ':attribute 必須被接受。',
    'accepted_if' => '當 :other 為 :value 時，:attribute 必須被接受。',
    'active_url' => ':attribute 不是有效的網址。',
    'after' => ':attribute 必須是 :date 之後的日期。',
    'after_or_equal' => ':attribute 必須是 :date 或之後的日期。',
    'alpha' => ':attribute 只能包含字母。',
    'alpha_dash' => ':attribute 只能包含字母、數字、破折號和底線。',
    'alpha_num' => ':attribute 只能包含字母和數字。',
    'array' => ':attribute 必須是陣列。',
    'ascii' => ':attribute 只能包含單字節字母數字字符和符號。',
    'before' => ':attribute 必須是 :date 之前的日期。',
    'before_or_equal' => ':attribute 必須是 :date 或之前的日期。',
    'between' => [
        'array' => ':attribute 必須有 :min 到 :max 個項目。',
        'file' => ':attribute 必須介於 :min 到 :max KB 之間。',
        'numeric' => ':attribute 必須介於 :min 到 :max 之間。',
        'string' => ':attribute 必須介於 :min 到 :max 個字元之間。',
    ],
    'boolean' => ':attribute 必須為 true 或 false。',
    'can' => ':attribute 包含未授權的值。',
    'confirmed' => ':attribute 確認不匹配。',
    'contains' => ':attribute 缺少必要的值。',
    'current_password' => '密碼不正確。',
    'date' => ':attribute 不是有效的日期。',
    'date_equals' => ':attribute 必須等於 :date。',
    'date_format' => ':attribute 不符合格式 :format。',
    'decimal' => ':attribute 必須有 :decimal 位小數。',
    'declined' => ':attribute 必須被拒絕。',
    'declined_if' => '當 :other 為 :value 時，:attribute 必須被拒絕。',
    'different' => ':attribute 和 :other 必須不同。',
    'digits' => ':attribute 必須是 :digits 位數字。',
    'digits_between' => ':attribute 必須介於 :min 到 :max 位數字之間。',
    'dimensions' => ':attribute 圖片尺寸無效。',
    'distinct' => ':attribute 有重複的值。',
    'doesnt_end_with' => ':attribute 不能以以下值結尾：:values。',
    'doesnt_start_with' => ':attribute 不能以以下值開頭：:values。',
    'email' => ':attribute 必須是有效的電子郵件地址。',
    'ends_with' => ':attribute 必須以以下值結尾：:values。',
    'enum' => '所選的 :attribute 無效。',
    'exists' => '所選的 :attribute 無效。',
    'extensions' => ':attribute 必須具有以下副檔名之一：:values。',
    'file' => ':attribute 必須是檔案。',
    'filled' => ':attribute 必須有值。',
    'gt' => [
        'array' => ':attribute 必須多於 :value 個項目。',
        'file' => ':attribute 必須大於 :value KB。',
        'numeric' => ':attribute 必須大於 :value。',
        'string' => ':attribute 必須多於 :value 個字元。',
    ],
    'gte' => [
        'array' => ':attribute 必須有 :value 個或更多項目。',
        'file' => ':attribute 必須大於或等於 :value KB。',
        'numeric' => ':attribute 必須大於或等於 :value。',
        'string' => ':attribute 必須多於或等於 :value 個字元。',
    ],
    'hex_color' => ':attribute 必須是有效的十六進位顏色。',
    'image' => ':attribute 必須是圖片。',
    'in' => '所選的 :attribute 無效。',
    'in_array' => ':attribute 不存在於 :other 中。',
    'integer' => ':attribute 必須是整數。',
    'ip' => ':attribute 必須是有效的 IP 位址。',
    'ipv4' => ':attribute 必須是有效的 IPv4 位址。',
    'ipv6' => ':attribute 必須是有效的 IPv6 位址。',
    'json' => ':attribute 必須是有效的 JSON 字串。',
    'list' => ':attribute 必須是列表。',
    'lowercase' => ':attribute 必須為小寫。',
    'lt' => [
        'array' => ':attribute 必須少於 :value 個項目。',
        'file' => ':attribute 必須小於 :value KB。',
        'numeric' => ':attribute 必須小於 :value。',
        'string' => ':attribute 必須少於 :value 個字元。',
    ],
    'lte' => [
        'array' => ':attribute 不得超過 :value 個項目。',
        'file' => ':attribute 必須小於或等於 :value KB。',
        'numeric' => ':attribute 必須小於或等於 :value。',
        'string' => ':attribute 不得超過 :value 個字元。',
    ],
    'mac_address' => ':attribute 必須是有效的 MAC 位址。',
    'max' => [
        'array' => ':attribute 不得超過 :max 個項目。',
        'file' => ':attribute 不得大於 :max KB。',
        'numeric' => ':attribute 不得大於 :max。',
        'string' => ':attribute 不得超過 :max 個字元。',
    ],
    'max_digits' => ':attribute 不得超過 :max 位數字。',
    'mimes' => ':attribute 必須是以下類型的檔案：:values。',
    'mimetypes' => ':attribute 必須是以下類型的檔案：:values。',
    'min' => [
        'array' => ':attribute 必須至少有 :min 個項目。',
        'file' => ':attribute 必須至少為 :min KB。',
        'numeric' => ':attribute 必須至少為 :min。',
        'string' => ':attribute 必須至少有 :min 個字元。',
    ],
    'min_digits' => ':attribute 必須至少有 :min 位數字。',
    'missing' => ':attribute 必須缺失。',
    'missing_if' => '當 :other 為 :value 時，:attribute 必須缺失。',
    'missing_unless' => '除非 :other 為 :value，否則 :attribute 必須缺失。',
    'missing_with' => '當 :values 存在時，:attribute 必須缺失。',
    'missing_with_all' => '當 :values 都存在時，:attribute 必須缺失。',
    'multiple_of' => ':attribute 必須是 :value 的倍數。',
    'not_in' => '所選的 :attribute 無效。',
    'not_regex' => ':attribute 格式無效。',
    'numeric' => ':attribute 必須是數字。',
    'password' => [
        'letters' => ':attribute 必須包含至少一個字母。',
        'mixed' => ':attribute 必須包含至少一個大寫字母和一個小寫字母。',
        'numbers' => ':attribute 必須包含至少一個數字。',
        'symbols' => ':attribute 必須包含至少一個符號。',
        'uncompromised' => '給定的 :attribute 已出現在資料外洩中。請選擇不同的 :attribute。',
    ],
    'present' => ':attribute 必須存在。',
    'present_if' => '當 :other 為 :value 時，:attribute 必須存在。',
    'present_unless' => '除非 :other 為 :value，否則 :attribute 必須存在。',
    'present_with' => '當 :values 存在時，:attribute 必須存在。',
    'present_with_all' => '當 :values 都存在時，:attribute 必須存在。',
    'prohibited' => ':attribute 被禁止。',
    'prohibited_if' => '當 :other 為 :value 時，:attribute 被禁止。',
    'prohibited_unless' => '除非 :other 在 :values 中，否則 :attribute 被禁止。',
    'prohibits' => ':attribute 禁止 :other 存在。',
    'regex' => ':attribute 格式無效。',
    'required' => ':attribute 為必填欄位。',
    'required_array_keys' => ':attribute 必須包含以下鍵：:values。',
    'required_if' => '當 :other 為 :value 時，:attribute 為必填欄位。',
    'required_if_accepted' => '當 :other 被接受時，:attribute 為必填欄位。',
    'required_if_declined' => '當 :other 被拒絕時，:attribute 為必填欄位。',
    'required_unless' => '除非 :other 在 :values 中，否則 :attribute 為必填欄位。',
    'required_with' => '當 :values 存在時，:attribute 為必填欄位。',
    'required_with_all' => '當 :values 都存在時，:attribute 為必填欄位。',
    'required_without' => '當 :values 不存在時，:attribute 為必填欄位。',
    'required_without_all' => '當 :values 都不存在時，:attribute 為必填欄位。',
    'same' => ':attribute 和 :other 必須相同。',
    'size' => [
        'array' => ':attribute 必須包含 :size 個項目。',
        'file' => ':attribute 必須為 :size KB。',
        'numeric' => ':attribute 必須為 :size。',
        'string' => ':attribute 必須為 :size 個字元。',
    ],
    'starts_with' => ':attribute 必須以以下值開頭：:values。',
    'string' => ':attribute 必須是字串。',
    'timezone' => ':attribute 必須是有效的時區。',
    'unique' => ':attribute 已經被使用。',
    'uploaded' => ':attribute 上傳失敗。',
    'uppercase' => ':attribute 必須為大寫。',
    'url' => ':attribute 必須是有效的網址。',
    'ulid' => ':attribute 必須是有效的 ULID。',
    'uuid' => ':attribute 必須是有效的 UUID。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'name' => [
            'required' => '請輸入您的姓名。',
        ],
        'email' => [
            'required' => '請輸入電子郵件地址。',
            'email' => '請輸入有效的電子郵件地址。',
        ],
        'message' => [
            'required' => '請輸入訊息內容。',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'name' => '姓名',
        'email' => '電子郵件',
        'password' => '密碼',
        'message' => '訊息',
        'project_type' => '專案類型',
    ],

];
