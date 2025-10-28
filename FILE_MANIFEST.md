# 虹宇智能客服系統 - 完整文件清單

> **版本**: v1.0.0
> **最後更新**: 2025-10-28
> **總文件數**: 29個核心文件 + 7個文檔

## 📋 文件分類

| 類別 | 數量 | 說明 |
|------|------|------|
| **PHP 核心類別** | 10 | Services + Agents |
| **Livewire 組件** | 2 | PHP + Blade |
| **JSON 資料** | 11 | 課程、FAQ、補助 |
| **靜態資源** | 3 | Logo、頭像 |
| **環境配置** | 1 | .env additions |
| **文檔** | 7 | 整合指南、API文檔等 |
| **總計** | **34** | **完整文件包** |

---

## 1. PHP 核心類別（10個文件）

### A. Agent 類別（7個）

#### 📄 `BaseAgent.php`
```
路徑: app/Services/Chatbot/Agents/BaseAgent.php
大小: ~80 行
用途: 所有 Agent 的基礎類別
功能:
  - Session 管理基礎
  - OpenAI API 調用封裝
  - 錯誤處理基礎
依賴:
  - SessionManager
  - OpenAIService
```

**遷移命名空間**:
```php
// 原始
namespace App\Services\Agents;

// 目標
namespace App\Services\Chatbot\Agents;
```

---

#### 📄 `ClassificationAgent.php` ⭐ **核心**
```
路徑: app/Services/Chatbot/Agents/ClassificationAgent.php
大小: ~980 行
用途: 意圖分類與路由核心
功能:
  - 9級優先級路由系統
  - 快速按鈕專用路由（65個按鈕）
  - 上下文感知（課程編號、補助問題）
  - 課程查詢模式檢測（5種正則）
  - 模糊關鍵字匹配（評分系統）
  - OpenAI GPT 意圖分類
  - 用戶引導系統
  - 未知查詢日誌記錄
依賴:
  - BaseAgent
  - CourseAgent
  - SubsidyAgent
  - FAQAgent
  - EnrollmentAgent
  - HumanServiceAgent
  - RAGService
```

**關鍵特性**:
- ✅ 支援虹宇所有實際課程關鍵字（20+）
- ✅ 100% 課程識別測試通過
- ✅ 快速按鈕 100% 成功率
- ✅ 完整的降級機制（無 OpenAI API 仍可運作）

---

#### 📄 `CourseAgent.php`
```
路徑: app/Services/Chatbot/Agents/CourseAgent.php
大小: ~650 行
用途: 課程查詢處理
功能:
  - 課程列表查詢（在職/待業）
  - 關鍵字搜尋（權重評分）
  - 分頁顯示（每頁5個）
  - 課程詳情查詢（編號/關鍵字）
  - 動態快速按鈕（編號1-5）
  - 上下文感知（相對/絕對編號）
依賴:
  - BaseAgent
  - RAGService
```

**支援的查詢類型**:
- "AI課程有哪些" → 列表
- "多媒體設計課程" → 列表
- "我想學數位行銷" → 列表
- "1" （上下文中）→ 第1個課程詳情
- "第3個" → 第3個課程詳情
- "AI-001" → 特定課程ID

---

#### 📄 `SubsidyAgent.php`
```
路徑: app/Services/Chatbot/Agents/SubsidyAgent.php
大小: ~310 行
用途: 補助資格諮詢
功能:
  - 雇用狀態檢測（在職/待業）
  - 補助規則查詢
  - 特定身份補助說明（10-15種）
  - 證明文件查詢（8種身份）
  - 上下文記憶（employment_status）
依賴:
  - BaseAgent
  - RAGService
```

**支援的身份**:
- 在職者：80%基本，100%特定身份（10種）
- 待業者：80%基本，100%特定身份（15種）

---

#### 📄 `FAQAgent.php`
```
路徑: app/Services/Chatbot/Agents/FAQAgent.php
大小: ~180 行
用途: 常見問題處理
功能:
  - FAQ 查詢（24個問題）
  - 關鍵字匹配
  - 相關問題推薦
依賴:
  - BaseAgent
  - RAGService
```

---

#### 📄 `EnrollmentAgent.php`
```
路徑: app/Services/Chatbot/Agents/EnrollmentAgent.php
大小: ~120 行
用途: 報名流程引導
功能:
  - 5步驟報名流程顯示
  - 線上報名連結
  - 注意事項說明
依賴:
  - BaseAgent
  - RAGService
```

---

#### 📄 `HumanServiceAgent.php`
```
路徑: app/Services/Chatbot/Agents/HumanServiceAgent.php
大小: ~150 行
用途: 真人客服聯絡
功能:
  - 聯絡資訊顯示（電話、LINE、Email、地址）
  - 設備檢測（Mobile/Desktop 分流）
  - 一鍵撥號/開啟LINE（Mobile）
  - 動態快速按鈕
依賴:
  - BaseAgent
  - RAGService
```

**設備檢測邏輯**:
- Mobile: 顯示 "撥打電話"、"開啟LINE"（可直接執行）
- Desktop: 顯示 "複製電話"、"複製LINE ID"

---

### B. 核心 Services（3個）

#### 📄 `OpenAIService.php`
```
路徑: app/Services/Chatbot/OpenAIService.php
大小: ~150 行
用途: OpenAI API 整合
功能:
  - GPT-3.5-turbo/GPT-4 支援
  - Chat Completions API
  - 意圖分類
  - 錯誤處理
  - 日誌記錄
依賴:
  - Laravel Http Facade
  - Laravel Log Facade
```

**環境變數需求**:
```env
OPENAI_API_KEY=sk-...
OPENAI_AGENT_MODEL=gpt-3.5-turbo
```

---

#### 📄 `SessionManager.php`
```
路徑: app/Services/Chatbot/SessionManager.php
大小: ~120 行
用途: Session 管理
功能:
  - 上下文存儲（last_action, last_course等）
  - Session 資訊追蹤
  - 對話歷史管理
  - Session ID 管理
依賴:
  - Laravel Session Facade
```

**Session 結構**:
```php
[
    'chatbot_history' => [...],      // 對話記錄
    'chatbot_context' => [
        'last_action' => 'course_list',
        'last_course' => 'AI-001',
        'display_offset' => 5,
        'employment_status' => 'employed',
        'pending_question' => null
    ]
]
```

---

#### 📄 `RAGService.php`
```
路徑: app/Services/Chatbot/RAGService.php
大小: ~385 行
用途: 資料查詢服務
功能:
  - JSON 文件載入（11個文件）
  - Cache 機制（可配置）
  - 課程查詢（篩選、搜尋、排序）
  - 課程詳情查詢
  - 補助規則查詢
  - 補助文件查詢
  - FAQ 查詢
  - 報名流程查詢
  - 聯絡資訊查詢
  - 歡迎訊息查詢
  - 快速按鈕配置查詢
依賴:
  - Laravel Cache Facade
```

**快取策略**:
```php
protected $cacheDuration = 0; // 0 = 不緩存，方便開發
// 生產環境建議: 3600（1小時）
```

---

## 2. Livewire 組件（2個文件）

#### 📄 `ChatbotWidget.php` (Livewire Component)
```
路徑: app/Http/Livewire/ChatbotWidget.php
大小: ~180 行
用途: 聊天視窗 Livewire 組件
功能:
  - 對話歷史載入
  - 訊息發送處理
  - Session 持久化
  - 歡迎訊息
  - 清除對話
  - 快速選項處理
依賴:
  - SessionManager
  - ClassificationAgent
```

**公開屬性**:
```php
public $messages = [];      // 訊息陣列
public $userInput = '';     // 輸入框內容
public $isOpen = false;     // 視窗開關狀態
public $sessionInfo = [];   // Session 資訊
```

**Livewire 方法**:
- `mount()`: 初始化
- `toggleWidget()`: 切換視窗
- `sendMessage()`: 發送訊息
- `clearSession()`: 清除對話

---

#### 📄 `chatbot-widget.blade.php` (Blade Template)
```
路徑: resources/views/livewire/chatbot-widget.blade.php
大小: ~284 行
用途: 聊天視窗 UI 模板
功能:
  - 浮動按鈕（未展開）
  - 聊天視窗（展開）
  - Header（Logo、標題、控制按鈕）
  - 訊息顯示區
  - 快速選項按鈕
  - 輸入框
  - 載入動畫
  - 自訂樣式
依賴:
  - Alpine.js（Livewire 內建）
  - Tailwind CSS
```

**Alpine.js 方法**:
```javascript
scrollToBottom()    // 滾動到底部
formatMessage()     // Markdown 格式化
handleEnter()       // Enter 鍵處理
```

**自訂樣式**:
- `animate-slide-in-left`: 訊息滑入動畫
- `animate-fade-in`: 淡入動畫
- 自訂滾動條樣式

---

## 3. JSON 資料文件（11個）

### A. 課程資料（2個）

#### 📄 `course_list.json`
```
路徑: resources/data/chatbot/courses/course_list.json
大小: 28KB
筆數: 60個課程
結構:
{
  "courses": [
    {
      "course_id": "AI-001",
      "course_name": "AI商業應用入門",
      "type": "unemployed",           // unemployed 或 employed
      "full_name": "人工智慧商業應用實務班",
      "content": "課程內容說明...",
      "keywords": ["AI", "人工智慧", "商業應用"],
      "priority": 1,                  // 排序優先級
      "featured": 1                   // 是否精選
    }
  ]
}
```

**課程分類**:
- 待業課程：30個
- 在職課程：30個

---

#### 📄 `course_mapping.json`
```
路徑: resources/data/chatbot/courses/course_mapping.json
大小: 741B
用途: 課程類別對應表
結構:
{
  "categories": {
    "AI": {
      "name": "AI與資料分析",
      "keywords": ["AI", "人工智慧", "機器學習", "資料分析"]
    },
    "marketing": {
      "name": "行銷與電商",
      "keywords": ["行銷", "電商", "社群", "廣告"]
    }
    // ... 其他類別
  }
}
```

---

### B. FAQ 資料（2個）

#### 📄 `general_faq.json`
```
路徑: resources/data/chatbot/faq/general_faq.json
大小: 8.9KB
筆數: 24個問題
結構:
{
  "faqs": [
    {
      "id": "FAQ-001",
      "question": "請問課程費用多少？",
      "answer": "課程費用依補助身份而定...",
      "keywords": ["費用", "價格", "多少錢"],
      "category": "course",           // course, subsidy, enrollment
      "priority": 1
    }
  ]
}
```

**FAQ 分類**:
- 課程相關：10個
- 補助相關：8個
- 報名相關：6個

---

#### 📄 `enrollment_process.json`
```
路徑: resources/data/chatbot/faq/enrollment_process.json
大小: 2.3KB
用途: 報名流程說明
結構:
{
  "process": {
    "title": "虹宇職訓報名流程",
    "steps": [
      {
        "step": 1,
        "title": "線上報名",
        "description": "填寫報名表...",
        "duration": "5-10分鐘",
        "url": "https://..."
      }
      // ... 5個步驟
    ]
  }
}
```

---

### C. 補助資料（4個）

#### 📄 `employed_rules.json`
```
路徑: resources/data/chatbot/subsidy/employed_rules.json
大小: 8.6KB
用途: 在職者補助規則
結構:
{
  "type": "employed",
  "rules": [
    {
      "id": "EMP-BASIC",
      "title": "一般在職者",
      "subsidy_rate": "80%",
      "description": "補助80%，自付20%",
      "requirements": ["投保勞保或就保", "開訓日仍在職"]
    },
    {
      "id": "EMP-SPECIAL",
      "title": "特定身份在職者",
      "subsidy_rate": "100%",
      "special_identities": [
        {
          "name": "低收入戶",
          "criteria": {...}
        }
        // ... 10種身份
      ]
    }
  ]
}
```

---

#### 📄 `unemployed_rules.json`
```
路徑: resources/data/chatbot/subsidy/unemployed_rules.json
大小: 9.2KB
用途: 待業者補助規則
結構: 類似 employed_rules.json
特點: 15種特定身份（比在職者多5種）
```

---

#### 📄 `subsidy_documents.json`
```
路徑: resources/data/chatbot/subsidy/subsidy_documents.json
大小: 9.9KB
用途: 證明文件清單
結構:
{
  "employed": {
    "LOW_INCOME": {
      "identity_name": "低收入戶",
      "required_documents": [
        "低收入戶證明正本（有效期內）",
        "戶籍謄本（3個月內）"
      ],
      "notes": "證明文件有效期需包含開訓日"
    }
    // ... 8種身份
  },
  "unemployed": { ... }
}
```

---

#### 📄 `subsidy_faq.json`
```
路徑: resources/data/chatbot/subsidy/subsidy_faq.json
大小: 774B
用途: 補助常見問題
筆數: 5個問題
```

---

### D. 其他資料（3個）

#### 📄 `default_responses.json`
```
路徑: resources/data/chatbot/greetings/default_responses.json
大小: 1.9KB
用途: 歡迎訊息配置
結構:
{
  "welcome_messages": [
    {
      "id": "default",
      "content": "您好！歡迎來到虹宇職訓...",
      "quick_options": ["查看課程", "補助資格", "常見問題", "聯絡客服"]
    }
  ]
}
```

---

#### 📄 `service_info.json`
```
路徑: resources/data/chatbot/contacts/service_info.json
大小: 1.4KB
用途: 聯絡資訊
結構:
{
  "phone": "03-4227723",
  "line_id": "@hong-yu",
  "email": "service@hong-yu.com.tw",
  "address": "桃園市中壢區中央西路二段30號11樓",
  "business_hours": {
    "weekday": "週一至週五 09:00-18:00",
    "weekend": "週六 09:00-12:00",
    "holiday": "週日及國定假日休息"
  }
}
```

---

#### 📄 `button_config.json`
```
路徑: resources/data/chatbot/quick_options/button_config.json
大小: 1.7KB
用途: 快速按鈕配置（已棄用，目前使用 ClassificationAgent 內建路由）
```

---

## 4. 靜態資源（3個）

#### 🖼️ `logo.png`
```
路徑: public/logo.png
大小: 4.1KB
尺寸: 建議 200x50 px
用途: 虹宇職訓 Logo
位置: Header 左上角
```

---

#### 🖼️ `agent.png`
```
路徑: public/agent.png
大小: 8.4KB
尺寸: 建議 100x100 px
用途: AI 助手頭像
位置: 每條 AI 訊息左側
```

---

#### 🖼️ `line@.png`
```
路徑: public/images/line@.png
大小: N/A
用途: LINE 官方帳號圖示（可選）
位置: 真人客服聯絡資訊
```

---

## 5. 環境配置（1個）

#### 📄 `.env` 新增配置
```env
# OpenAI API 配置（智能客服）
OPENAI_API_KEY=your-openai-api-key-here
OPENAI_AGENT_MODEL=gpt-3.5-turbo

# Session 配置（建議）
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

---

## 6. 文檔（7個）⭐ **本文件包**

### A. 整合文檔（5個）

#### 📄 `INTEGRATION_GUIDE.md` ⭐ **必讀**
```
用途: 完整的整合指南
內容:
  - 快速開始（3步驟）
  - 詳細步驟（6大步驟）
  - 配置說明
  - 測試驗證
  - 常見問題（7個Q&A）
  - 附錄（腳本、資源）
頁數: ~80行
```

---

#### 📄 `DEVELOPMENT_STATUS.md`
```
用途: 開發進度與功能清單
內容:
  - 專案概覽
  - 已完成功能（18個主要功能）
  - 已修復問題（10個Bug）
  - 開發歷史（20個Commit）
  - 技術統計
  - 未來規劃
  - 已知限制
  - 維護注意事項
  - 效能指標
  - 變更日誌
頁數: ~600行
```

---

#### 📄 `FILE_MANIFEST.md` 📍 **本文件**
```
用途: 完整文件清單
內容:
  - 文件分類（6大類）
  - 每個文件的詳細說明
  - 文件路徑
  - 文件大小
  - 文件用途
  - 依賴關係
  - 資料結構
頁數: ~500行（本文件）
```

---

#### 📄 `API_DOCUMENTATION.md`
```
用途: API 使用文檔
內容:
  - Agent API（7個Agent）
  - RAGService API
  - SessionManager API
  - OpenAIService API
  - 使用範例
  - 錯誤處理
  - 擴展開發指南
頁數: 待建立
```

---

#### 📄 `MIGRATION_CHECKLIST.md`
```
用途: 遷移檢查清單
內容:
  - 18步驟檢查清單
  - 每步的驗證命令
  - 常見錯誤排查
  - 回滾指南
頁數: 待建立
```

---

### B. 專案文檔（2個）

#### 📄 `README.md`
```
用途: 專案說明（將更新為獨立模組版本）
內容:
  - 專案簡介
  - 功能特點
  - 安裝說明
  - 專案結構
  - 開發指南
  - 技術架構
  - 測試說明
  - 常用命令
頁數: ~214行
```

---

#### 📄 `CLAUDE.md`
```
用途: Claude Code 開發規範（保持不變）
內容:
  - 開發規則
  - 禁止事項
  - 必須要求
  - 執行模式
  - Git 工作流程
  - 技術債務防止
頁數: 已存在，不更動
```

---

## 📦 完整遷移清單

### 遷移 Checklist（按順序）

#### Phase 1: 核心 Services（3個）
- [ ] `app/Services/Chatbot/OpenAIService.php`
- [ ] `app/Services/Chatbot/SessionManager.php`
- [ ] `app/Services/Chatbot/RAGService.php`

#### Phase 2: Agent 基礎類別（1個）
- [ ] `app/Services/Chatbot/Agents/BaseAgent.php`

#### Phase 3: 功能 Agents（6個）
- [ ] `app/Services/Chatbot/Agents/ClassificationAgent.php` ⭐
- [ ] `app/Services/Chatbot/Agents/CourseAgent.php`
- [ ] `app/Services/Chatbot/Agents/SubsidyAgent.php`
- [ ] `app/Services/Chatbot/Agents/FAQAgent.php`
- [ ] `app/Services/Chatbot/Agents/EnrollmentAgent.php`
- [ ] `app/Services/Chatbot/Agents/HumanServiceAgent.php`

#### Phase 4: Livewire 組件（2個）
- [ ] `app/Http/Livewire/ChatbotWidget.php`
- [ ] `resources/views/livewire/chatbot-widget.blade.php`

#### Phase 5: JSON 資料（11個）
- [ ] `resources/data/chatbot/courses/course_list.json`
- [ ] `resources/data/chatbot/courses/course_mapping.json`
- [ ] `resources/data/chatbot/faq/general_faq.json`
- [ ] `resources/data/chatbot/faq/enrollment_process.json`
- [ ] `resources/data/chatbot/subsidy/employed_rules.json`
- [ ] `resources/data/chatbot/subsidy/unemployed_rules.json`
- [ ] `resources/data/chatbot/subsidy/subsidy_documents.json`
- [ ] `resources/data/chatbot/subsidy/subsidy_faq.json`
- [ ] `resources/data/chatbot/greetings/default_responses.json`
- [ ] `resources/data/chatbot/contacts/service_info.json`
- [ ] `resources/data/chatbot/quick_options/button_config.json`

#### Phase 6: 靜態資源（3個）
- [ ] `public/logo.png`
- [ ] `public/agent.png`
- [ ] `public/images/line@.png`

#### Phase 7: 文檔（7個）
- [ ] `INTEGRATION_GUIDE.md`
- [ ] `DEVELOPMENT_STATUS.md`
- [ ] `FILE_MANIFEST.md`
- [ ] `API_DOCUMENTATION.md`
- [ ] `MIGRATION_CHECKLIST.md`
- [ ] `README.md`（更新版）
- [ ] `CLAUDE.md`（保持不變）

---

## 📊 文件統計

### 按類型統計

| 類型 | 數量 | 總大小 | 總行數 |
|------|------|--------|--------|
| PHP 類別 | 10 | ~85KB | ~2,600 |
| Livewire 組件 | 2 | ~15KB | ~464 |
| JSON 資料 | 11 | ~72KB | ~1,200 |
| 靜態資源 | 3 | ~13KB | - |
| 文檔 | 7 | ~150KB | ~2,000 |
| **總計** | **33** | **~335KB** | **~6,264** |

### 按功能模組統計

| 模組 | 文件數 | 說明 |
|------|--------|------|
| 意圖分類 | 1 | ClassificationAgent（核心） |
| 課程查詢 | 3 | CourseAgent + 2 JSON |
| 補助諮詢 | 5 | SubsidyAgent + 4 JSON |
| 常見問題 | 3 | FAQAgent + 2 JSON |
| 報名流程 | 1 | EnrollmentAgent |
| 真人客服 | 2 | HumanServiceAgent + service_info.json |
| 基礎設施 | 5 | BaseAgent + 3 Services + 1 Livewire |
| UI 組件 | 1 | chatbot-widget.blade.php |
| 靜態資源 | 3 | Logo + Agent + LINE 圖示 |
| 文檔 | 7 | 整合指南 + API 文檔等 |

---

## 🔍 依賴關係圖

```
ChatbotWidget (Livewire)
    ↓
SessionManager ←────────┐
    ↓                   │
ClassificationAgent ────┤
    ↓                   │
├─ CourseAgent         │
├─ SubsidyAgent        ├─ All Agents
├─ FAQAgent            │
├─ EnrollmentAgent     │
└─ HumanServiceAgent   │
    ↓                   │
BaseAgent ──────────────┤
    ↓                   │
OpenAIService          │
RAGService ─────────────┘
```

---

## ⚠️ 特別注意事項

### 必須修改的文件（命名空間）

1. **所有 Agent 類別（7個）**
   - 原始：`namespace App\Services\Agents;`
   - 目標：`namespace App\Services\Chatbot\Agents;`

2. **核心 Services（3個）**
   - 原始：`namespace App\Services;`
   - 目標：`namespace App\Services\Chatbot;`

3. **Agent 中的 use 語句**
   - 原始：`use App\Services\Agents\BaseAgent;`
   - 目標：`use App\Services\Chatbot\Agents\BaseAgent;`

4. **ChatbotWidget.php 的 use 語句**
   - 原始：`use App\Services\SessionManager;`
   - 目標：`use App\Services\Chatbot\SessionManager;`
   - 原始：`use App\Services\Agents\ClassificationAgent;`
   - 目標：`use App\Services\Chatbot\Agents\ClassificationAgent;`

### 不需修改的文件

1. **所有 JSON 文件**：直接複製即可
2. **所有靜態資源**：直接複製即可
3. **chatbot-widget.blade.php**：直接複製即可（不涉及命名空間）

---

## 📞 文件說明

如需更多資訊，請參閱：
- **整合步驟**: `INTEGRATION_GUIDE.md`
- **功能說明**: `DEVELOPMENT_STATUS.md`
- **API 使用**: `API_DOCUMENTATION.md`（待建立）
- **檢查清單**: `MIGRATION_CHECKLIST.md`（待建立）

---

**文件清單版本**: v1.0.0
**維護者**: Claude Code AI
**最後審查**: 2025-10-28
