# 虹宇智能客服系統 - API 文檔

> **版本**: v1.0.0
> **最後更新**: 2025-10-28
> **適用對象**: 開發者、擴展開發

## 📋 目錄

- [Agent API](#agent-api)
- [RAGService API](#ragservice-api)
- [SessionManager API](#sessionmanager-api)
- [OpenAIService API](#openaiservice-api)
- [使用範例](#使用範例)
- [擴展開發指南](#擴展開發指南)

---

## Agent API

### BaseAgent

所有 Agent 的基礎類別，提供共用功能。

#### 構造函數

```php
public function __construct($openAI, $session, RAGService $ragService)
```

**參數**:
- `$openAI`: OpenAIService 實例
- `$session`: SessionManager 實例
- `$ragService`: RAGService 實例

#### 方法

##### `handle($userMessage)` (抽象)

```php
abstract public function handle($userMessage);
```

處理用戶訊息，每個子類別必須實現。

**參數**:
- `$userMessage` (string): 用戶輸入的訊息

**返回**:
```php
[
    'content' => '回應內容',
    'quick_options' => ['選項1', '選項2']  // 可選
]
```

---

### ClassificationAgent ⭐ **核心**

意圖分類與路由核心。

#### 方法

##### `handle($userMessage)`

```php
public function handle($userMessage)
```

處理用戶訊息，進行意圖分類並路由到對應 Agent。

**優先級系統** (9級):
```
0: 快速按鈕 (直接路由)
1: 純數字 (上下文感知)
2.4: 補助上下文
2.5: 課程上下文
2.6: 課程查詢模式
3: OpenAI GPT 分類
4: 模糊關鍵字匹配
5: 用戶引導
```

**範例**:

```php
$classification = app(\App\Services\Chatbot\Agents\ClassificationAgent::class);

// 快速按鈕 (優先級 0)
$result = $classification->handle('查看課程');
// 返回: CourseAgent 處理結果

// 純數字 + 上下文 (優先級 1)
// 假設上一次操作是 course_list
$result = $classification->handle('2');
// 返回: 第2個課程的詳情

// 課程查詢 (優先級 2.6)
$result = $classification->handle('AI課程有哪些');
// 返回: 課程列表

// OpenAI 分類 (優先級 3)
$result = $classification->handle('我想報名但不確定選什麼');
// 返回: OpenAI 分析後路由到對應 Agent

// 未知查詢 (優先級 5)
$result = $classification->handle('asdf1234');
// 返回: 用戶引導訊息
```

##### `preprocessUserInput($message)`

```php
protected function preprocessUserInput($message)
```

預處理用戶輸入（移除多餘空白、禮貌用語）。

**範例**:
```php
$input = "   請問  AI課程   有哪些呢？   ";
$processed = $this->preprocessUserInput($input);
// 結果: "AI課程有哪些"
```

##### `fuzzyKeywordMatch($message)`

```php
protected function fuzzyKeywordMatch($message)
```

模糊關鍵字匹配（評分系統）。

**返回**:
```php
'course' | 'subsidy' | 'faq' | 'enrollment' | 'contact' | null
```

**評分規則**:
- 每個匹配的關鍵字 +2分
- 每個排除關鍵字 -3分
- 最低門檻：2分

**範例**:
```php
$match = $this->fuzzyKeywordMatch('我想學AI');
// 返回: 'course' (匹配到 '學', 'AI')
```

##### `detectCourseQueryPattern($message)`

```php
protected function detectCourseQueryPattern($message)
```

檢測課程查詢模式（5種正則模式）。

**支援的模式**:
1. "XX課程有哪些"
2. "有什麼XX課程"
3. "XX課程" (精確匹配)
4. "我想學XX"
5. "課程 + 查詢動詞"

**範例**:
```php
$isMatch = $this->detectCourseQueryPattern('多媒體設計課程');
// 返回: true
```

---

### CourseAgent

課程查詢處理。

#### 方法

##### `handle($userMessage)`

```php
public function handle($userMessage)
```

處理課程相關查詢。

**支援的查詢類型**:
- 列表查詢："AI課程有哪些"
- 編號查詢："1" 或 "第2個"
- 分頁查詢："更多課程"

**範例**:
```php
$course = app(\App\Services\Chatbot\Agents\CourseAgent::class);

// 列表查詢
$result = $course->handle('AI課程有哪些');
// 返回: 5個課程 + 編號快速按鈕(1-5) + "更多課程"按鈕

// 編號查詢
$result = $course->handle('1');
// 返回: 第1個課程的詳情

// 分頁查詢
$result = $course->handle('更多課程');
// 返回: 下一頁5個課程
```

##### `queryCourses($filters)`

```php
protected function queryCourses($filters = [])
```

查詢課程列表。

**參數**:
```php
[
    'type' => 'unemployed' | 'employed',  // 可選
    'keyword' => 'AI',                    // 可選
    'featured' => true                    // 可選
]
```

**範例**:
```php
// 查詢所有待業課程
$courses = $this->queryCourses(['type' => 'unemployed']);

// 查詢 AI 相關課程
$courses = $this->queryCourses(['keyword' => 'AI']);

// 查詢精選課程
$courses = $this->queryCourses(['featured' => true]);
```

##### `getCourseDetail($identifier)`

```php
protected function getCourseDetail($identifier)
```

獲取課程詳情。

**參數**:
- `$identifier` (string): 課程ID或關鍵字

**範例**:
```php
$course = $this->getCourseDetail('AI-001');
// 返回: AI-001 課程的完整資訊

$course = $this->getCourseDetail('ChatGPT');
// 返回: 第一個匹配 ChatGPT 的課程
```

---

### SubsidyAgent

補助資格諮詢。

#### 方法

##### `handle($userMessage)`

```php
public function handle($userMessage)
```

處理補助相關查詢。

**流程**:
1. 檢查 Session 中的雇用狀態
2. 如果沒有，嘗試從訊息中檢測
3. 如果還是沒有，詢問用戶
4. 根據狀態提供補助資訊

**範例**:
```php
$subsidy = app(\App\Services\Chatbot\Agents\SubsidyAgent::class);

// 第一次查詢（無狀態）
$result = $subsidy->handle('補助資格');
// 返回: 詢問就業狀況 + 快速按鈕(在職/待業)

// 有狀態的查詢
$result = $subsidy->handle('我是原住民需要什麼文件');
// 返回: 原住民身份的證明文件清單
```

##### `detectEmploymentStatus($message)`

```php
protected function detectEmploymentStatus($message)
```

檢測雇用狀態。

**返回**: `'employed'` | `'unemployed'` | `null`

**範例**:
```php
$status = $this->detectEmploymentStatus('我是在職者');
// 返回: 'employed'

$status = $this->detectEmploymentStatus('目前待業中');
// 返回: 'unemployed'

$status = $this->detectEmploymentStatus('你好');
// 返回: null
```

---

### FAQAgent

常見問題處理。

#### 方法

##### `handle($userMessage)`

```php
public function handle($userMessage)
```

處理 FAQ 查詢。

**範例**:
```php
$faq = app(\App\Services\Chatbot\Agents\FAQAgent::class);

$result = $faq->handle('常見問題');
// 返回: FAQ 列表 + 快速按鈕(問題1-5) + "更多問題"

$result = $faq->handle('課程費用多少');
// 返回: 費用相關的 FAQ 回答
```

---

### EnrollmentAgent

報名流程引導。

#### 方法

##### `handle($userMessage)`

```php
public function handle($userMessage)
```

顯示報名流程。

**範例**:
```php
$enrollment = app(\App\Services\Chatbot\Agents\EnrollmentAgent::class);

$result = $enrollment->handle('如何報名');
// 返回: 5步驟報名流程 + 線上報名連結
```

---

### HumanServiceAgent

真人客服聯絡。

#### 方法

##### `handle($userMessage)`

```php
public function handle($userMessage)
```

提供真人客服聯絡方式。

**設備檢測**:
- Mobile: 顯示可直接執行的按鈕（撥打電話、開啟LINE）
- Desktop: 顯示複製按鈕

**範例**:
```php
$human = app(\App\Services\Chatbot\Agents\HumanServiceAgent::class);

$result = $human->handle('聯絡客服');
// 返回: 聯絡資訊 + 設備適配的快速按鈕
```

---

## RAGService API

資料查詢服務（JSON 文件載入與查詢）。

### 方法

#### `queryCourses($filters)`

查詢課程列表。

```php
public function queryCourses($filters = [])
```

**參數**:
```php
[
    'type' => 'unemployed' | 'employed',
    'keyword' => 'AI',
    'featured' => true
]
```

**返回**: 課程陣列（已排序）

**範例**:
```php
$rag = app(\App\Services\Chatbot\RAGService::class);

// 查詢所有課程
$all = $rag->queryCourses();

// 查詢待業課程
$unemployed = $rag->queryCourses(['type' => 'unemployed']);

// 搜尋 AI 課程
$aiCourses = $rag->queryCourses(['keyword' => 'AI']);

// 精選課程
$featured = $rag->queryCourses(['featured' => true]);
```

---

#### `getCourseDetail($identifier)`

獲取課程詳情。

```php
public function getCourseDetail($identifier)
```

**參數**:
- `$identifier` (string): 課程ID或關鍵字

**返回**: 課程物件或 `null`

**範例**:
```php
// 根據 ID
$course = $rag->getCourseDetail('AI-001');

// 根據關鍵字
$course = $rag->getCourseDetail('ChatGPT');
```

---

#### `getSubsidyRules($employmentStatus)`

獲取補助規則。

```php
public function getSubsidyRules($employmentStatus)
```

**參數**:
- `$employmentStatus`: `'employed'` | `'unemployed'`

**返回**:
```php
[
    'type' => 'employed',
    'rules' => [...]
]
```

**範例**:
```php
$rules = $rag->getSubsidyRules('employed');
// 返回: 在職者補助規則（基本80% + 特定身份100%）
```

---

#### `getSubsidyDocuments($employmentStatus)`

獲取證明文件清單。

```php
public function getSubsidyDocuments($employmentStatus)
```

**返回**: 證明文件陣列

**範例**:
```php
$docs = $rag->getSubsidyDocuments('employed');
// 返回: 在職者各身份的證明文件清單
```

---

#### `getFAQs()`

獲取所有 FAQ。

```php
public function getFAQs()
```

**返回**: FAQ 陣列

**範例**:
```php
$faqs = $rag->getFAQs();
// 返回: 24個 FAQ
```

---

#### `getEnrollmentProcess()`

獲取報名流程。

```php
public function getEnrollmentProcess()
```

**返回**: 報名流程物件

**範例**:
```php
$process = $rag->getEnrollmentProcess();
// 返回: 5步驟報名流程
```

---

#### `getServiceInfo()`

獲取聯絡資訊。

```php
public function getServiceInfo()
```

**返回**: 聯絡資訊物件

**範例**:
```php
$info = $rag->getServiceInfo();
/*
返回:
[
    'phone' => '03-4227723',
    'line_id' => '@hong-yu',
    'email' => '...',
    'address' => '...',
    'business_hours' => [...]
]
*/
```

---

#### `clearCache()`

清除所有緩存。

```php
public function clearCache()
```

**範例**:
```php
$rag->clearCache();
// 清除所有 JSON 文件的緩存
```

---

## SessionManager API

Session 管理（對話上下文與歷史）。

### 方法

#### `setContext($key, $value)`

設定上下文。

```php
public function setContext($key, $value)
```

**範例**:
```php
$session = app(\App\Services\Chatbot\SessionManager::class);

$session->setContext('last_action', 'course_list');
$session->setContext('last_course', 'AI-001');
$session->setContext('employment_status', 'employed');
```

---

#### `getContext($key, $default)`

獲取上下文。

```php
public function getContext($key, $default = null)
```

**範例**:
```php
$lastAction = $session->getContext('last_action');
// 返回: 'course_list' 或 null

$status = $session->getContext('employment_status', 'unknown');
// 返回: 'employed' 或 'unknown'（如果未設定）
```

---

#### `appendMessage($role, $content, $quickOptions)`

添加訊息到對話歷史。

```php
public function appendMessage($role, $content, $quickOptions = [])
```

**參數**:
- `$role`: `'user'` | `'assistant'`
- `$content` (string): 訊息內容
- `$quickOptions` (array): 快速選項按鈕（可選）

**範例**:
```php
// 用戶訊息
$session->appendMessage('user', 'AI課程有哪些');

// AI 訊息（附快速按鈕）
$session->appendMessage('assistant', '以下是AI課程...', [
    '1', '2', '3', '更多課程'
]);
```

---

#### `getHistory()`

獲取對話歷史。

```php
public function getHistory()
```

**返回**: 訊息陣列

**範例**:
```php
$history = $session->getHistory();
/*
返回:
[
    [
        'role' => 'user',
        'content' => 'AI課程有哪些',
        'timestamp' => '14:30'
    ],
    [
        'role' => 'assistant',
        'content' => '以下是AI課程...',
        'quick_options' => ['1', '2'],
        'timestamp' => '14:30'
    ]
]
*/
```

---

#### `clearHistory()`

清除對話歷史。

```php
public function clearHistory()
```

**範例**:
```php
$session->clearHistory();
// 清除所有對話記錄和上下文
```

---

## OpenAIService API

OpenAI API 整合。

### 方法

#### `chat($messages, $model, $options)`

調用 OpenAI Chat API。

```php
public function chat($messages, $model = null, $options = [])
```

**參數**:
```php
$messages = [
    ['role' => 'system', 'content' => 'You are a helpful assistant.'],
    ['role' => 'user', 'content' => 'Hello!']
];

$model = 'gpt-3.5-turbo';  // 或 'gpt-4'

$options = [
    'temperature' => 0.7,
    'max_tokens' => 1500
];
```

**返回**:
```php
[
    'success' => true,
    'content' => 'AI的回應內容',
    'usage' => [...],
    'model' => 'gpt-3.5-turbo'
]

// 或錯誤
[
    'success' => false,
    'error' => '錯誤訊息'
]
```

**範例**:
```php
$openai = app(\App\Services\Chatbot\OpenAIService::class);

$result = $openai->chat([
    ['role' => 'system', 'content' => 'You are a course advisor.'],
    ['role' => 'user', 'content' => '推薦適合初學者的AI課程']
]);

if ($result['success']) {
    echo $result['content'];
} else {
    echo "Error: " . $result['error'];
}
```

---

## 使用範例

### 範例 1: 完整的對話流程

```php
use App\Services\Chatbot\SessionManager;
use App\Services\Chatbot\Agents\ClassificationAgent;

// 1. 初始化
$session = app(SessionManager::class);
$classification = app(ClassificationAgent::class);

// 2. 用戶輸入
$userInput = 'AI課程有哪些';
$session->appendMessage('user', $userInput);

// 3. 處理
$response = $classification->handle($userInput);

// 4. 添加 AI 回應
$session->appendMessage(
    'assistant',
    $response['content'],
    $response['quick_options'] ?? []
);

// 5. 返回給前端
return [
    'messages' => $session->getHistory()
];
```

---

### 範例 2: 自訂 Agent

建立一個新的 Agent 來處理課程評價：

```php
namespace App\Services\Chatbot\Agents;

use App\Services\RAGService;

class CourseReviewAgent extends BaseAgent
{
    /**
     * 處理課程評價相關查詢
     */
    public function handle($userMessage)
    {
        // 1. 從 RAG 獲取課程評價資料
        $reviews = $this->ragService->loadJSON('reviews/course_reviews.json');

        // 2. 根據用戶訊息搜尋相關評價
        $keyword = $this->extractKeyword($userMessage);
        $matchedReviews = $this->searchReviews($reviews, $keyword);

        // 3. 格式化回應
        $content = "以下是 {$keyword} 的課程評價：\n\n";

        foreach ($matchedReviews as $review) {
            $content .= "**學員**: {$review['student']}\n";
            $content .= "**評分**: {$review['rating']}/5\n";
            $content .= "**心得**: {$review['comment']}\n\n";
        }

        // 4. 返回結果
        return [
            'content' => $content,
            'quick_options' => ['查看更多評價', '課程詳情', '我要報名']
        ];
    }

    /**
     * 提取關鍵字
     */
    protected function extractKeyword($message)
    {
        // 實作關鍵字提取邏輯
        if (preg_match('/(AI|Python|Java|行銷)/ui', $message, $matches)) {
            return $matches[1];
        }
        return '';
    }

    /**
     * 搜尋評價
     */
    protected function searchReviews($reviews, $keyword)
    {
        return array_filter($reviews['reviews'], function($review) use ($keyword) {
            return stripos($review['course_name'], $keyword) !== false;
        });
    }

    /**
     * 系統提示詞
     */
    protected function getSystemPrompt()
    {
        return "你是課程評價助手，幫助用戶了解課程的學員反饋。";
    }
}
```

**整合到 ClassificationAgent**:

```php
// 在 ClassificationAgent::handle() 中添加
case 7: // 課程評價
    return $this->handleReview($trimmed);

// 新增方法
protected function handleReview($userMessage)
{
    $reviewAgent = new CourseReviewAgent(
        $this->openAI,
        $this->session,
        $this->ragService
    );
    return $reviewAgent->handle($userMessage);
}
```

---

### 範例 3: 擴展 RAGService

新增課程評價查詢方法：

```php
// 在 RAGService 中添加

/**
 * 獲取課程評價
 *
 * @param string $courseId 課程ID
 * @return array
 */
public function getCourseReviews($courseId)
{
    $reviews = $this->loadJSON('reviews/course_reviews.json');

    return array_filter($reviews['reviews'], function($review) use ($courseId) {
        return $review['course_id'] === $courseId;
    });
}

/**
 * 獲取課程平均評分
 *
 * @param string $courseId 課程ID
 * @return float
 */
public function getCourseAverageRating($courseId)
{
    $reviews = $this->getCourseReviews($courseId);

    if (empty($reviews)) {
        return 0;
    }

    $totalRating = array_sum(array_column($reviews, 'rating'));
    return round($totalRating / count($reviews), 1);
}
```

---

## 擴展開發指南

### 新增 Agent 的步驟

1. **建立 Agent 類別**
   ```bash
   touch app/Services/Chatbot/Agents/YourAgent.php
   ```

2. **繼承 BaseAgent**
   ```php
   class YourAgent extends BaseAgent
   {
       public function handle($userMessage)
       {
           // 實作邏輯
       }

       protected function getSystemPrompt()
       {
           return "你是...助手";
       }
   }
   ```

3. **在 ClassificationAgent 中註冊**
   ```php
   // 添加意圖類型
   case 10: // 你的新意圖
       return $this->handleYourIntent($trimmed);

   // 添加處理方法
   protected function handleYourIntent($userMessage)
   {
       $agent = new YourAgent(
           $this->openAI,
           $this->session,
           $this->ragService
       );
       return $agent->handle($userMessage);
   }
   ```

4. **準備 JSON 資料**（如果需要）
   ```bash
   mkdir -p resources/data/chatbot/your_module
   echo '{}' > resources/data/chatbot/your_module/data.json
   ```

5. **在 RAGService 中添加查詢方法**（如果需要）
   ```php
   public function getYourData()
   {
       return $this->loadJSON('your_module/data.json');
   }
   ```

---

### 最佳實踐

#### 1. 錯誤處理

```php
public function handle($userMessage)
{
    try {
        // 處理邏輯
        $data = $this->ragService->getYourData();

        if (empty($data)) {
            return $this->errorResponse();
        }

        return $this->successResponse($data);

    } catch (\Exception $e) {
        \Log::error('YourAgent error', [
            'message' => $e->getMessage(),
            'user_input' => $userMessage
        ]);

        return $this->errorResponse();
    }
}

protected function errorResponse()
{
    return [
        'content' => "抱歉，目前無法處理您的請求。\n\n如需協助，請聯絡客服：03-4227723",
        'quick_options' => ['聯絡客服', '返回主選單']
    ];
}
```

#### 2. 日誌記錄

```php
// 記錄重要操作
\Log::info('YourAgent::handle', [
    'user_input' => $userMessage,
    'data_found' => count($results)
]);

// 記錄錯誤
\Log::error('YourAgent error', [
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString()
]);
```

#### 3. 上下文管理

```php
// 設定上下文
$this->session->setContext('last_action', 'your_action');
$this->session->setContext('your_data', $data);

// 讀取上下文
$lastAction = $this->session->getContext('last_action');
if ($lastAction === 'your_action') {
    // 使用上下文處理
}
```

#### 4. 快速按鈕設計

```php
// 主要操作（1-3個）
$quickOptions = ['查看詳情', '立即報名'];

// 次要操作（1-2個）
$quickOptions[] = '返回列表';
$quickOptions[] = '聯絡客服';

// 總數建議不超過 5 個
return [
    'content' => $content,
    'quick_options' => $quickOptions
];
```

---

## 效能優化建議

### 1. 啟用緩存

```php
// 在 RAGService 中
protected $cacheDuration = 3600; // 1小時

// 或在 config/cache.php 中配置
'chatbot' => [
    'ttl' => 3600  // 1小時
],
```

### 2. 減少 OpenAI 調用

```php
// 優先使用模糊匹配和模式檢測
// 只在真正需要時才調用 OpenAI

if ($this->canHandleLocally($message)) {
    return $this->localHandle($message);
}

// 降級機制
if (!$this->isOpenAIAvailable()) {
    return $this->fallbackHandle($message);
}
```

### 3. 查詢優化

```php
// 使用權重評分代替全文搜尋
protected function searchWithScoring($items, $keyword)
{
    $results = [];

    foreach ($items as $item) {
        $score = 0;

        // 標題匹配 +10
        if (stripos($item['title'], $keyword) !== false) {
            $score += 10;
        }

        // 關鍵字匹配 +5
        foreach ($item['keywords'] as $kw) {
            if (stripos($kw, $keyword) !== false) {
                $score += 5;
                break;
            }
        }

        if ($score > 0) {
            $results[] = ['item' => $item, 'score' => $score];
        }
    }

    // 按分數降序排序
    usort($results, fn($a, $b) => $b['score'] <=> $a['score']);

    return array_column($results, 'item');
}
```

---

## 測試建議

### 單元測試範例

```php
use Tests\TestCase;
use App\Services\Chatbot\Agents\CourseAgent;

class CourseAgentTest extends TestCase
{
    /** @test */
    public function it_can_handle_course_list_query()
    {
        $agent = app(CourseAgent::class);

        $response = $agent->handle('AI課程有哪些');

        $this->assertArrayHasKey('content', $response);
        $this->assertArrayHasKey('quick_options', $response);
        $this->assertStringContainsString('AI', $response['content']);
    }

    /** @test */
    public function it_can_handle_course_number_query()
    {
        $session = app(\App\Services\Chatbot\SessionManager::class);
        $session->setContext('last_action', 'course_list');

        $agent = app(CourseAgent::class);

        $response = $agent->handle('1');

        $this->assertStringContainsString('課程名稱', $response['content']);
    }
}
```

---

**API 文檔版本**: v1.0.0
**維護者**: Claude Code AI
**最後審查**: 2025-10-28
