# 虹宇智能客服系統 - 遷移檢查清單

> **版本**: v1.1.0
> **最後更新**: 2025-11-03
> **預計時間**: 40-50 分鐘
> **支援**: Laravel 8.83+ / PHP 7.4+

## 📋 完整檢查清單

### 前置準備（5分鐘）

- [ ] **Step 0.1**: 確認目標專案 Laravel 版本
  ```bash
  cd /path/to/target-project
  php artisan --version
  # 應該顯示: Laravel Framework 8.83.x 或更高
  ```

- [ ] **Step 0.2**: 確認 PHP 版本
  ```bash
  php -v
  # 應該顯示: PHP 7.4.x 或更高 (支援 PHP 7.4 - 8.2)
  ```

- [ ] **Step 0.3**: 檢查 Composer
  ```bash
  composer --version
  # 確保 Composer 已安裝
  ```

- [ ] **Step 0.4**: 備份目標專案（重要！）
  ```bash
  cd /path/to/target-project
  git add .
  git commit -m "Backup before chatbot integration"
  git push origin main  # 或您的分支名稱
  ```

---

### Phase 1: 安裝依賴（5分鐘）

- [ ] **Step 1.1**: 安裝 Livewire
  ```bash
  cd /path/to/target-project
  composer require livewire/livewire:^2.10
  ```

  **驗證**:
  ```bash
  composer show livewire/livewire
  # 應該顯示: versions : * v2.10.x
  ```

- [ ] **Step 1.2**: 檢查 Guzzle（通常已安裝）
  ```bash
  composer show guzzlehttp/guzzle
  # 應該顯示: versions : * 7.x
  ```

  如果未安裝:
  ```bash
  composer require guzzlehttp/guzzle:^7.2
  ```

---

### Phase 2: 複製檔案（15分鐘）

#### A. 複製 Services（10個PHP檔案）

- [ ] **Step 2.1**: 建立目錄結構
  ```bash
  mkdir -p app/Services/Chatbot/Agents
  ```

- [ ] **Step 2.2**: 複製所有 Agent 類別（7個）
  ```bash
  cp chatbot-ui3/app/Services/Agents/*.php app/Services/Chatbot/Agents/
  ```

  **驗證**:
  ```bash
  ls -la app/Services/Chatbot/Agents/
  # 應該看到 7 個 PHP 檔案
  ```

- [ ] **Step 2.3**: 複製核心 Services（3個）
  ```bash
  cp chatbot-ui3/app/Services/OpenAIService.php app/Services/Chatbot/
  cp chatbot-ui3/app/Services/SessionManager.php app/Services/Chatbot/
  cp chatbot-ui3/app/Services/RAGService.php app/Services/Chatbot/
  ```

  **驗證**:
  ```bash
  ls -la app/Services/Chatbot/
  # 應該看到 3 個 PHP 檔案 + Agents 資料夾
  ```

#### B. 複製 Livewire 組件（2個檔案）

- [ ] **Step 2.4**: 複製 Livewire PHP 組件
  ```bash
  cp chatbot-ui3/app/Http/Livewire/ChatbotWidget.php app/Http/Livewire/
  ```

- [ ] **Step 2.5**: 複製 Livewire Blade 視圖
  ```bash
  mkdir -p resources/views/livewire
  cp chatbot-ui3/resources/views/livewire/chatbot-widget.blade.php resources/views/livewire/
  ```

  **驗證**:
  ```bash
  ls -la app/Http/Livewire/ChatbotWidget.php
  ls -la resources/views/livewire/chatbot-widget.blade.php
  # 兩個檔案都應該存在
  ```

#### C. 複製資料檔案（11個JSON）

- [ ] **Step 2.6**: 建立資料目錄結構
  ```bash
  mkdir -p resources/data/chatbot/{courses,faq,subsidy,greetings,contacts,quick_options}
  ```

- [ ] **Step 2.7**: 複製所有 JSON 資料
  ```bash
  cp -r chatbot-ui3/resources/data/chatbot/* resources/data/chatbot/
  ```

  **驗證**:
  ```bash
  find resources/data/chatbot -name "*.json"
  # 應該顯示 11 個 JSON 檔案
  ```

#### D. 複製靜態資源（3個圖片）

- [ ] **Step 2.8**: 複製 Logo 和頭像
  ```bash
  cp chatbot-ui3/public/logo.png public/
  cp chatbot-ui3/public/agent.png public/
  ```

- [ ] **Step 2.9**: 複製 LINE 圖示（如果有）
  ```bash
  mkdir -p public/images
  cp chatbot-ui3/public/images/line@.png public/images/  2>/dev/null || echo "LINE 圖示不存在，跳過"
  ```

  **驗證**:
  ```bash
  ls -la public/logo.png public/agent.png
  # 應該看到兩個圖片檔案
  ```

---

### Phase 3: 調整命名空間（10分鐘）

#### A. 修改 Agent 類別

- [ ] **Step 3.1**: 批量修改 Agent 命名空間
  ```bash
  cd app/Services/Chatbot/Agents

  # macOS
  find . -name "*.php" -exec sed -i '' 's/namespace App\\Services\\Agents;/namespace App\\Services\\Chatbot\\Agents;/g' {} +

  # Linux
  find . -name "*.php" -exec sed -i 's/namespace App\\Services\\Agents;/namespace App\\Services\\Chatbot\\Agents;/g' {} +
  ```

- [ ] **Step 3.2**: 修改 Agent use 語句
  ```bash
  # macOS
  find . -name "*.php" -exec sed -i '' 's/use App\\Services\\Agents\\BaseAgent;/use App\\Services\\Chatbot\\Agents\\BaseAgent;/g' {} +

  # Linux
  find . -name "*.php" -exec sed -i 's/use App\\Services\\Agents\\BaseAgent;/use App\\Services\\Chatbot\\Agents\\BaseAgent;/g' {} +
  ```

  **驗證**:
  ```bash
  grep -r "namespace App\\\\Services\\\\Agents" .
  # 不應該有任何輸出（表示都已改為 Chatbot\Agents）
  ```

#### B. 修改核心 Services

- [ ] **Step 3.3**: 修改 Services 命名空間
  ```bash
  cd ../..  # 回到 app/Services/Chatbot

  # macOS
  sed -i '' 's/namespace App\\Services;/namespace App\\Services\\Chatbot;/g' OpenAIService.php
  sed -i '' 's/namespace App\\Services;/namespace App\\Services\\Chatbot;/g' SessionManager.php
  sed -i '' 's/namespace App\\Services;/namespace App\\Services\\Chatbot;/g' RAGService.php

  # Linux
  sed -i 's/namespace App\\Services;/namespace App\\Services\\Chatbot;/g' OpenAIService.php
  sed -i 's/namespace App\\Services;/namespace App\\Services\\Chatbot;/g' SessionManager.php
  sed -i 's/namespace App\\Services;/namespace App\\Services\\Chatbot;/g' RAGService.php
  ```

  **驗證**:
  ```bash
  grep "namespace App\\\\Services\\\\Chatbot" *.php
  # 應該顯示 3 個檔案的命名空間
  ```

#### C. 修改 Livewire 組件

- [ ] **Step 3.4**: 手動編輯 ChatbotWidget.php
  ```bash
  cd ../..  # 回到 project root
  nano app/Http/Livewire/ChatbotWidget.php
  # 或使用您喜歡的編輯器
  ```

  修改 use 語句:
  ```php
  // 原始
  use App\Services\SessionManager;
  use App\Services\Agents\ClassificationAgent;

  // 修改為
  use App\Services\Chatbot\SessionManager;
  use App\Services\Chatbot\Agents\ClassificationAgent;
  ```

  **驗證**:
  ```bash
  grep "use App\\\\Services\\\\Chatbot" app/Http/Livewire/ChatbotWidget.php
  # 應該顯示 2 行
  ```

---

### Phase 4: 配置環境（5分鐘）

- [ ] **Step 4.1**: 編輯 .env 檔案
  ```bash
  nano .env
  ```

  添加:
  ```env
  # OpenAI API 配置（智能客服）
  OPENAI_API_KEY=your-openai-api-key-here
  OPENAI_AGENT_MODEL=gpt-3.5-turbo
  ```

- [ ] **Step 4.2**: 確認 Session 配置
  ```bash
  grep "SESSION_DRIVER" .env
  # 應該顯示: SESSION_DRIVER=file（或其他有效的driver）
  ```

  **驗證**:
  ```bash
  php artisan config:cache
  php artisan config:clear
  # 確保配置生效
  ```

---

### Phase 5: 整合到 Layout（5分鐘）

- [ ] **Step 5.1**: 編輯主 Layout 檔案
  ```bash
  # 通常是 resources/views/layouts/app.blade.php
  nano resources/views/layouts/app.blade.php
  ```

- [ ] **Step 5.2**: 在 `<head>` 中添加 Livewire Styles
  ```blade
  <!-- Livewire Styles -->
  @livewireStyles
  ```

- [ ] **Step 5.3**: 在 `</body>` 之前添加聊天視窗和 Livewire Scripts
  ```blade
  <!-- 智能客服聊天視窗 -->
  @livewire('chatbot-widget')

  <!-- Livewire Scripts -->
  @livewireScripts
  ```

  **完整範例**:
  ```blade
  <!DOCTYPE html>
  <html lang="zh-TW">
  <head>
      <meta charset="UTF-8">
      <title>@yield('title')</title>
      <link href="{{ mix('css/app.css') }}" rel="stylesheet">
      @livewireStyles  <!-- 新增 -->
  </head>
  <body>
      @yield('content')

      @livewire('chatbot-widget')  <!-- 新增 -->

      <script src="{{ mix('js/app.js') }}"></script>
      @livewireScripts  <!-- 新增 -->
  </body>
  </html>
  ```

---

### Phase 6: 清除緩存與測試（10分鐘）

#### A. 清除所有緩存

- [ ] **Step 6.1**: 執行清除命令
  ```bash
  php artisan optimize:clear
  ```

  或逐一清除:
  ```bash
  php artisan cache:clear
  php artisan config:clear
  php artisan route:clear
  php artisan view:clear
  ```

- [ ] **Step 6.2**: 重新產生 autoload
  ```bash
  composer dump-autoload
  ```

  **驗證**:
  ```bash
  php artisan list
  # 確保沒有錯誤訊息
  ```

#### B. 啟動開發伺服器

- [ ] **Step 6.3**: 啟動伺服器
  ```bash
  php artisan serve
  ```

  **驗證**:
  ```
  應該顯示: Laravel development server started: http://127.0.0.1:8000
  ```

#### C. 瀏覽器測試

- [ ] **Step 6.4**: 開啟瀏覽器
  ```bash
  open http://localhost:8000
  # 或手動訪問 http://localhost:8000
  ```

- [ ] **Step 6.5**: 視覺檢查
  - [ ] 右下角出現紫色圓形浮動按鈕
  - [ ] 點擊按鈕，聊天視窗彈出（動畫流暢）
  - [ ] Header 顯示 Logo 和「虹宇職訓」
  - [ ] 歡迎訊息正確顯示
  - [ ] 快速按鈕正確顯示（4個）

#### D. 功能測試

- [ ] **Step 6.6**: 測試快速按鈕
  - [ ] 點擊「查看課程」→ 應顯示課程列表
  - [ ] 點擊「補助資格」→ 應詢問就業狀況
  - [ ] 點擊「常見問題」→ 應顯示FAQ
  - [ ] 點擊「聯絡客服」→ 應顯示聯絡資訊

- [ ] **Step 6.7**: 測試課程查詢
  ```
  輸入: "AI課程有哪些"
  預期: 顯示 AI 相關課程列表 + 編號快速按鈕(1-5)
  ```

  ```
  輸入: "1"
  預期: 顯示第1個課程的詳細資訊
  ```

- [ ] **Step 6.8**: 測試補助查詢
  ```
  輸入: "補助資格"
  預期: 詢問「您是在職者還是待業者？」+ 快速按鈕
  ```

  ```
  選擇: "我是在職者"
  預期: 顯示在職者補助資訊（80%基本，100%特定身份）
  ```

- [ ] **Step 6.9**: 測試上下文理解
  ```
  輸入: "查看課程"
  預期: 顯示課程列表

  輸入: "第2個"（不輸入完整問題）
  預期: 理解為第2個課程，顯示課程詳情
  ```

---

### Phase 7: 日誌檢查（5分鐘）

- [ ] **Step 7.1**: 開啟日誌監控
  ```bash
  tail -f storage/logs/laravel.log
  ```

- [ ] **Step 7.2**: 發送測試訊息，觀察日誌
  ```
  應該看到類似:
  [2025-10-28 10:45:07] local.INFO: ClassificationAgent::handle {"original":"AI課程有哪些"}
  [2025-10-28 10:45:08] local.INFO: ClassificationAgent: Using fuzzy match {"match":"course"}
  ```

- [ ] **Step 7.3**: 確認沒有錯誤
  ```
  不應該看到 ERROR 或 Exception
  ```

---

### Phase 8: 瀏覽器 Console 檢查（2分鐘）

- [ ] **Step 8.1**: 打開開發者工具（F12）

- [ ] **Step 8.2**: 檢查 Console
  - [ ] 沒有 JavaScript 錯誤
  - [ ] Livewire 正常載入
  - [ ] 沒有 404 圖片載入失敗

- [ ] **Step 8.3**: 檢查 Network
  - [ ] Livewire 請求成功（200 OK）
  - [ ] 圖片資源載入成功

---

## 🐛 常見問題排查

### 問題 1: 聊天按鈕沒有出現

**檢查清單**:
- [ ] Livewire 是否安裝？`composer show livewire/livewire`
- [ ] Layout 是否加入 `@livewireStyles`？
- [ ] Layout 是否加入 `@livewire('chatbot-widget')`？
- [ ] Layout 是否加入 `@livewireScripts`？
- [ ] 清除緩存了嗎？`php artisan view:clear`

**修復**:
```bash
php artisan view:clear
php artisan optimize:clear
# 重新整理頁面（Ctrl+Shift+R）
```

---

### 問題 2: 命名空間錯誤

**錯誤訊息**: `Class 'App\Services\Agents\ClassificationAgent' not found`

**檢查清單**:
- [ ] 命名空間是否已修改？
  ```bash
  grep -r "namespace App\\\\Services\\\\Agents" app/Services/Chatbot/
  # 不應該有任何輸出
  ```

- [ ] ChatbotWidget.php 的 use 語句是否已更新？
  ```bash
  grep "use App\\\\Services\\\\Chatbot" app/Http/Livewire/ChatbotWidget.php
  # 應該顯示 2 行
  ```

**修復**:
```bash
# 重新執行命名空間修改步驟（Step 3.1 - 3.4）
composer dump-autoload
php artisan optimize:clear
```

---

### 問題 3: JSON 文件找不到

**錯誤訊息**: `Knowledge base file not found: courses/course_list.json`

**檢查清單**:
- [ ] JSON 文件是否已複製？
  ```bash
  ls -la resources/data/chatbot/courses/course_list.json
  ```

- [ ] RAGService 的路徑是否正確？
  ```php
  // 應該是:
  $path = resource_path("data/chatbot/{$filename}");
  ```

**修復**:
```bash
# 重新複製 JSON 文件（Step 2.6 - 2.7）
php artisan cache:clear
```

---

### 問題 4: OpenAI API 錯誤

**錯誤訊息**: `OpenAI API Key not configured` 或 `401 Unauthorized`

**檢查清單**:
- [ ] .env 是否有 `OPENAI_API_KEY`？
  ```bash
  grep "OPENAI_API_KEY" .env
  ```

- [ ] API Key 是否有效？（訪問 https://platform.openai.com/api-keys 確認）

**修復**:
```bash
# 1. 編輯 .env
nano .env
# 添加或更新 OPENAI_API_KEY=sk-...

# 2. 清除配置緩存
php artisan config:clear

# 3. 驗證
php artisan tinker
>>> env('OPENAI_API_KEY')
# 應該顯示您的 API Key
```

**暫時沒有 API Key?**
系統會自動降級使用模糊匹配，仍可正常運作（智能度較低）。

---

### 問題 5: 樣式跑版

**症狀**: 聊天視窗樣式不正常

**檢查清單**:
- [ ] Tailwind CSS 是否已編譯？
  ```bash
  npm run dev
  ```

- [ ] Alpine.js 是否載入？（Livewire 自帶）
  ```javascript
  // 在瀏覽器 Console 輸入:
  Alpine
  // 應該返回 Alpine 對象
  ```

**修復**:
```bash
npm install
npm run dev
# 重新整理頁面（Ctrl+Shift+R）
```

---

## 🔄 回滾指南

如果整合出現問題需要回滾：

### 快速回滾（Git）

```bash
# 1. 回到整合前的狀態
git reset --hard HEAD~1

# 2. 或恢復特定檔案
git checkout HEAD -- app/Http/Livewire/ChatbotWidget.php
git checkout HEAD -- resources/views/layouts/app.blade.php

# 3. 清除緩存
php artisan optimize:clear
```

### 手動回滾

1. **移除 Livewire**（如果是新安裝的）
   ```bash
   composer remove livewire/livewire
   ```

2. **刪除智能客服檔案**
   ```bash
   rm -rf app/Services/Chatbot
   rm -f app/Http/Livewire/ChatbotWidget.php
   rm -f resources/views/livewire/chatbot-widget.blade.php
   rm -rf resources/data/chatbot
   rm -f public/logo.png public/agent.png
   ```

3. **恢復 Layout**
   ```bash
   # 手動編輯 resources/views/layouts/app.blade.php
   # 移除 @livewireStyles, @livewire('chatbot-widget'), @livewireScripts
   ```

4. **清除緩存**
   ```bash
   php artisan optimize:clear
   composer dump-autoload
   ```

---

## ✅ 完成確認

### 最終檢查清單

- [ ] 所有檔案已複製（29個核心檔案）
- [ ] 命名空間已正確修改
- [ ] 環境變數已配置
- [ ] Layout 已整合 Livewire
- [ ] 聊天按鈕正常顯示
- [ ] 快速按鈕功能正常
- [ ] 課程查詢功能正常
- [ ] 補助查詢功能正常
- [ ] 日誌沒有錯誤
- [ ] 瀏覽器 Console 沒有錯誤

### 提交整合結果

```bash
git add .
git commit -m "整合虹宇智能客服系統 v1.0.0

- 新增 Multi-Agent 智能對話系統
- 整合 Livewire 2.10
- 添加課程查詢、補助諮詢、FAQ功能
- 完整的彈出式聊天視窗UI

文檔：
- INTEGRATION_GUIDE.md
- DEVELOPMENT_STATUS.md
- FILE_MANIFEST.md
- API_DOCUMENTATION.md
- MIGRATION_CHECKLIST.md"

git push origin main
```

---

## 📞 需要協助？

如果遇到問題：

1. **查閱文檔**
   - `INTEGRATION_GUIDE.md` - 詳細整合步驟
   - `API_DOCUMENTATION.md` - API 使用說明
   - `FILE_MANIFEST.md` - 完整文件清單

2. **檢查日誌**
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **驗證命名空間**
   ```bash
   grep -r "namespace App\\\\Services\\\\Agents" app/Services/Chatbot/
   # 不應該有輸出
   ```

4. **測試 RAGService**
   ```bash
   php artisan tinker
   >>> $rag = app(\App\Services\Chatbot\RAGService::class);
   >>> $courses = $rag->queryCourses();
   >>> count($courses);
   # 應該返回課程數量（例如 60）
   ```

---

**檢查清單版本**: v1.1.0
**維護者**: Claude Code AI
**最後審查**: 2025-11-03
**Laravel 版本**: 8.83.29
**PHP 版本**: 7.4+ (支援 7.4 - 8.2)

---

## 🎉 整合完成！

恭喜您成功整合虹宇智能客服系統！

現在您的網站右下角應該有一個智能聊天按鈕，可以：
- ✅ 回答課程查詢問題
- ✅ 提供補助資格諮詢
- ✅ 解答常見問題
- ✅ 引導用戶完成報名
- ✅ 提供真人客服聯絡方式

## 📝 版本歷史

### v1.1.0 (2025-11-03)
- 🔄 降級至 Laravel 8.83.29 以支援 PHP 7.4
- ✨ 更新系統需求與相依套件
- 📚 更新文檔以反映新版本

### v1.0.0 (2025-10-28)
- 🎉 首次發布
- ✨ 完整的 Multi-Agent 智能對話系統

**享受您的智能客服系統！** 🚀
