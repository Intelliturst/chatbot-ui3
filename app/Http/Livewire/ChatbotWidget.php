<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Services\SessionManager;
use App\Services\Agents\ClassificationAgent;

class ChatbotWidget extends Component
{
    // 公開屬性
    public $messages = [];
    public $userInput = '';
    public $isOpen = false;
    public $sessionInfo = [];

    /**
     * 組件初始化
     */
    public function mount()
    {
        // 載入對話歷史
        $this->loadHistory();

        // 如果是新對話（無歷史記錄），發送歡迎訊息
        if (empty($this->messages)) {
            $this->addWelcomeMessage();
        }

        // 更新 Session 資訊
        $this->updateSessionInfo();
    }

    /**
     * 取得 SessionManager 實例
     */
    protected function getSessionManager()
    {
        return app(SessionManager::class);
    }

    /**
     * 取得 ClassificationAgent 實例
     */
    protected function getClassificationAgent()
    {
        return app(ClassificationAgent::class);
    }

    /**
     * 取得 RAGService 實例
     */
    protected function getRAGService()
    {
        return app(\App\Services\RAGService::class);
    }

    /**
     * 載入對話歷史
     */
    protected function loadHistory()
    {
        $history = $this->getSessionManager()->getHistory();

        $this->messages = array_map(function($msg) {
            return [
                'role' => $msg['role'],
                'content' => $msg['content'],
                'timestamp' => date('H:i', strtotime($msg['timestamp'])),
                'quick_options' => []
            ];
        }, $history);
    }

    /**
     * 發送訊息
     *
     * @param string|null $message 訊息內容（可選，優先使用參數）
     */
    public function sendMessage($message = null)
    {
        // 優先使用參數，fallback 到 $this->userInput
        $userMessage = $message ?? $this->userInput;

        \Log::info('ChatbotWidget::sendMessage called', [
            'param_message' => $message,
            'this_userInput' => $this->userInput,
            'final_userMessage' => $userMessage
        ]);

        if (empty(trim($userMessage))) {
            \Log::warning('ChatbotWidget::sendMessage - Empty message');
            return;
        }

        // 加入用戶訊息
        \Log::info('Adding user message');
        $this->addUserMessage($userMessage);

        // 清空輸入
        $this->userInput = '';

        try {
            // 調用分類代理處理
            \Log::info('Calling ClassificationAgent');
            $response = $this->getClassificationAgent()->handle($userMessage);
            \Log::info('ClassificationAgent response received', ['has_content' => !empty($response['content'])]);

            // 加入AI回覆
            \Log::info('Adding assistant message');
            $this->addAssistantMessage(
                $response['content'],
                $response['quick_options'] ?? []
            );
            \Log::info('Assistant message added successfully');

        } catch (\Exception $e) {
            \Log::error('Chatbot Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            // 錯誤回覆
            $this->addAssistantMessage(
                '抱歉，系統暫時無法處理您的請求。請稍後再試或聯絡客服：03-4227723',
                ['聯絡客服']
            );
        }

        // 更新 Session 資訊
        \Log::info('Updating session info');
        $this->updateSessionInfo();

        // 滾動到底部
        $this->dispatchBrowserEvent('scroll-to-bottom');
        \Log::info('ChatbotWidget::sendMessage completed');
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
     * 清除對話記錄
     */
    public function clearSession()
    {
        $this->getSessionManager()->clearSession();
        $this->messages = [];
        $this->addWelcomeMessage();
        $this->updateSessionInfo();

        $this->dispatchBrowserEvent('session-cleared', [
            'message' => '對話記錄已清除'
        ]);
    }

    /**
     * 加入用戶訊息
     */
    protected function addUserMessage($content)
    {
        $message = [
            'role' => 'user',
            'content' => $content,
            'timestamp' => now()->format('H:i')
        ];

        $this->messages[] = $message;

        // 同步保存到 SessionManager
        $this->getSessionManager()->addMessage('user', $content);
    }

    /**
     * 加入AI訊息
     */
    protected function addAssistantMessage($content, $quickOptions = [])
    {
        $message = [
            'role' => 'assistant',
            'content' => $content,
            'timestamp' => now()->format('H:i'),
            'quick_options' => $quickOptions
        ];

        $this->messages[] = $message;

        // 同步保存到 SessionManager
        $this->getSessionManager()->addMessage('assistant', $content);

        // 保存上下文
        $this->getSessionManager()->setContext('last_response', $content);
    }

    /**
     * 加入歡迎訊息
     */
    protected function addWelcomeMessage()
    {
        // 從 JSON 讀取歡迎訊息
        $greetingData = $this->getRAGService()->getDefaultResponse('greetings');

        if ($greetingData) {
            $this->addAssistantMessage(
                $greetingData['response'] ?? '您好！我是虹宇職訓的智能客服小幫手 👋',
                $greetingData['quick_options'] ?? []
            );
        } else {
            // 備用歡迎訊息（如果 JSON 讀取失敗）
            $this->addAssistantMessage(
                "您好！我是虹宇職訓的智能客服小幫手 👋\n\n請問有什麼可以幫您的呢？",
                ['查看課程清單', '補助資格確認', '如何報名', '聯絡客服']
            );
        }
    }

    /**
     * 選擇快速選項
     */
    public function selectOption($option)
    {
        $this->userInput = $option;
        // selectOption 會調用 sendMessage，sendMessage 會觸發 processing 事件
        $this->sendMessage();
    }

    /**
     * 更新 Session 資訊
     */
    protected function updateSessionInfo()
    {
        $this->sessionInfo = $this->getSessionManager()->getSessionInfo();
    }

    /**
     * 渲染組件
     */
    public function render()
    {
        return view('livewire.chatbot-widget');
    }
}
