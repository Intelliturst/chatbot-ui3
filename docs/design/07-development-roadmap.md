# 虹宇職訓智能客服系統 - 開發階段計劃

**文件版本**: 1.0
**最後更新**: 2025-10-24
**作者**: 虹宇職訓開發團隊

---

## 📋 專案概覽

### 總體時程

- **總開發時間**：8週（約2個月）
- **開發階段**：8個Phase
- **測試階段**：每個Phase結束時進行測試
- **部署策略**：分階段部署，先JSON後API

---

## Phase 1: 基礎框架建置（Week 1）

### 目標

建立專案基礎架構，完成彈出式視窗UI

### 任務清單

#### 1.1 Laravel專案設定

- [ ] 建立Laravel 8專案
- [ ] 安裝Livewire 2.x
- [ ] 安裝Tailwind CSS 3.x
- [ ] 設定專案目錄結構
- [ ] 配置.env環境變數

#### 1.2 Livewire組件開發

- [ ] 創建ChatbotWidget主組件
- [ ] 實現浮動按鈕（未展開狀態）
- [ ] 實現彈出式視窗（展開狀態）
- [ ] 實現展開/收合動畫

#### 1.3 UI實現

- [ ] 實現頂部欄（Logo + 品牌名 + 功能按鈕）
- [ ] 實現訊息顯示區
- [ ] 實現輸入區（Textarea + 發送按鈕）
- [ ] 實現訊息氣泡樣式（用戶/AI）

#### 1.4 RWD響應式設計

- [ ] Desktop版本（400px寬，固定右下角）
- [ ] Mobile版本（全螢幕展開）
- [ ] Tablet版本（350px寬）

### 交付成果

- ✅ 可展開/收合的彈出式視窗
- ✅ 基本訊息顯示功能
- ✅ RWD支援（Desktop/Mobile）

### 測試重點

- [ ] 視窗展開/收合動畫流暢
- [ ] 不同裝置尺寸顯示正常
- [ ] 輸入框功能正常

---

## Phase 2: Session管理與對話記憶（Week 1-2）

### 目標

實現對話記憶功能，支援上下文理解

### 任務清單

#### 2.1 SessionManager實現

- [ ] 創建SessionManager服務
- [ ] 實現Session初始化
- [ ] 實現addMessage方法
- [ ] 實現getHistory方法
- [ ] 實現Context管理（setContext/getContext）

#### 2.2 對話歷史顯示

- [ ] 載入歷史訊息
- [ ] 顯示時間戳
- [ ] 實現滾動到底部功能

#### 2.3 載入動畫

- [ ] 實現打字效果載入動畫
- [ ] 實現Loading狀態顯示

### 交付成果

- ✅ 對話記憶功能
- ✅ 歷史訊息載入
- ✅ 上下文管理

### 測試重點

- [ ] Session正常初始化
- [ ] 對話記憶正確保存
- [ ] 刷新頁面後歷史訊息正常載入

---

## Phase 3: 分類總代理與OpenAI整合（Week 2）

### 目標

實現分類總代理，整合OpenAI API

### 任務清單

#### 3.1 OpenAIService實現

- [ ] 創建OpenAIService服務
- [ ] 實現chat方法
- [ ] 配置API Key
- [ ] 實現錯誤處理

#### 3.2 BaseAgent基礎類別

- [ ] 創建BaseAgent抽象類別
- [ ] 實現generateResponse方法
- [ ] 實現getSystemPrompt方法

#### 3.3 ClassificationAgent實現

- [ ] 實現分類邏輯
- [ ] 實現9個分類的判斷
- [ ] 實現路由分發機制
- [ ] 實現0/9直接RAG回覆

#### 3.4 打招呼/未知分類處理

- [ ] 創建greetings JSON資料
- [ ] 實現直接RAG回覆邏輯

### 交付成果

- ✅ OpenAI API整合
- ✅ 分類總代理運作
- ✅ 基本對話功能

### 測試重點

- [ ] OpenAI API調用正常
- [ ] 分類準確度測試
- [ ] 打招呼正常回覆

---

## Phase 4: JSON知識庫建置（Week 3）

### 目標

建立完整的JSON知識庫

### 任務清單

#### 4.1 課程資料JSON

- [ ] 整理20門課程資料
- [ ] 創建course_list.json
- [ ] 創建course_mapping.json
- [ ] 準備課程圖片（或預設圖）

#### 4.2 補助規則JSON

- [ ] 創建employed_rules.json
- [ ] 創建unemployed_rules.json
- [ ] 創建subsidy_faq.json

#### 4.3 常見問題JSON

- [ ] 創建general_faq.json
- [ ] 創建enrollment_process.json
- [ ] 創建related_questions.json

#### 4.4 聯絡資訊與快速選項JSON

- [ ] 創建service_info.json
- [ ] 創建button_config.json
- [ ] 創建default_responses.json

#### 4.5 RAGService實現

- [ ] 創建RAGService服務
- [ ] 實現queryCourses方法
- [ ] 實現searchFAQ方法
- [ ] 實現getSubsidyRules方法

### 交付成果

- ✅ 完整JSON知識庫
- ✅ RAGService運作正常

### 測試重點

- [ ] JSON格式驗證
- [ ] RAG查詢功能正常
- [ ] 關鍵字搜尋準確

---

## Phase 5: 專業代理開發（Week 3-4）

### 目標

實現5個專業代理

### 任務清單

#### 5.1 CourseAgent（課程內容代理）

- [ ] 實現課程清單顯示
- [ ] 實現課程編號選擇
- [ ] 實現單一問題回答
- [ ] 實現課程卡片組件
- [ ] 實現關聯問題生成

#### 5.2 SubsidyAgent（補助判斷代理）

- [ ] 實現決策樹邏輯
- [ ] 實現在職/待業判斷
- [ ] 實現特殊身份詢問
- [ ] 實現補助結果生成
- [ ] 實現選項按鈕

#### 5.3 FAQAgent（常見問題代理）

- [ ] 實現FAQ搜尋
- [ ] 實現關聯問題緩存
- [ ] 實現從緩存回答邏輯

#### 5.4 HumanServiceAgent（真人客服代理）

- [ ] 實現需求理解邏輯
- [ ] 實現通知訊息生成
- [ ] 實現客服通知API調用

#### 5.5 EnrollmentAgent & FeaturedAgent

- [ ] 實現報名流程說明
- [ ] 實現精選課程查詢

### 交付成果

- ✅ 5個專業代理運作正常
- ✅ 完整的對話流程

### 測試重點

- [ ] 每個代理獨立測試
- [ ] 完整對話流程測試
- [ ] 分類準確度測試

---

## Phase 6: UI/UX優化（Week 5）

### 目標

優化用戶體驗，實現所有UI組件

### 任務清單

#### 6.1 課程卡片優化

- [ ] 實現Carousel樣式（如有多個課程）
- [ ] 實現課程圖片顯示
- [ ] 實現點擊展開詳情

#### 6.2 快速選項按鈕

- [ ] 實現水平滾動按鈕群
- [ ] 實現按鈕點擊效果
- [ ] 實現動態快速選項

#### 6.3 關聯問題按鈕

- [ ] 實現關聯問題樣式
- [ ] 實現點擊直接回答

#### 6.4 補助判斷選項按鈕

- [ ] 實現"在職者/待業者"按鈕
- [ ] 實現"符合/不符合"按鈕

#### 6.5 動畫效果優化

- [ ] 訊息逐條顯示動畫
- [ ] 按鈕hover效果
- [ ] 視窗展開動畫優化

### 交付成果

- ✅ 完整的UI組件庫
- ✅ 流暢的動畫效果
- ✅ 優秀的用戶體驗

### 測試重點

- [ ] 所有按鈕功能正常
- [ ] 動畫效果流暢
- [ ] 用戶操作直觀

---

## Phase 7: 外部課程 API 串接（Week 6）

### 目標

串接外部課程 API，實現 JSON/API 雙模式切換

### 任務清單

#### 7.1 CourseAPIService 實現

- [ ] 創建 CourseAPIService 服務
- [ ] 實現外部 API 調用（https://www.hongyu.goblinlab.org/api/courses）
- [ ] 實現 getCourses 方法（支援 type 篩選）
- [ ] 實現 getCourseById 方法
- [ ] 實現 searchCourses 方法
- [ ] 實現錯誤處理與重試機制

#### 7.2 JSON/API 雙模式切換

- [ ] 實現 loadFromJSON 方法（讀取 resources/data/chatbot/courses/*.json）
- [ ] 實現 fetchFromAPI 方法（調用外部 API）
- [ ] 根據 .env 配置自動切換模式
- [ ] 測試兩種模式正常運作

#### 7.3 API 響應處理

- [ ] 統一 JSON 和 API 的數據格式
- [ ] 實現數據轉換層
- [ ] 處理 API 異常情況（降級到 JSON）
- [ ] 實現 API 超時處理

#### 7.4 緩存機制

- [ ] 實現課程資料緩存（Redis/File）
- [ ] 設定緩存過期時間
- [ ] 實現緩存更新策略
- [ ] 測試緩存效能

#### 7.5 環境配置

- [ ] 配置 .env 變數（CHATBOT_USE_COURSE_API、CHATBOT_COURSE_API_URL）
- [ ] 配置 API 超時時間
- [ ] 配置緩存策略
- [ ] 撰寫配置文檔

### 交付成果

- ✅ 外部 Course API 串接成功
- ✅ JSON/API 雙模式切換正常
- ✅ 緩存機制運作正常
- ✅ 錯誤處理完善

### 測試重點

- [ ] 外部 API 調用測試
- [ ] JSON 模式正常運作
- [ ] 模式切換無縫銜接
- [ ] API 異常降級測試
- [ ] 緩存功能測試

---

## Phase 8: 測試與優化（Week 6-7）

### 目標

全面測試，修復Bug，性能優化

### 任務清單

#### 8.1 功能測試

- [ ] 分類準確度測試（100個測試案例）
- [ ] 課程查詢功能測試
- [ ] 補助判斷邏輯測試
- [ ] FAQ搜尋測試
- [ ] 真人客服轉接測試

#### 8.2 對話流程測試

- [ ] 完整對話流程測試（各種情境）
- [ ] 上下文理解測試
- [ ] 簡短回覆測試
- [ ] 多輪對話測試

#### 8.3 UI/UX測試

- [ ] 不同裝置測試（Desktop/Tablet/Mobile）
- [ ] 不同瀏覽器測試（Chrome/Safari/Firefox）
- [ ] 動畫效果測試
- [ ] 操作流暢度測試

#### 8.4 性能優化

- [ ] OpenAI API調用優化
- [ ] 緩存策略優化
- [ ] 載入速度優化
- [ ] 資料庫查詢優化

#### 8.5 錯誤處理

- [ ] OpenAI API錯誤處理
- [ ] 網路錯誤處理
- [ ] Session異常處理
- [ ] 降級策略測試

### 交付成果

- ✅ 全面測試報告
- ✅ Bug修復
- ✅ 性能優化

### 測試重點

- [ ] 分類準確率 > 90%
- [ ] 回應時間 < 3秒
- [ ] 無嚴重Bug
- [ ] 用戶體驗良好

---

## Phase 9: RAG 向量資料庫建置（Week 7-8，延後執行）

### 目標

建立 RAG 向量資料表，實現語義搜索，提升檢索準確度

### 任務清單（待 Phase 1-8 完成後執行）

#### 9.1 RAG 資料表設計

- [ ] 設計 `rag_documents` 表（文檔主表）
- [ ] 設計 `rag_embeddings` 表（向量嵌入表）
- [ ] 設計 `rag_metadata` 表（元數據表）
- [ ] 建立索引優化查詢
- [ ] 創建 Migration 文件

**資料表結構範例**：

```sql
-- rag_documents 文檔表
CREATE TABLE rag_documents (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  source_type VARCHAR(50) NOT NULL,  -- 'course', 'subsidy', 'faq'
  source_id VARCHAR(100),             -- 來源資料ID
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  metadata JSON,                      -- 額外元數據
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  INDEX idx_source (source_type, source_id)
);

-- rag_embeddings 向量表
CREATE TABLE rag_embeddings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  document_id BIGINT UNSIGNED NOT NULL,
  chunk_index INT NOT NULL,           -- 文檔分片索引
  chunk_text TEXT NOT NULL,           -- 分片內容
  embedding JSON NOT NULL,            -- 向量嵌入（768維度）
  created_at TIMESTAMP,
  FOREIGN KEY (document_id) REFERENCES rag_documents(id) ON DELETE CASCADE,
  INDEX idx_document (document_id)
);
```

#### 9.2 文檔向量化處理

- [ ] 實現文檔分片策略（Chunking）
- [ ] 整合 OpenAI Embeddings API（text-embedding-3-small）
- [ ] 實現批次向量化處理
- [ ] 實現向量存儲邏輯
- [ ] 處理長文本分片

#### 9.3 知識庫資料匯入

- [ ] 創建匯入指令（ImportRAGDocumentsCommand）
- [ ] 匯入課程資料（從 API 或 JSON）
- [ ] 匯入補助政策
- [ ] 匯入常見問題
- [ ] 驗證資料完整性

#### 9.4 語義搜索實現

- [ ] 實現查詢向量化
- [ ] 實現餘弦相似度計算
- [ ] 實現 Top-K 相似文檔檢索
- [ ] 實現混合檢索（關鍵字 + 語義）
- [ ] 優化檢索性能

#### 9.5 RAGService 升級

- [ ] 創建 VectorRAGService
- [ ] 實現 semanticSearch 方法
- [ ] 實現 hybridSearch 方法（關鍵字 + 向量）
- [ ] 實現相關性排序
- [ ] 實現緩存策略

#### 9.6 性能優化與測試

- [ ] 向量索引優化（考慮使用 Milvus 或 Pinecone）
- [ ] 批次查詢優化
- [ ] 緩存機制優化
- [ ] 效能基準測試（vs JSON 模式）
- [ ] 準確度評估

### 交付成果

- ✅ RAG 向量資料表建立完成
- ✅ 知識庫文檔向量化完成
- ✅ 語義搜索功能運作正常
- ✅ 檢索準確度提升 20%+

### 測試重點

- [ ] 向量化處理正確性
- [ ] 語義搜索準確度測試
- [ ] 混合檢索效果測試
- [ ] 查詢性能測試（< 500ms）
- [ ] 與 JSON 模式對比測試

### 技術選型

**向量嵌入模型**：
- OpenAI `text-embedding-3-small`（1536 維度，成本較低）
- OpenAI `text-embedding-3-large`（3072 維度，更高精度）

**向量資料庫選項**：
1. **MySQL + JSON**（Phase 9 初期）：向量存為 JSON 欄位
2. **PostgreSQL + pgvector**（推薦）：原生向量支援
3. **Milvus / Pinecone**（高級選項）：專業向量資料庫

### 成本評估

**OpenAI Embeddings API 成本**：
- text-embedding-3-small: $0.00002 / 1K tokens
- 假設 20 門課程 + 50 條 FAQ，約 100K tokens
- 初次向量化成本：約 $2 USD
- 查詢成本可忽略不計（僅對用戶問題向量化）

---

## 測試策略

### 單元測試

```bash
# 運行所有測試
php artisan test

# 測試特定代理
php artisan test --filter=CourseAgentTest
```

**測試範圍**：
- 每個Agent的handle方法
- RAGService的查詢方法
- CourseAPIService的API調用
- SessionManager的Session管理

### 整合測試

**測試場景**：
1. 用戶查詢待業課程 → 列出課程清單 → 選擇課程 → 詢問報名截止時間
2. 用戶詢問補助 → 判斷在職 → 詢問特殊身份 → 返回結果
3. 用戶打招呼 → 返回歡迎訊息 → 提供快速選項

### 用戶驗收測試（UAT）

**測試人員**：虹宇職訓內部人員
**測試時間**：Week 7
**測試內容**：
- 實際業務場景測試
- 回答準確性評估
- 用戶體驗評估

---

## 部署策略

### 開發環境（Dev）

```bash
git clone <repo>
composer install
npm install
cp .env.example .env
php artisan key:generate
npm run dev
php artisan serve
```

### 測試環境（Staging）

**部署方式**：自動化部署（GitHub Actions）
**測試資料**：使用測試課程資料

### 正式環境（Production）

**部署步驟**：
1. 備份現有資料
2. 拉取最新程式碼
3. 安裝依賴
4. 更新知識庫 JSON 文件
5. 編譯前端資源
6. 清除緩存
7. 重啟服務

**部署腳本**：

```bash
#!/bin/bash
# deploy.sh

echo "🚀 開始部署..."

# 1. 拉取程式碼
git pull origin main

# 2. 安裝依賴
composer install --no-dev --optimize-autoloader
npm install --production

# 3. 編譯前端資源
npm run build

# 4. 清除緩存
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. 重啟服務（根據環境調整）
php artisan queue:restart

echo "✅ 部署完成！"
```

---

## 維護計劃

### 日常維護

**頻率**：每週

- 監控OpenAI API使用量
- 檢查錯誤日誌
- 更新課程資料（如有新課程）
- 備份資料庫

### 內容更新

**課程資料更新**（JSON 模式）：
```bash
# 更新 JSON 文件
# 編輯 resources/data/chatbot/courses/*.json
git add resources/data/chatbot/courses/
git commit -m "Update course data"
git push
# 部署後清除緩存
php artisan cache:clear
```

**課程資料更新**（API 模式）：
- 課程資料由外部 API 提供，無需本地更新
- 緩存會自動過期更新

**補助規則更新**：
```bash
# 修改 JSON 文件
vim resources/data/chatbot/subsidy/unemployed.json
git add resources/data/chatbot/subsidy/
git commit -m "Update subsidy rules"
git push
# 部署後清除緩存
php artisan cache:clear
```

### 性能監控

**監控指標**：
- API回應時間
- OpenAI API成本
- 錯誤率
- 用戶滿意度

**工具**：
- Laravel Telescope（開發環境）
- 日誌分析
- Google Analytics（前端）

---

## 里程碑與檢查點

| Phase | 時間 | 檢查點 | 狀態 |
|-------|------|--------|------|
| Phase 1 | Week 1 | 彈出式視窗完成 | ⏳ |
| Phase 2 | Week 1-2 | 對話記憶完成 | ⏳ |
| Phase 3 | Week 2 | OpenAI整合完成 | ⏳ |
| Phase 4 | Week 3 | JSON知識庫完成 | ⏳ |
| Phase 5 | Week 3-4 | 專業代理完成 | ⏳ |
| Phase 6 | Week 5 | UI優化完成 | ⏳ |
| Phase 7 | Week 6 | 外部API串接完成 | ⏳ |
| Phase 8 | Week 6-7 | 測試與優化完成 | ⏳ |
| Phase 9 | Week 7-8 | RAG向量資料庫完成 | 延後 |

---

## 風險管理

### 已識別風險

1. **OpenAI API 成本超支**
   - 緩解措施：實施緩存、限制調用頻率、使用較便宜的模型分類

2. **分類準確度不足**
   - 緩解措施：優化 Prompt、增加測試案例、持續調整

3. **外部課程 API 不穩定或失效**
   - 緩解措施：實施 JSON/API 雙模式切換、API 異常自動降級到 JSON、設定 API 超時與重試機制

4. **資料更新不及時**
   - 緩解措施：API 模式自動取得最新資料、JSON 模式透過 Git 版本控管

5. **性能問題**
   - 緩解措施：實施緩存（課程資料緩存 1 小時）、API 調用優化、CDN 加速

6. **RAG 向量化成本**
   - 緩解措施：選用成本較低的 text-embedding-3-small 模型、批次處理降低調用次數

---

## 附錄

### 相關文件

- [01-system-architecture.md](./01-system-architecture.md) - 系統架構設計
- [02-knowledge-base-structure.md](./02-knowledge-base-structure.md) - JSON知識庫設計
- [03-agent-implementation.md](./03-agent-implementation.md) - 代理實現規範
- [04-frontend-ui-specification.md](./04-frontend-ui-specification.md) - 前端UI設計規範
- [05-course-api-integration.md](./05-course-api-integration.md) - Course API對接設計
- [06-laravel-development-guide.md](./06-laravel-development-guide.md) - Laravel開發規範

---

**文件結束**

**專案啟動準備清單**：
- [ ] 確認所有開發文件已閱讀
- [ ] 設定開發環境
- [ ] 取得OpenAI API Key
- [ ] 準備課程資料
- [ ] 組建開發團隊
- [ ] 啟動Phase 1開發
