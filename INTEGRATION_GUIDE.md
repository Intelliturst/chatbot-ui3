# 虹宇智能客服系統 - 整合指南

> **版本**: v1.0
> **最後更新**: 2025-10-28
> **整合方式**: 方案1（獨立模組）

## 📋 目錄

- [整合概覽](#整合概覽)
- [前置需求](#前置需求)
- [快速開始](#快速開始)
- [詳細步驟](#詳細步驟)
- [配置說明](#配置說明)
- [測試驗證](#測試驗證)
- [常見問題](#常見問題)
- [附錄](#附錄)

---

## 整合概覽

### 🎯 整合目標

將虹宇智能客服系統整合到您的 Laravel 專案中，以**獨立模組**的形式存在於 `app/Services/Chatbot/` 目錄下。

### 📊 整合規模

| 類別 | 數量 | 說明 |
|------|------|------|
| PHP 檔案 | 12 | Services + Agents + Livewire |
| Blade 模板 | 2 | 聊天視窗 + Demo 頁面 |
| JSON 資料 | 11 | 課程、FAQ、補助資料 |
| 靜態資源 | 3 | Logo、Agent 頭像 |
| 配置文件 | 1 | .env 配置 |
| **總計** | **29** | **核心檔案** |

### ⏱️ 預計時間

- **文件遷移**: 15-20 分鐘
- **依賴安裝**: 5 分鐘
- **配置調整**: 10 分鐘
- **測試驗證**: 10-15 分鐘
- **總計**: **40-50 分鐘**

---

## 前置需求

### ✅ 必備條件

1. **Laravel 版本**: >= 9.0（推薦 9.52+）
2. **PHP 版本**: >= 8.0（推薦 8.2）
3. **Composer**: 已安裝
4. **OpenAI API Key**: 需要申請（或稍後配置）

### 📦 依賴套件

```json
{
    "livewire/livewire": "^2.10",
    "guzzlehttp/guzzle": "^7.2"
}
```

### 🔍 檢查清單

```bash
# 1. 檢查 Laravel 版本
php artisan --version
# 應該顯示: Laravel Framework 9.x.x

# 2. 檢查 PHP 版本
php -v
# 應該顯示: PHP 8.x.x

# 3. 檢查 Livewire（如果已安裝）
composer show livewire/livewire
# 如果未安裝，會顯示錯誤（正常）
```

---

## 快速開始

### 🚀 3 步驟快速整合

```bash
# 1. 複製智能客服檔案到目標專案
cp -r chatbot-ui3/app/Services/Chatbot target-project/app/Services/
cp -r chatbot-ui3/resources/data/chatbot target-project/resources/data/
cp -r chatbot-ui3/app/Http/Livewire/ChatbotWidget.php target-project/app/Http/Livewire/
cp -r chatbot-ui3/resources/views/livewire/chatbot-widget.blade.php target-project/resources/views/livewire/
cp chatbot-ui3/public/*.png target-project/public/

# 2. 安裝 Livewire（如果未安裝）
cd target-project
composer require livewire/livewire:^2.10

# 3. 配置環境變數
echo "OPENAI_API_KEY=your-key-here" >> .env
echo "OPENAI_AGENT_MODEL=gpt-3.5-turbo" >> .env

# 完成！現在可以測試
php artisan serve
# 訪問 http://localhost:8000（右下角應出現聊天按鈕）
```

---

## 詳細步驟

### Step 1: 安裝 Livewire（如未安裝）

```bash
cd /path/to/target-project

# 安裝 Livewire 2.x
composer require livewire/livewire:^2.10

# 發布 Livewire 配置（可選）
php artisan livewire:publish --config
```

**驗證安裝**:
```bash
composer show livewire/livewire
# 應該顯示: versions : * v2.10.x
```

---

### Step 2: 複製核心檔案

#### A. 複製 Services（智能客服核心）

```bash
# 建立目標目錄
mkdir -p target-project/app/Services/Chatbot
mkdir -p target-project/app/Services/Chatbot/Agents

# 複製所有 Agent 類別
cp chatbot-ui3/app/Services/Agents/BaseAgent.php \
   target-project/app/Services/Chatbot/Agents/

cp chatbot-ui3/app/Services/Agents/ClassificationAgent.php \
   target-project/app/Services/Chatbot/Agents/

cp chatbot-ui3/app/Services/Agents/CourseAgent.php \
   target-project/app/Services/Chatbot/Agents/

cp chatbot-ui3/app/Services/Agents/SubsidyAgent.php \
   target-project/app/Services/Chatbot/Agents/

cp chatbot-ui3/app/Services/Agents/FAQAgent.php \
   target-project/app/Services/Chatbot/Agents/

cp chatbot-ui3/app/Services/Agents/EnrollmentAgent.php \
   target-project/app/Services/Chatbot/Agents/

cp chatbot-ui3/app/Services/Agents/HumanServiceAgent.php \
   target-project/app/Services/Chatbot/Agents/

# 複製核心 Services
cp chatbot-ui3/app/Services/OpenAIService.php \
   target-project/app/Services/Chatbot/

cp chatbot-ui3/app/Services/SessionManager.php \
   target-project/app/Services/Chatbot/

cp chatbot-ui3/app/Services/RAGService.php \
   target-project/app/Services/Chatbot/
```

#### B. 複製 Livewire 組件

```bash
# 複製 Livewire 組件（PHP）
cp chatbot-ui3/app/Http/Livewire/ChatbotWidget.php \
   target-project/app/Http/Livewire/

# 複製 Livewire 視圖（Blade）
mkdir -p target-project/resources/views/livewire
cp chatbot-ui3/resources/views/livewire/chatbot-widget.blade.php \
   target-project/resources/views/livewire/
```

#### C. 複製資料檔案

```bash
# 建立資料目錄結構
mkdir -p target-project/resources/data/chatbot/{courses,faq,subsidy,greetings,contacts,quick_options}

# 複製所有 JSON 資料檔案
cp -r chatbot-ui3/resources/data/chatbot/* \
      target-project/resources/data/chatbot/
```

#### D. 複製靜態資源

```bash
# 複製 Logo 和頭像圖片
cp chatbot-ui3/public/logo.png target-project/public/
cp chatbot-ui3/public/agent.png target-project/public/

# 複製 LINE 圖示（如果有）
mkdir -p target-project/public/images
cp chatbot-ui3/public/images/line@.png target-project/public/images/
```

---

### Step 3: 調整命名空間

#### A. 批量修改 Agent 類別命名空間

```bash
cd target-project/app/Services/Chatbot/Agents

# macOS/Linux
find . -name "*.php" -exec sed -i '' 's/namespace App\\Services\\Agents;/namespace App\\Services\\Chatbot\\Agents;/g' {} +

# Linux
find . -name "*.php" -exec sed -i 's/namespace App\\Services\\Agents;/namespace App\\Services\\Chatbot\\Agents;/g' {} +
```

#### B. 修改核心 Services 命名空間

```bash
cd target-project/app/Services/Chatbot

# macOS/Linux
sed -i '' 's/namespace App\\Services;/namespace App\\Services\\Chatbot;/g' OpenAIService.php
sed -i '' 's/namespace App\\Services;/namespace App\\Services\\Chatbot;/g' SessionManager.php
sed -i '' 's/namespace App\\Services;/namespace App\\Services\\Chatbot;/g' RAGService.php

# Linux
sed -i 's/namespace App\\Services;/namespace App\\Services\\Chatbot;/g' OpenAIService.php
sed -i 's/namespace App\\Services;/namespace App\\Services\\Chatbot;/g' SessionManager.php
sed -i 's/namespace App\\Services;/namespace App\\Services\\Chatbot;/g' RAGService.php
```

#### C. 修改 Agent 中的 use 語句

```bash
cd target-project/app/Services/Chatbot/Agents

# 所有 Agent 都引用 BaseAgent，需要更新
# macOS/Linux
find . -name "*.php" -exec sed -i '' 's/use App\\Services\\Agents\\BaseAgent;/use App\\Services\\Chatbot\\Agents\\BaseAgent;/g' {} +

# Linux
find . -name "*.php" -exec sed -i 's/use App\\Services\\Agents\\BaseAgent;/use App\\Services\\Chatbot\\Agents\\BaseAgent;/g' {} +
```

#### D. 修改 Livewire 組件的引用

編輯 `target-project/app/Http/Livewire/ChatbotWidget.php`:

```php
// 原始
use App\Services\SessionManager;
use App\Services\Agents\ClassificationAgent;

// 修改為
use App\Services\Chatbot\SessionManager;
use App\Services\Chatbot\Agents\ClassificationAgent;
```

#### E. 修改 RAGService 中的路徑

編輯 `target-project/app/Services/Chatbot/RAGService.php`:

```php
// 確認 loadJSON 方法中的路徑正確
protected function loadJSON($filename)
{
    $cacheKey = 'chatbot_json_' . str_replace('/', '_', $filename);

    return Cache::remember($cacheKey, $this->cacheDuration, function() use ($filename) {
        // 路徑應該是: resources/data/chatbot/{filename}
        $path = resource_path("data/chatbot/{$filename}");

        // ... 其餘代碼不變
    });
}
```

---

### Step 4: 配置環境變數

編輯 `target-project/.env`:

```env
# OpenAI API 配置（智能客服）
OPENAI_API_KEY=your-openai-api-key-here
OPENAI_AGENT_MODEL=gpt-3.5-turbo

# 可選：Session 配置
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

**取得 OpenAI API Key**:
1. 訪問 https://platform.openai.com/api-keys
2. 登入並建立新的 API Key
3. 複製 Key 並貼上到 `.env` 檔案

**暫時不使用 OpenAI**:
如果暫時沒有 API Key，系統會使用模糊匹配和模式檢測作為備用方案，仍可正常運作（但智能度較低）。

---

### Step 5: 整合到 Layout

#### 方式 A: 全站顯示（推薦）

編輯您的主 Layout 檔案（通常是 `resources/views/layouts/app.blade.php`）:

```blade
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '您的網站')</title>

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body>
    <!-- 您的原有內容 -->
    @yield('content')

    <!-- 智能客服聊天視窗（浮動在右下角） -->
    @livewire('chatbot-widget')

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}"></script>

    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html>
```

#### 方式 B: 特定頁面顯示

在需要顯示智能客服的頁面中加入:

```blade
@extends('layouts.app')

@section('content')
    <!-- 您的頁面內容 -->
@endsection

<!-- 在該頁面顯示智能客服 -->
@section('scripts')
    @parent
    @livewire('chatbot-widget')
@endsection
```

---

### Step 6: 清除緩存

```bash
cd target-project

# 清除所有 Laravel 緩存
php artisan optimize:clear

# 或者逐一清除
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 重新產生 Composer autoload
composer dump-autoload
```

---

## 配置說明

### 📋 環境變數

| 變數名稱 | 說明 | 預設值 | 必填 |
|---------|------|--------|------|
| `OPENAI_API_KEY` | OpenAI API 金鑰 | - | ❌ |
| `OPENAI_AGENT_MODEL` | 使用的 GPT 模型 | `gpt-3.5-turbo` | ❌ |
| `SESSION_DRIVER` | Session 驅動 | `file` | ✅ |
| `SESSION_LIFETIME` | Session 有效期（分鐘） | `120` | ✅ |

### 🎨 自訂樣式

智能客服使用 Tailwind CSS，可透過以下方式自訂:

#### 修改顏色主題

編輯 `resources/views/livewire/chatbot-widget.blade.php`:

```blade
{{-- 浮動按鈕顏色 --}}
<button class="... bg-gradient-to-br from-primary to-primary-dark ...">

{{-- Header 顏色 --}}
<div class="... bg-gradient-to-r from-primary to-primary-dark ...">
```

在您的 `tailwind.config.js` 中定義 primary 顏色:

```javascript
module.exports = {
    theme: {
        extend: {
            colors: {
                primary: '#6366f1',          // 主色調
                'primary-dark': '#4f46e5',   // 主色調深色
            }
        }
    }
}
```

#### 修改 Logo

替換 `public/logo.png` 和 `public/agent.png`:

```bash
# 替換 Logo（建議尺寸: 200x50 px，透明背景）
cp your-logo.png target-project/public/logo.png

# 替換 Agent 頭像（建議尺寸: 100x100 px，透明或圓形）
cp your-agent.png target-project/public/agent.png
```

### 📝 自訂資料

所有資料檔案位於 `resources/data/chatbot/`:

#### 修改課程資料

編輯 `resources/data/chatbot/courses/course_list.json`:

```json
{
  "courses": [
    {
      "course_id": "YOUR-001",
      "course_name": "您的課程名稱",
      "type": "unemployed",
      "full_name": "完整課程名稱",
      "content": "課程內容說明...",
      "keywords": ["關鍵字1", "關鍵字2"],
      "priority": 1,
      "featured": 1
    }
  ]
}
```

#### 修改 FAQ

編輯 `resources/data/chatbot/faq/general_faq.json`:

```json
{
  "faqs": [
    {
      "question": "您的問題",
      "answer": "您的回答",
      "keywords": ["關鍵字"],
      "category": "general"
    }
  ]
}
```

#### 修改聯絡資訊

編輯 `resources/data/chatbot/contacts/service_info.json`:

```json
{
  "phone": "03-4227723",
  "line_id": "@hong-yu",
  "email": "service@hong-yu.com",
  "address": "桃園市中壢區...",
  "business_hours": {
    "weekday": "週一至週五 09:00-18:00",
    "weekend": "週六 09:00-12:00"
  }
}
```

---

## 測試驗證

### ✅ 功能測試清單

```bash
# 1. 啟動開發伺服器
php artisan serve

# 2. 開啟瀏覽器訪問
open http://localhost:8000
```

#### A. 視覺檢查

- [ ] 右下角出現紫色圓形浮動按鈕
- [ ] 點擊按鈕，聊天視窗彈出（動畫流暢）
- [ ] Header 顯示 Logo 和「虹宇職訓」（或您的機構名稱）
- [ ] 歡迎訊息正確顯示
- [ ] 快速按鈕正確顯示（查看課程、補助資格、常見問題、聯絡客服）

#### B. 功能測試

**測試1: 快速按鈕**
```
點擊「查看課程」→ 應顯示課程列表
點擊「補助資格」→ 應詢問就業狀況
點擊「常見問題」→ 應顯示FAQ列表
點擊「聯絡客服」→ 應顯示聯絡資訊
```

**測試2: 課程查詢**
```
輸入: "AI課程有哪些"
預期: 顯示 AI 相關課程列表（最多5個）+ 編號快速按鈕

輸入: "1"
預期: 顯示第1個課程的詳細資訊
```

**測試3: 補助查詢**
```
輸入: "補助資格"
預期: 詢問「您是在職者還是待業者？」

選擇: "我是在職者"
預期: 顯示在職者補助資訊（80%基本補助，100%特定身份）
```

**測試4: 上下文理解**
```
輸入: "查看課程"
預期: 顯示課程列表

輸入: "第2個"（不輸入完整問題）
預期: 應該理解指的是課程列表中的第2個，顯示課程詳情
```

**測試5: 常見問題**
```
輸入: "如何報名"
預期: 顯示報名流程（線上報名 → 資格審查 → 繳費...）
```

#### C. 瀏覽器Console檢查

打開開發者工具（F12），檢查 Console:

```javascript
// 應該沒有 JavaScript 錯誤
// Livewire 應該正常載入
// 沒有 404 圖片載入失敗
```

#### D. 日誌檢查

```bash
# 查看 Laravel 日誌
tail -f storage/logs/laravel.log

# 應該看到類似這樣的日誌（發送訊息時）
[2025-10-28 10:45:07] local.INFO: ClassificationAgent::handle {"original":"AI課程有哪些","preprocessed":"AI課程有哪些"}
[2025-10-28 10:45:08] local.INFO: ClassificationAgent: Using fuzzy match {"message":"AI課程有哪些","match":"course"}
```

---

## 常見問題

### Q1: 聊天按鈕沒有出現

**可能原因**:
1. Livewire 未正確載入
2. CSS 未編譯
3. Layout 未加入 `@livewire('chatbot-widget')`

**解決方法**:
```bash
# 1. 檢查 Livewire 是否安裝
composer show livewire/livewire

# 2. 檢查 Layout 是否加入 @livewireStyles 和 @livewireScripts
# 3. 清除緩存
php artisan view:clear

# 4. 重新編譯 CSS
npm run dev
```

---

### Q2: 點擊按鈕沒有反應

**可能原因**:
1. JavaScript 錯誤
2. Livewire scripts 未載入
3. CSRF token 問題

**解決方法**:
```bash
# 1. 檢查瀏覽器 Console 是否有錯誤（F12）
# 2. 確認 @livewireScripts 在 </body> 之前
# 3. 清除緩存
php artisan optimize:clear
```

---

### Q3: 訊息發送後沒有回應

**可能原因**:
1. Session 未正確初始化
2. 命名空間錯誤
3. JSON 資料檔案遺失

**解決方法**:
```bash
# 1. 檢查 Session 配置
php artisan config:cache

# 2. 檢查 JSON 檔案是否存在
ls -la resources/data/chatbot/courses/

# 3. 檢查日誌錯誤
tail -f storage/logs/laravel.log

# 4. 測試 SessionManager
php artisan tinker
>>> $session = app(\App\Services\Chatbot\SessionManager::class);
>>> $session->setContext('test', 'value');
>>> $session->getContext('test');
// 應該返回 'value'
```

---

### Q4: OpenAI 相關錯誤

**錯誤訊息**: `OpenAI API Key not configured` 或 `401 Unauthorized`

**解決方法**:
```bash
# 1. 檢查 .env 檔案
cat .env | grep OPENAI

# 2. 應該看到
OPENAI_API_KEY=sk-...
OPENAI_AGENT_MODEL=gpt-3.5-turbo

# 3. 清除配置緩存
php artisan config:clear

# 4. 驗證 API Key
# 訪問 https://platform.openai.com/api-keys 確認 Key 有效
```

**沒有 API Key?**
系統會自動降級使用模糊匹配和模式檢測，仍可正常運作（智能度較低）。

---

### Q5: 課程列表顯示空白

**可能原因**:
1. JSON 檔案路徑錯誤
2. JSON 格式錯誤
3. 緩存問題

**解決方法**:
```bash
# 1. 檢查 JSON 檔案
cat resources/data/chatbot/courses/course_list.json

# 2. 驗證 JSON 格式
php -r "json_decode(file_get_contents('resources/data/chatbot/courses/course_list.json'));"
# 沒有輸出表示格式正確

# 3. 清除 RAG 緩存
php artisan cache:clear

# 4. 測試 RAGService
php artisan tinker
>>> $rag = app(\App\Services\Chatbot\RAGService::class);
>>> $courses = $rag->queryCourses();
>>> count($courses);
// 應該返回課程數量（例如 60）
```

---

### Q6: 命名空間錯誤

**錯誤訊息**: `Class 'App\Services\Agents\ClassificationAgent' not found`

**解決方法**:
```bash
# 1. 確認命名空間已正確修改
grep -r "namespace App\\Services\\Agents" app/Services/Chatbot/

# 應該沒有任何輸出（表示都已改為 App\Services\Chatbot\Agents）

# 2. 確認 use 語句已更新
grep -r "use App\\Services\\Agents\\" app/

# 應該沒有任何輸出（除了 ChatbotWidget.php 中正確的引用）

# 3. 重新產生 autoload
composer dump-autoload

# 4. 清除緩存
php artisan optimize:clear
```

---

### Q7: 樣式跑版或不正常

**可能原因**:
1. Tailwind CSS 未正確配置
2. CSS 未編譯
3. Alpine.js 未載入

**解決方法**:
```bash
# 1. 檢查 Tailwind 配置
cat tailwind.config.js

# 確保 content 包含 Livewire 視圖
content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
],

# 2. 重新編譯 CSS
npm run dev

# 3. 檢查是否有 Alpine.js（Livewire 自帶）
# 在瀏覽器 Console 輸入:
# Alpine
# 應該返回 Alpine 對象

# 4. 強制重新載入頁面（Ctrl+Shift+R）
```

---

## 附錄

### A. 完整的資料夾結構

```
target-project/
├── app/
│   ├── Http/
│   │   └── Livewire/
│   │       └── ChatbotWidget.php
│   └── Services/
│       └── Chatbot/                    ← 智能客服核心
│           ├── Agents/
│           │   ├── BaseAgent.php
│           │   ├── ClassificationAgent.php
│           │   ├── CourseAgent.php
│           │   ├── SubsidyAgent.php
│           │   ├── FAQAgent.php
│           │   ├── EnrollmentAgent.php
│           │   └── HumanServiceAgent.php
│           ├── OpenAIService.php
│           ├── SessionManager.php
│           └── RAGService.php
├── resources/
│   ├── data/
│   │   └── chatbot/                    ← 所有資料檔案
│   │       ├── courses/
│   │       │   ├── course_list.json
│   │       │   └── course_mapping.json
│   │       ├── faq/
│   │       │   ├── general_faq.json
│   │       │   └── enrollment_process.json
│   │       ├── subsidy/
│   │       │   ├── employed_rules.json
│   │       │   ├── unemployed_rules.json
│   │       │   ├── subsidy_documents.json
│   │       │   └── subsidy_faq.json
│   │       ├── greetings/
│   │       │   └── default_responses.json
│   │       ├── contacts/
│   │       │   └── service_info.json
│   │       └── quick_options/
│   │           └── button_config.json
│   └── views/
│       └── livewire/
│           └── chatbot-widget.blade.php
└── public/
    ├── logo.png
    ├── agent.png
    └── images/
        └── line@.png
```

### B. 批量操作腳本

建立一個整合腳本 `integrate-chatbot.sh`:

```bash
#!/bin/bash

# 虹宇智能客服整合腳本
# 使用方法: ./integrate-chatbot.sh /path/to/source /path/to/target

SOURCE_DIR=$1
TARGET_DIR=$2

if [ -z "$SOURCE_DIR" ] || [ -z "$TARGET_DIR" ]; then
    echo "使用方法: ./integrate-chatbot.sh <source-dir> <target-dir>"
    exit 1
fi

echo "🚀 開始整合虹宇智能客服系統..."
echo "來源: $SOURCE_DIR"
echo "目標: $TARGET_DIR"
echo ""

# 1. 建立目錄結構
echo "📁 建立目錄結構..."
mkdir -p "$TARGET_DIR/app/Services/Chatbot/Agents"
mkdir -p "$TARGET_DIR/resources/data/chatbot"
mkdir -p "$TARGET_DIR/resources/views/livewire"
mkdir -p "$TARGET_DIR/public/images"

# 2. 複製 Agents
echo "📦 複製 Agent 類別..."
cp "$SOURCE_DIR/app/Services/Agents/"*.php "$TARGET_DIR/app/Services/Chatbot/Agents/"

# 3. 複製核心 Services
echo "📦 複製核心 Services..."
cp "$SOURCE_DIR/app/Services/OpenAIService.php" "$TARGET_DIR/app/Services/Chatbot/"
cp "$SOURCE_DIR/app/Services/SessionManager.php" "$TARGET_DIR/app/Services/Chatbot/"
cp "$SOURCE_DIR/app/Services/RAGService.php" "$TARGET_DIR/app/Services/Chatbot/"

# 4. 複製 Livewire 組件
echo "📦 複製 Livewire 組件..."
cp "$SOURCE_DIR/app/Http/Livewire/ChatbotWidget.php" "$TARGET_DIR/app/Http/Livewire/"
cp "$SOURCE_DIR/resources/views/livewire/chatbot-widget.blade.php" "$TARGET_DIR/resources/views/livewire/"

# 5. 複製資料檔案
echo "📦 複製資料檔案..."
cp -r "$SOURCE_DIR/resources/data/chatbot/"* "$TARGET_DIR/resources/data/chatbot/"

# 6. 複製靜態資源
echo "📦 複製靜態資源..."
cp "$SOURCE_DIR/public/logo.png" "$TARGET_DIR/public/"
cp "$SOURCE_DIR/public/agent.png" "$TARGET_DIR/public/"
cp "$SOURCE_DIR/public/images/line@.png" "$TARGET_DIR/public/images/" 2>/dev/null || true

# 7. 修改命名空間
echo "🔧 修改命名空間..."
cd "$TARGET_DIR/app/Services/Chatbot"

# macOS
if [[ "$OSTYPE" == "darwin"* ]]; then
    find . -name "*.php" -exec sed -i '' 's/namespace App\\Services\\Agents;/namespace App\\Services\\Chatbot\\Agents;/g' {} +
    find . -name "*.php" -exec sed -i '' 's/namespace App\\Services;/namespace App\\Services\\Chatbot;/g' {} +
    find . -name "*.php" -exec sed -i '' 's/use App\\Services\\Agents\\BaseAgent;/use App\\Services\\Chatbot\\Agents\\BaseAgent;/g' {} +
else
    # Linux
    find . -name "*.php" -exec sed -i 's/namespace App\\Services\\Agents;/namespace App\\Services\\Chatbot\\Agents;/g' {} +
    find . -name "*.php" -exec sed -i 's/namespace App\\Services;/namespace App\\Services\\Chatbot;/g' {} +
    find . -name "*.php" -exec sed -i 's/use App\\Services\\Agents\\BaseAgent;/use App\\Services\\Chatbot\\Agents\\BaseAgent;/g' {} +
fi

echo ""
echo "✅ 整合完成！"
echo ""
echo "📋 接下來請執行："
echo "1. cd $TARGET_DIR"
echo "2. composer require livewire/livewire:^2.10"
echo "3. 編輯 .env 加入 OPENAI_API_KEY"
echo "4. php artisan optimize:clear"
echo "5. 在 Layout 中加入 @livewire('chatbot-widget')"
echo ""
```

使用方法:

```bash
chmod +x integrate-chatbot.sh
./integrate-chatbot.sh ~/Sites/chatbot-ui3 ~/Sites/target-project
```

### C. 驗證腳本

建立驗證腳本 `verify-integration.sh`:

```bash
#!/bin/bash

# 驗證整合是否成功

TARGET_DIR=$1

if [ -z "$TARGET_DIR" ]; then
    echo "使用方法: ./verify-integration.sh <target-dir>"
    exit 1
fi

echo "🔍 驗證智能客服整合..."
echo ""

ERRORS=0

# 檢查目錄
echo "📁 檢查目錄結構..."
[ -d "$TARGET_DIR/app/Services/Chatbot" ] && echo "✅ Chatbot 目錄存在" || { echo "❌ Chatbot 目錄不存在"; ERRORS=$((ERRORS+1)); }
[ -d "$TARGET_DIR/app/Services/Chatbot/Agents" ] && echo "✅ Agents 目錄存在" || { echo "❌ Agents 目錄不存在"; ERRORS=$((ERRORS+1)); }
[ -d "$TARGET_DIR/resources/data/chatbot" ] && echo "✅ 資料目錄存在" || { echo "❌ 資料目錄不存在"; ERRORS=$((ERRORS+1)); }

echo ""

# 檢查核心檔案
echo "📄 檢查核心檔案..."
[ -f "$TARGET_DIR/app/Services/Chatbot/Agents/ClassificationAgent.php" ] && echo "✅ ClassificationAgent.php" || { echo "❌ ClassificationAgent.php 遺失"; ERRORS=$((ERRORS+1)); }
[ -f "$TARGET_DIR/app/Services/Chatbot/OpenAIService.php" ] && echo "✅ OpenAIService.php" || { echo "❌ OpenAIService.php 遺失"; ERRORS=$((ERRORS+1)); }
[ -f "$TARGET_DIR/app/Http/Livewire/ChatbotWidget.php" ] && echo "✅ ChatbotWidget.php" || { echo "❌ ChatbotWidget.php 遺失"; ERRORS=$((ERRORS+1)); }

echo ""

# 檢查資料檔案
echo "📊 檢查資料檔案..."
[ -f "$TARGET_DIR/resources/data/chatbot/courses/course_list.json" ] && echo "✅ course_list.json" || { echo "❌ course_list.json 遺失"; ERRORS=$((ERRORS+1)); }
[ -f "$TARGET_DIR/resources/data/chatbot/faq/general_faq.json" ] && echo "✅ general_faq.json" || { echo "❌ general_faq.json 遺失"; ERRORS=$((ERRORS+1)); }

echo ""

# 檢查靜態資源
echo "🖼️  檢查靜態資源..."
[ -f "$TARGET_DIR/public/logo.png" ] && echo "✅ logo.png" || { echo "❌ logo.png 遺失"; ERRORS=$((ERRORS+1)); }
[ -f "$TARGET_DIR/public/agent.png" ] && echo "✅ agent.png" || { echo "❌ agent.png 遺失"; ERRORS=$((ERRORS+1)); }

echo ""

# 檢查命名空間
echo "🔧 檢查命名空間..."
if grep -r "namespace App\\\\Services\\\\Agents" "$TARGET_DIR/app/Services/Chatbot" > /dev/null; then
    echo "❌ 發現舊命名空間（未修改）"
    ERRORS=$((ERRORS+1))
else
    echo "✅ 命名空間已正確更新"
fi

echo ""
echo "================================"
if [ $ERRORS -eq 0 ]; then
    echo "✅ 驗證通過！整合成功。"
    echo ""
    echo "請繼續執行："
    echo "1. cd $TARGET_DIR"
    echo "2. composer dump-autoload"
    echo "3. php artisan optimize:clear"
    echo "4. php artisan serve"
else
    echo "❌ 發現 $ERRORS 個錯誤，請檢查整合過程。"
fi
echo "================================"
```

使用方法:

```bash
chmod +x verify-integration.sh
./verify-integration.sh ~/Sites/target-project
```

---

### D. 相關資源

- **Laravel 官方文檔**: https://laravel.com/docs/9.x
- **Livewire 官方文檔**: https://laravel-livewire.com/docs/2.x/quickstart
- **Tailwind CSS**: https://tailwindcss.com/docs
- **OpenAI API**: https://platform.openai.com/docs

---

## 📞 支援

如果在整合過程中遇到問題:

1. 檢查本文檔的「常見問題」章節
2. 查看 Laravel 日誌: `storage/logs/laravel.log`
3. 查閱 `DEVELOPMENT_STATUS.md` 了解已知問題
4. 聯絡技術支援: support@example.com

---

**整合愉快！** 🎉
