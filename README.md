# 虹宇智能客服系統

> **Laravel 9 智能客服模組 | 獨立可整合式設計**
> **版本**: v1.0.0 | **狀態**: ✅ Production Ready

使用 Laravel 9 開發的智能客服系統，採用**獨立模組設計**，可輕鬆整合到任何 Laravel 9+ 專案。基於 Multi-Agent 架構、Livewire 2.x 即時互動、JSON 資料管理，打造功能完整、易於維護的智能客服解決方案。

---

## ✨ 核心特點

### 技術特色
- 🤖 **Multi-Agent 架構**: 7個專業 Agent 協同工作，處理不同類型查詢
- ⚡ **Livewire 2.x**: 無刷新即時對話體驗
- 🎨 **Tailwind CSS**: 現代化、響應式 UI 設計
- 📚 **JSON RAG**: 輕量級資料管理，無需額外資料庫
- 🧠 **OpenAI 整合**: GPT-3.5/GPT-4 智能意圖分類（可選）
- 🔄 **完整降級機制**: 無 OpenAI API 仍可正常運作

### 功能模組
- ✅ **課程查詢**: 60個課程、關鍵字搜尋、分頁顯示、智能推薦
- ✅ **補助諮詢**: 在職/待業分類、特定身份識別、證明文件查詢
- ✅ **常見問題**: 24個 FAQ、智能匹配、相關問題推薦
- ✅ **報名流程**: 5步驟引導、線上報名連結
- ✅ **真人客服**: 設備檢測、一鍵撥號/LINE（Mobile）

### 智能識別
- ✅ **100% 課程識別率**: 支援虹宇所有實際課程關鍵字（測試通過）
- ✅ **9級優先級路由**: 快速按鈕 → 模式檢測 → GPT分類 → 模糊匹配
- ✅ **上下文感知**: 記憶對話歷史，支援簡短指令（如「1」、「第2個」）
- ✅ **用戶引導**: 無法理解時提供15字建議 + 主選單

---

## 📦 整合到您的專案

### 方式一：獨立模組整合 ⭐ **推薦**

**適合**: 需要整合到現有 Laravel 專案

```bash
# 1. 安裝 Livewire（如未安裝）
composer require livewire/livewire:^2.10

# 2. 複製智能客服檔案
cp -r chatbot-ui3/app/Services/Chatbot your-project/app/Services/
cp -r chatbot-ui3/resources/data/chatbot your-project/resources/data/
cp chatbot-ui3/app/Http/Livewire/ChatbotWidget.php your-project/app/Http/Livewire/
cp chatbot-ui3/resources/views/livewire/chatbot-widget.blade.php your-project/resources/views/livewire/

# 3. 修改命名空間（詳見 INTEGRATION_GUIDE.md）

# 4. 在 Layout 中加入聊天視窗
# @livewire('chatbot-widget')

# 完成！40-50分鐘完成整合
```

**完整整合指南**: 請參閱 [INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md)

---

### 方式二：獨立專案運行

**適合**: 測試、開發、學習

```bash
# 1. Clone 專案
git clone https://github.com/Intelliturst/chatbot-ui3.git
cd chatbot-ui3

# 2. 安裝依賴
composer install
npm install

# 3. 配置環境
cp .env.example .env
php artisan key:generate

# 4. 啟動服務
npm run dev    # 終端1
php artisan serve  # 終端2

# 5. 訪問
open http://localhost:8000/chat-demo
```

---

## 📋 前置需求

| 項目 | 版本需求 | 說明 |
|------|---------|------|
| **PHP** | >= 8.0 | 推薦 8.2 |
| **Laravel** | >= 9.0 | 推薦 9.52+ |
| **Livewire** | 2.10+ | 自動安裝 |
| **Composer** | Latest | - |
| **Node.js** | >= 14.x | 用於前端編譯 |
| **OpenAI API** | 可選 | 無 API Key 仍可運作 |

---

## 📁 專案結構（獨立模組設計）

```
app/Services/Chatbot/               ← 智能客服核心（可整體遷移）
├── Agents/
│   ├── BaseAgent.php               ← 基礎代理
│   ├── ClassificationAgent.php    ← 意圖分類（核心）
│   ├── CourseAgent.php             ← 課程查詢
│   ├── SubsidyAgent.php            ← 補助諮詢
│   ├── FAQAgent.php                ← 常見問題
│   ├── EnrollmentAgent.php         ← 報名流程
│   └── HumanServiceAgent.php       ← 真人客服
├── OpenAIService.php               ← OpenAI API 整合
├── SessionManager.php              ← Session 管理
└── RAGService.php                  ← 資料查詢服務

resources/data/chatbot/             ← 所有資料（可自訂）
├── courses/                        ← 60個課程
├── faq/                            ← 24個FAQ
├── subsidy/                        ← 補助規則、文件
├── greetings/                      ← 歡迎訊息
├── contacts/                       ← 聯絡資訊
└── quick_options/                  ← 快速按鈕配置

app/Http/Livewire/
└── ChatbotWidget.php               ← Livewire 組件

resources/views/livewire/
└── chatbot-widget.blade.php        ← UI 模板

public/
├── logo.png                        ← Logo（可替換）
└── agent.png                       ← AI頭像（可替換）
```

---

## 🚀 快速開始

### 本地測試運行

```bash
# 1. 安裝依賴
composer install
npm install

# 2. 配置環境
cp .env.example .env
php artisan key:generate

# 3. 配置 OpenAI API（可選）
# 編輯 .env:
#   OPENAI_API_KEY=sk-...
#   OPENAI_AGENT_MODEL=gpt-3.5-turbo

# 4. 編譯前端
npm run dev

# 5. 啟動服務
php artisan serve

# 6. 訪問測試頁面
open http://localhost:8000/chat-demo
```

---

## 📚 文檔

### 整合與使用
- **[INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md)** ⭐ - 完整整合指南（18步驟、常見問題、腳本）
- **[MIGRATION_CHECKLIST.md](MIGRATION_CHECKLIST.md)** - 遷移檢查清單（逐步驗證）

### 開發與維護
- **[DEVELOPMENT_STATUS.md](DEVELOPMENT_STATUS.md)** - 開發進度、功能清單、版本歷史
- **[API_DOCUMENTATION.md](API_DOCUMENTATION.md)** - API 使用文檔、擴展開發指南
- **[FILE_MANIFEST.md](FILE_MANIFEST.md)** - 完整文件清單（29個核心檔案）

### 開發規範
- **[CLAUDE.md](CLAUDE.md)** - Claude Code 開發規範（必讀）

---

## 🎯 功能展示

### 1. 智能對話

```
用戶: "AI課程有哪些"
AI: 顯示 AI 相關課程列表（1-5）+ 快速按鈕(1,2,3,4,5)

用戶: "2"
AI: 顯示第2個課程詳細資訊（名稱、內容、時數、費用）
```

### 2. 上下文感知

```
用戶: "補助資格"
AI: 請問您目前的就業狀況？[我是在職者] [我是待業者]

用戶: "我是在職者"
AI: 在職者補助資訊（80%基本，100%特定身份）

用戶: "我是原住民需要什麼文件"（不用再說就業狀況）
AI: 原住民身份證明文件清單（記住了在職者狀態）
```

### 3. 模糊識別

```
用戶: "多媒體"（簡短關鍵字）
AI: 多媒體相關課程列表

用戶: "ChatGPT"
AI: ChatGPT 相關課程列表

用戶: "我想學行銷"
AI: 行銷課程列表
```

---

## 🧪 測試結果

### 課程識別測試（2025-10-28）
- **總測試案例**: 23個
- **課程查詢識別**: 20/20 (100%)
- **負面案例排除**: 3/3 (100%)
- **綜合準確率**: 23/23 (100%)

### 支援的虹宇課程關鍵字
✅ 多媒體設計、電商行銷、職場文書、AI商業設計、影音剪輯、商務office、手機剪輯創作、PMP專案管理、數位行銷、ESG永續發展、室內設計規劃、ChatGPT自動化數據分析

---

## 🛠️ 常用命令

### Laravel 開發
```bash
php artisan serve                   # 啟動開發服務器
php artisan optimize:clear          # 清除所有緩存
php artisan make:livewire YourComponent  # 新增 Livewire 組件
```

### 前端編譯
```bash
npm run dev                         # 開發模式
npm run watch                       # 監聽檔案變更
npm run production                  # 生產模式
```

### Git 工作流程
```bash
git add .
git commit -m "功能描述"
git push origin main                 # 備份到 GitHub
```

---

## 🎨 自訂設定

### 修改 Logo 和頭像

```bash
# 替換 Logo（建議尺寸: 200x50 px）
cp your-logo.png public/logo.png

# 替換 AI 頭像（建議尺寸: 100x100 px）
cp your-agent.png public/agent.png
```

### 修改課程資料

編輯 `resources/data/chatbot/courses/course_list.json`:

```json
{
  "courses": [
    {
      "course_id": "YOUR-001",
      "course_name": "您的課程名稱",
      "type": "unemployed",
      "content": "課程內容說明...",
      "keywords": ["關鍵字1", "關鍵字2"]
    }
  ]
}
```

修改後清除緩存:
```bash
php artisan cache:clear
```

### 修改聯絡資訊

編輯 `resources/data/chatbot/contacts/service_info.json`:

```json
{
  "phone": "您的電話",
  "line_id": "@您的LINE",
  "email": "您的Email",
  "address": "您的地址"
}
```

---

## 🔧 技術架構

### Multi-Agent 系統

```
用戶輸入
    ↓
ClassificationAgent（意圖分類）
    ├─ 優先級 0: 快速按鈕
    ├─ 優先級 1: 純數字（上下文）
    ├─ 優先級 2.4: 補助上下文
    ├─ 優先級 2.5: 課程上下文
    ├─ 優先級 2.6: 課程查詢模式
    ├─ 優先級 3: OpenAI GPT 分類
    ├─ 優先級 4: 模糊關鍵字匹配
    └─ 優先級 5: 用戶引導
          ↓
    ┌─────┴─────┐
    ↓           ↓
CourseAgent   SubsidyAgent
FAQAgent      EnrollmentAgent
HumanServiceAgent
```

### 技術棧

| 層級 | 技術 | 用途 |
|------|------|------|
| **後端框架** | Laravel 9.52 | 核心框架 |
| **前端組件** | Livewire 2.10 | 即時互動 |
| **前端樣式** | Tailwind CSS 3.x | UI設計 |
| **前端行為** | Alpine.js | 互動效果（Livewire內建） |
| **AI整合** | OpenAI GPT-3.5 | 意圖分類（可選） |
| **資料管理** | JSON Files | 輕量級RAG |
| **Session** | Laravel Session | 上下文管理 |

---

## 📊 效能指標

| 指標 | 目標值 | 當前值 | 狀態 |
|------|--------|--------|------|
| 課程識別率 | ≥95% | 100% | ✅ 超標 |
| 快速按鈕成功率 | 100% | 100% | ✅ 達標 |
| 平均回應時間 | <2秒 | ~1.5秒 | ✅ 達標 |

---

## 🤝 貢獻指南

1. Fork 本專案
2. 建立功能分支 (`git checkout -b feature/AmazingFeature`)
3. 遵循 `CLAUDE.md` 開發規範
4. 提交變更 (`git commit -m 'Add some AmazingFeature'`)
5. 推送到分支 (`git push origin feature/AmazingFeature`)
6. 開啟 Pull Request

### 開發規範（必讀）

⚠️ **開始開發前，請務必閱讀 [CLAUDE.md](CLAUDE.md)**

關鍵規則：
- ✅ 搜尋優先（避免重複程式碼）
- ✅ 擴展優先（不建立 v2、enhanced 等檔案）
- ✅ 使用 Task agents（>30秒操作）
- ✅ 頻繁 commit 並推送到 GitHub

---

## 📝 版本歷史

### v1.0.0 (2025-10-28) - Production Ready ✅
- ✨ 完整的 Multi-Agent 系統（7個Agent）
- ✨ 100% 課程識別測試通過
- ✨ 虹宇實際課程關鍵字全支援
- ✨ 完整的整合文檔（5個文檔）
- 🐛 修復 6 個重大Bug
- 📚 完整的 API 文檔

### v0.9.0 (2025-10-27) - Beta
- 系統性改善（用戶引導、模糊匹配）
- 三個關鍵問題修復

### v0.8.0 (2025-10-26) - Alpha
- 快速按鈕系統完成
- 真人客服設備檢測

---

## 📄 授權

MIT License

---

## 🙏 致謝

- **Laravel**: https://laravel.com
- **Livewire**: https://laravel-livewire.com
- **Tailwind CSS**: https://tailwindcss.com
- **OpenAI**: https://platform.openai.com

**Template by**: Chang Ho Chien | HC AI 說人話channel

---

## 📞 支援

- **整合問題**: 查看 [INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md)
- **API 使用**: 查看 [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- **Bug 回報**: GitHub Issues
- **功能建議**: GitHub Issues

---

**⭐ 如果這個專案對您有幫助，請給個 Star！**

---

**整合愉快！** 🎉
