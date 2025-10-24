# 虹宇職訓智能客服系統 - Laravel開發規範

**文件版本**: 2.0
**最後更新**: 2025-10-24
**作者**: 虹宇職訓開發團隊

---

## 📋 完整專案結構（Laravel 8 標準架構）

```
chatbot-ui3/                              # 專案根目錄
├── app/                                  # Laravel 應用程式碼
│   ├── Console/
│   │   └── Commands/
│   │       └── ImportCoursesCommand.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── CourseController.php
│   │   │
│   │   ├── Livewire/
│   │   │   └── ChatbotWidget.php
│   │   │
│   │   ├── Middleware/
│   │   └── Kernel.php
│   │
│   ├── Models/
│   │   └── Course.php
│   │
│   ├── Services/                         # 業務邏輯服務
│   │   ├── Agents/                       # 多代理系統
│   │   │   ├── BaseAgent.php
│   │   │   ├── ClassificationAgent.php
│   │   │   ├── CourseAgent.php
│   │   │   ├── SubsidyAgent.php
│   │   │   ├── FAQAgent.php
│   │   │   ├── FeaturedAgent.php
│   │   │   └── HumanServiceAgent.php
│   │   │
│   │   ├── OpenAIService.php             # OpenAI API 整合
│   │   ├── RAGService.php                # RAG 檢索服務
│   │   ├── SessionManager.php            # 會話管理
│   │   └── CourseAPIService.php          # 課程 API 服務
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── RouteServiceProvider.php
│   │   └── ChatbotServiceProvider.php    # 智能客服服務提供者
│   │
│   └── Exceptions/
│       └── Handler.php
│
├── resources/                            # 資源文件
│   ├── views/
│   │   ├── livewire/
│   │   │   └── chatbot-widget.blade.php
│   │   └── welcome.blade.php
│   │
│   ├── data/                             # 靜態數據資源（需版本控管）
│   │   └── chatbot/                      # 智能客服知識庫
│   │       ├── courses/                  # 課程資料（JSON模擬API）
│   │       │   ├── 6.json                # 課程 ID 6
│   │       │   └── 12.json               # 課程 ID 12
│   │       │
│   │       ├── subsidy/                  # 補助資訊
│   │       │   ├── unemployed.json       # 待業補助
│   │       │   └── employed.json         # 在職補助
│   │       │
│   │       ├── faq/                      # 常見問題
│   │       │   └── general.json
│   │       │
│   │       ├── service_info.json         # 服務資訊（聯絡方式）
│   │       └── greetings.json            # 歡迎訊息與快速選項
│   │
│   ├── js/
│   │   └── app.js
│   │
│   └── css/
│       └── app.css
│
├── storage/                              # 可寫入的儲存（不進版控）
│   ├── app/
│   │   └── chatbot/                      # 智能客服動態數據
│   │       ├── sessions/                 # 會話數據（Session）
│   │       └── logs/                     # 對話日誌
│   │
│   ├── framework/
│   │   ├── cache/
│   │   ├── sessions/
│   │   └── views/
│   └── logs/
│       └── laravel.log
│
├── config/                               # 配置文件
│   ├── app.php
│   ├── database.php
│   ├── chatbot.php                       # 智能客服配置（新增）
│   └── ...
│
├── database/                             # 資料庫
│   ├── migrations/
│   │   └── 2025_10_24_create_courses_table.php
│   ├── seeders/
│   └── factories/
│
├── routes/                               # 路由定義
│   ├── web.php                           # Web 路由
│   └── api.php                           # API 路由
│
├── public/                               # 公開資源（Web 根目錄）
│   ├── index.php
│   ├── logo.png                          # 虹宇職訓 Logo
│   ├── agent.png                         # AI 助手頭像
│   ├── css/
│   ├── js/
│   └── images/
│       └── courses/                      # 課程圖片
│
├── tests/                                # 測試
│   ├── Feature/
│   │   ├── AgentsTest.php
│   │   └── CourseAPITest.php
│   └── Unit/
│
├── vendor/                               # Composer 依賴（自動生成）
├── node_modules/                         # NPM 依賴（自動生成）
├── .env                                  # 環境變數
├── .env.example                          # 環境變數範例
├── composer.json                         # PHP 依賴配置
├── composer.lock
├── package.json                          # NPM 依賴配置
├── webpack.mix.js                        # Laravel Mix 配置
└── artisan                               # Artisan CLI
```

---

## 🎯 架構說明

### 1. Laravel 標準目錄說明

#### `app/` - 應用程式核心

- **Console/Commands/** - Artisan 自訂命令
- **Http/Controllers/** - 控制器（MVC 的 C）
- **Http/Livewire/** - Livewire 全端組件
- **Models/** - Eloquent 模型（MVC 的 M）
- **Services/** - 業務邏輯服務層（推薦模式）
- **Providers/** - 服務提供者（依賴注入容器）

#### `resources/` - 前端資源

- **views/** - Blade 模板（MVC 的 V）
- **js/** - JavaScript 源碼
- **css/** - CSS 源碼

#### `resources/data/` - 靜態數據資源（需版本控管）

- **chatbot/** - 智能客服知識庫（JSON格式）
- **courses/** - 課程資料（模擬API回應）
- **subsidy/** - 補助政策資訊
- **faq/** - 常見問題

#### `storage/` - 可寫入儲存（不進版控）

- **app/chatbot/** - 動態生成的會話和日誌
- **framework/** - Laravel 框架快取
- **logs/** - 應用程式日誌

#### `config/` - 配置文件

所有 `.php` 配置文件，使用 `config('key')` 讀取

#### `routes/` - 路由定義

- **web.php** - Web 路由（Session、CSRF）
- **api.php** - API 路由（Stateless）

#### `public/` - Web 根目錄

對外可訪問的靜態資源，`index.php` 是入口點

---

## Livewire組件設計

### ChatbotWidget.php（主組件）

**位置**: `app/Http/Livewire/ChatbotWidget.php`

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Services\Agents\ClassificationAgent;
use App\Services\SessionManager;

class ChatbotWidget extends Component
{
    // 公開屬性
    public $messages = [];
    public $userInput = '';
    public $isOpen = false;
    public $isLoading = false;
    public $quickOptions = [];

    // 監聽器
    protected $listeners = [
        'messageReceived' => 'addMessage'
    ];

    /**
     * 組件初始化
     */
    public function mount()
    {
        $sessionManager = app(SessionManager::class);

        // 載入對話歷史
        $this->messages = $this->formatMessages($sessionManager->getHistory());

        // 載入快速選項
        $this->quickOptions = $this->loadQuickOptions();

        // 如果是新對話，發送歡迎訊息
        if (empty($this->messages)) {
            $this->addWelcomeMessage();
        }
    }

    /**
     * 發送訊息
     */
    public function sendMessage()
    {
        if (empty(trim($this->userInput))) {
            return;
        }

        // 加入用戶訊息
        $this->addUserMessage($this->userInput);

        // 顯示載入動畫
        $this->isLoading = true;

        // 調用分類代理處理
        $response = $this->processMessage($this->userInput);

        // 加入AI回覆
        $this->addAssistantMessage($response['content'], $response['quick_options'] ?? []);

        // 清空輸入
        $this->userInput = '';
        $this->isLoading = false;

        // 滾動到底部
        $this->dispatchBrowserEvent('scroll-to-bottom');
    }

    /**
     * 選擇快速選項
     */
    public function selectOption($option)
    {
        $this->userInput = $option;
        $this->sendMessage();
    }

    /**
     * 開關視窗
     */
    public function toggleWidget()
    {
        $this->isOpen = !$this->isOpen;

        if ($this->isOpen) {
            $this->dispatchBrowserEvent('widget-opened');
        }
    }

    /**
     * 處理訊息（調用分類代理）
     */
    protected function processMessage($userMessage)
    {
        $classificationAgent = app(ClassificationAgent::class);

        try {
            $response = $classificationAgent->handle($userMessage);
            return $response;
        } catch (\Exception $e) {
            \Log::error('Chatbot Error: ' . $e->getMessage());

            return [
                'content' => '抱歉，系統暫時無法回應。請稍後再試或聯絡客服：03-4227723',
                'quick_options' => ['聯絡客服']
            ];
        }
    }

    /**
     * 加入用戶訊息
     */
    protected function addUserMessage($content)
    {
        $sessionManager = app(SessionManager::class);

        $message = [
            'role' => 'user',
            'content' => $content,
            'timestamp' => now()->format('H:i')
        ];

        $this->messages[] = $message;
        $sessionManager->addMessage('user', $content);
    }

    /**
     * 加入AI訊息
     */
    protected function addAssistantMessage($content, $quickOptions = [])
    {
        $sessionManager = app(SessionManager::class);

        $message = [
            'role' => 'assistant',
            'content' => $content,
            'timestamp' => now()->format('H:i'),
            'quick_options' => $quickOptions
        ];

        $this->messages[] = $message;
        $sessionManager->addMessage('assistant', $content);
        $sessionManager->setContext('last_response', $content);
    }

    /**
     * 加入歡迎訊息
     */
    protected function addWelcomeMessage()
    {
        // 從 resources/data/chatbot/greetings.json 載入
        $greetingPath = resource_path('data/chatbot/greetings.json');

        if (file_exists($greetingPath)) {
            $greetingData = json_decode(file_get_contents($greetingPath), true);
            $message = $greetingData['message'] ?? $this->getDefaultGreeting();
            $options = $greetingData['quick_options'] ?? $this->getDefaultQuickOptions();
        } else {
            $message = $this->getDefaultGreeting();
            $options = $this->getDefaultQuickOptions();
        }

        $this->addAssistantMessage($message, $options);
    }

    /**
     * 預設歡迎訊息
     */
    protected function getDefaultGreeting()
    {
        return "您好！我是虹宇職訓的智能客服小幫手 👋\n\n我可以協助您：\n1️⃣ 查詢課程資訊\n2️⃣ 了解補助資格\n3️⃣ 報名流程說明\n4️⃣ 常見問題解答\n\n請問有什麼可以幫您的呢？";
    }

    /**
     * 預設快速選項
     */
    protected function getDefaultQuickOptions()
    {
        return ['查看課程清單', '補助資格確認', '如何報名', '聯絡客服'];
    }

    /**
     * 載入快速選項
     */
    protected function loadQuickOptions()
    {
        return [
            ['label' => '課程查詢', 'icon' => '📚'],
            ['label' => '補助諮詢', 'icon' => '💰'],
            ['label' => '報名流程', 'icon' => '📝'],
            ['label' => '聯絡客服', 'icon' => '☎️']
        ];
    }

    /**
     * 格式化訊息
     */
    protected function formatMessages($historyMessages)
    {
        return array_map(function($msg) {
            return [
                'role' => $msg['role'],
                'content' => $msg['content'],
                'timestamp' => isset($msg['timestamp'])
                    ? date('H:i', strtotime($msg['timestamp']))
                    : now()->format('H:i'),
                'quick_options' => []
            ];
        }, $historyMessages);
    }

    /**
     * 渲染組件
     */
    public function render()
    {
        return view('livewire.chatbot-widget');
    }
}
```

---

## Blade視圖模板

### chatbot-widget.blade.php

**位置**: `resources/views/livewire/chatbot-widget.blade.php`

```blade
{{-- resources/views/livewire/chatbot-widget.blade.php --}}

<div>
    {{-- 浮動按鈕（未展開狀態） --}}
    @if(!$isOpen)
    <button
        wire:click="toggleWidget"
        class="fixed bottom-6 right-6 w-16 h-16 bg-primary rounded-full shadow-lg
               hover:bg-primary-dark transition-all duration-300 flex items-center justify-center z-50">
        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
        </svg>
    </button>
    @endif

    {{-- 聊天視窗（展開狀態） --}}
    @if($isOpen)
    <div
        x-data="{ scrollToBottom() { this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight; } }"
        x-init="scrollToBottom()"
        @widget-opened.window="scrollToBottom()"
        @scroll-to-bottom.window="scrollToBottom()"
        class="fixed bottom-0 right-0 md:bottom-6 md:right-6 w-full md:w-[400px]
               h-screen md:h-auto md:max-h-[600px] bg-white rounded-none md:rounded-2xl
               shadow-2xl flex flex-col overflow-hidden z-50
               animate-slide-in-up md:animate-slide-in-right">

        {{-- Header --}}
        <div class="flex items-center justify-between p-4 bg-primary text-white
                    rounded-t-none md:rounded-t-2xl flex-shrink-0">
            <div class="flex items-center space-x-3">
                <img src="/logo.png" alt="虹宇職訓" class="h-6 md:h-8 w-auto">
                <h2 class="text-base md:text-lg font-semibold">虹宇職訓</h2>
            </div>

            <div class="flex space-x-2">
                <button class="w-8 h-8 rounded-full hover:bg-white/20 transition-colors">
                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </button>
                <button wire:click="toggleWidget" class="w-8 h-8 rounded-full hover:bg-white/20 transition-colors">
                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Messages Area --}}
        <div x-ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
            @foreach($messages as $message)
                @if($message['role'] === 'user')
                    {{-- 用戶訊息 --}}
                    <div class="flex justify-end">
                        <div class="bg-gray-100 text-gray-900 px-4 py-3 rounded-2xl rounded-tr-sm max-w-[80%]">
                            <p class="text-sm whitespace-pre-line">{{ $message['content'] }}</p>
                            <span class="text-xs text-gray-500 mt-1 block">{{ $message['timestamp'] }}</span>
                        </div>
                    </div>
                @else
                    {{-- AI訊息 --}}
                    <div class="flex justify-start">
                        <div class="w-8 h-8 rounded-full flex-shrink-0 mr-2">
                            <img src="/agent.png" alt="AI助手" class="w-full h-full">
                        </div>
                        <div class="bg-primary/10 text-gray-900 px-4 py-3 rounded-2xl rounded-tl-sm max-w-[80%]">
                            <p class="text-sm whitespace-pre-line">{{ $message['content'] }}</p>
                            <span class="text-xs text-gray-500 mt-1 block">{{ $message['timestamp'] }}</span>

                            {{-- 關聯問題 --}}
                            @if(!empty($message['quick_options']))
                                <div class="mt-3 space-y-2">
                                    @foreach($message['quick_options'] as $option)
                                        <button
                                            wire:click="selectOption('{{ $option }}')"
                                            class="w-full text-left bg-white hover:bg-gray-50 px-3 py-2
                                                   rounded-lg text-sm text-gray-700 transition-colors border border-gray-200">
                                            {{ $option }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach

            {{-- Loading --}}
            @if($isLoading)
                <div class="flex justify-start">
                    <div class="bg-primary/10 px-4 py-3 rounded-2xl rounded-tl-sm">
                        <div class="flex space-x-2">
                            <div class="w-2 h-2 bg-primary rounded-full animate-bounce"></div>
                            <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                            <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Input Area --}}
        <div class="p-4 bg-white border-t border-gray-200 rounded-b-none md:rounded-b-2xl flex-shrink-0">
            <div class="flex items-end space-x-2">
                <textarea
                    wire:model.defer="userInput"
                    rows="1"
                    placeholder="請輸入您的問題..."
                    class="flex-1 px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl
                           focus:outline-none focus:border-primary resize-none text-sm"
                    wire:keydown.enter.prevent="sendMessage"
                ></textarea>

                <button
                    wire:click="sendMessage"
                    wire:loading.attr="disabled"
                    class="w-12 h-12 bg-primary text-white rounded-xl hover:bg-primary-dark
                           transition-colors flex items-center justify-center flex-shrink-0
                           disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
```

---

## 配置文件

### config/chatbot.php

**位置**: `config/chatbot.php`

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 智能客服配置
    |--------------------------------------------------------------------------
    */

    // 是否使用Course API（false使用JSON）
    'use_course_api' => env('CHATBOT_USE_COURSE_API', false),

    // Course API URL
    'course_api_url' => env('CHATBOT_COURSE_API_URL', '/api/courses'),

    // OpenAI API設定
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'classification_model' => 'gpt-3.5-turbo',
        'agent_model' => 'gpt-4-turbo',
        'max_tokens' => 1500,
        'temperature' => 0.7,
    ],

    // Session設定
    'session' => [
        'lifetime' => 60, // 分鐘
        'max_history' => 20, // 最多保存20條對話記錄
    ],

    // 快取設定
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 秒
        'prefix' => 'chatbot_',
    ],

    // 客服通知API
    'notification_api' => env('CHATBOT_NOTIFICATION_API'),

    // 知識庫路徑（JSON 模式，需版本控管）
    'knowledge_base_path' => resource_path('data/chatbot'),

    // 動態數據路徑（會話、日誌，不進版控）
    'storage_path' => storage_path('app/chatbot'),
];
```

---

## 路由設定

### routes/web.php

**位置**: `routes/web.php`

```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Livewire 組件會自動註冊路由（無需手動設定）
```

### routes/api.php

**位置**: `routes/api.php`

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CourseController;

// Course API
Route::prefix('courses')->group(function() {
    Route::get('/', [CourseController::class, 'index']);
    Route::get('/search', [CourseController::class, 'search']);
    Route::get('/{id}', [CourseController::class, 'show']);
});
```

---

## Service Provider

### ChatbotServiceProvider.php

**位置**: `app/Providers/ChatbotServiceProvider.php`

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\OpenAIService;
use App\Services\RAGService;
use App\Services\SessionManager;
use App\Services\CourseAPIService;

class ChatbotServiceProvider extends ServiceProvider
{
    /**
     * 註冊服務
     */
    public function register()
    {
        // 註冊單例服務
        $this->app->singleton(SessionManager::class, function($app) {
            return new SessionManager();
        });

        $this->app->singleton(OpenAIService::class, function($app) {
            return new OpenAIService(config('chatbot.openai'));
        });

        $this->app->singleton(RAGService::class, function($app) {
            return new RAGService(
                $app->make(CourseAPIService::class)
            );
        });

        $this->app->singleton(CourseAPIService::class, function($app) {
            return new CourseAPIService();
        });
    }

    /**
     * 啟動服務
     */
    public function boot()
    {
        // 載入 Livewire 組件
        \Livewire\Livewire::component('chatbot-widget', \App\Http\Livewire\ChatbotWidget::class);
    }
}
```

**註冊到 `config/app.php`**：

```php
'providers' => [
    // ...
    App\Providers\ChatbotServiceProvider::class,
],
```

---

## 知識庫 JSON 路徑說明

### 知識庫結構

所有 JSON 知識庫文件存放在：**`resources/data/chatbot/`**（需版本控管）

```
resources/data/chatbot/
├── courses/                    # 課程資料（模擬API回應）
│   ├── 6.json                  # 課程 ID 6（待業課程）
│   └── 12.json                 # 課程 ID 12（在職課程）
│
├── subsidy/                    # 補助資訊
│   ├── unemployed.json         # 待業補助政策
│   └── employed.json           # 在職補助政策
│
├── faq/                        # 常見問題
│   └── general.json            # 一般性FAQ
│
├── service_info.json           # 服務資訊（聯絡方式、地址）
└── greetings.json              # 歡迎訊息與快速選項
```

### 課程資料來源說明

**📌 重要**：課程資料不存在本地伺服器，實際運作時透過 API 取得

- **開發階段**：使用 `resources/data/chatbot/courses/*.json` 模擬 API 回應
- **生產環境**：透過外部 API 取得課程資料（`https://www.hongyu.goblinlab.org/api/courses`）
- **切換方式**：在 `.env` 設定 `CHATBOT_USE_COURSE_API=true` 啟用 API 模式

### 在程式中讀取 JSON

```php
// 讀取課程資料（模擬 API 回應）
$coursePath = resource_path('data/chatbot/courses/6.json');
$courseData = json_decode(file_get_contents($coursePath), true);

// 讀取服務資訊
$serviceInfoPath = resource_path('data/chatbot/service_info.json');
$serviceInfo = json_decode(file_get_contents($serviceInfoPath), true);

// 使用 config 輔助函數
$knowledgeBasePath = config('chatbot.knowledge_base_path');
$greetingPath = $knowledgeBasePath . '/greetings.json';
```

### 動態數據儲存位置

會話和日誌等動態數據存放在：**`storage/app/chatbot/`**（不進版控）

```
storage/app/chatbot/
├── sessions/                   # 用戶會話數據
│   └── {session_id}.json
└── logs/                       # 對話日誌
    └── chat_log_2025-10-24.log
```

---

## 開發流程範例

### 1. 安裝依賴

```bash
composer install
npm install
```

### 2. 環境配置

```bash
cp .env.example .env
php artisan key:generate
```

編輯 `.env`：

```env
APP_NAME="虹宇職訓智能客服"
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chatbot_ui3
DB_USERNAME=root
DB_PASSWORD=

OPENAI_API_KEY=sk-xxxx
CHATBOT_USE_COURSE_API=false
```

### 3. 建立知識庫與動態數據目錄

```bash
# 知識庫目錄（需版本控管）
mkdir -p resources/data/chatbot/courses
mkdir -p resources/data/chatbot/subsidy
mkdir -p resources/data/chatbot/faq

# 動態數據目錄（不進版控）
mkdir -p storage/app/chatbot/sessions
mkdir -p storage/app/chatbot/logs
```

### 4. 資料庫設定

```bash
php artisan migrate
php artisan chatbot:import-courses
```

### 5. 編譯前端資源

```bash
npm run dev
# 或生產環境
npm run production
```

### 6. 啟動開發服務器

```bash
php artisan serve
```

訪問：`http://localhost:8000`

---

## 命名空間規範

### PHP 類別命名空間

所有類別必須遵循 PSR-4 自動載入標準：

```php
// app/Http/Livewire/ChatbotWidget.php
namespace App\Http\Livewire;

// app/Services/Agents/ClassificationAgent.php
namespace App\Services\Agents;

// app/Models/Course.php
namespace App\Models;

// app/Http/Controllers/Api/CourseController.php
namespace App\Http\Controllers\Api;
```

### Blade 視圖路徑

```php
// resources/views/livewire/chatbot-widget.blade.php
view('livewire.chatbot-widget')

// resources/views/welcome.blade.php
view('welcome')
```

---

---

## 🎯 重要設計原則

### 1. 版本控管策略

#### ✅ 需要版本控管（進入 Git）

```
resources/data/chatbot/           # 靜態知識庫
├── courses/*.json                # 課程資料（模擬API）
├── subsidy/*.json                # 補助政策
├── faq/*.json                    # 常見問題
├── service_info.json             # 服務資訊
└── greetings.json                # 歡迎訊息
```

**原因**：這些是業務邏輯配置，修改後需追蹤歷史

#### ❌ 不進版本控管（加入 .gitignore）

```
storage/app/chatbot/              # 動態生成數據
├── sessions/*.json               # 會話數據（用戶隱私）
└── logs/*.log                    # 對話日誌（運行時生成）
```

**原因**：這些是運行時動態數據，不應進入版控

### 2. 課程資料雙模式設計

#### 模式 A：JSON 模擬（開發階段）

```env
CHATBOT_USE_COURSE_API=false
```

- 從 `resources/data/chatbot/courses/*.json` 讀取
- 用於本地開發和測試
- 不依賴外部 API

#### 模式 B：API 取得（生產環境）

```env
CHATBOT_USE_COURSE_API=true
CHATBOT_COURSE_API_URL=https://www.hongyu.goblinlab.org/api/courses
```

- 從外部 API 即時取得課程資料
- 資料永遠是最新的
- 需要網路連線

### 3. 服務層設計

**CourseAPIService.php** 應實作兩種模式：

```php
class CourseAPIService
{
    public function getCourses($type = null)
    {
        if (config('chatbot.use_course_api')) {
            // 模式 B：從外部 API 取得
            return $this->fetchFromAPI($type);
        } else {
            // 模式 A：從本地 JSON 讀取
            return $this->loadFromJSON($type);
        }
    }

    protected function fetchFromAPI($type)
    {
        $url = config('chatbot.course_api_url');
        // HTTP Client 調用 API
        $response = Http::get($url, ['type' => $type]);
        return $response->json();
    }

    protected function loadFromJSON($type)
    {
        $path = resource_path('data/chatbot/courses');
        // 讀取本地 JSON 文件
        $files = glob("{$path}/*.json");
        $courses = [];
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if (!$type || $data['type'] === $type) {
                $courses[] = $data;
            }
        }
        return $courses;
    }
}
```

---

## 附錄

### 相關文件

- [01-system-architecture.md](./01-system-architecture.md) - 系統架構設計
- [02-knowledge-base-structure.md](./02-knowledge-base-structure.md) - 知識庫結構
- [03-agent-implementation.md](./03-agent-implementation.md) - 代理實現規範
- [04-frontend-ui-specification.md](./04-frontend-ui-specification.md) - 前端UI規範
- [05-course-api-integration.md](./05-course-api-integration.md) - 課程API整合
- [07-development-roadmap.md](./07-development-roadmap.md) - 開發階段計劃

### Laravel 文檔

- [Laravel 8 官方文檔](https://laravel.com/docs/8.x)
- [Livewire 2.x 文檔](https://laravel-livewire.com/docs/2.x/quickstart)
- [Tailwind CSS 文檔](https://tailwindcss.com/docs)

---

**文件結束**
