<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Services\SessionManager;

class ChatbotWidget extends Component
{
    // 公開屬性
    public $messages = [];
    public $userInput = '';
    public $isOpen = false;
    public $isLoading = false;
    public $sessionInfo = [];

    protected $sessionManager;

    /**
     * 組件初始化
     */
    public function mount()
    {
        $this->sessionManager = app(SessionManager::class);

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
     * 載入對話歷史
     */
    protected function loadHistory()
    {
        $history = $this->sessionManager->getHistory();

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

        // 模擬AI回覆（暫時）
        $response = "您說：「{$this->userInput}」。這是一個測試回覆，OpenAI 整合將在 Phase 3 完成。\n\n💡 Session 資訊：已保存 " . count($this->messages) . " 條訊息。";

        // 加入AI回覆
        $this->addAssistantMessage($response);

        // 清空輸入
        $this->userInput = '';
        $this->isLoading = false;

        // 更新 Session 資訊
        $this->updateSessionInfo();

        // 滾動到底部
        $this->dispatchBrowserEvent('scroll-to-bottom');
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
        $this->sessionManager->clearSession();
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
        $this->sessionManager->addMessage('user', $content);
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
        $this->sessionManager->addMessage('assistant', $content);

        // 保存上下文
        $this->sessionManager->setContext('last_response', $content);
    }

    /**
     * 加入歡迎訊息
     */
    protected function addWelcomeMessage()
    {
        $this->addAssistantMessage(
            "您好！我是虹宇職訓的智能客服小幫手 👋\n\n我可以協助您：\n1️⃣ 查詢課程資訊\n2️⃣ 了解補助資格\n3️⃣ 報名流程說明\n4️⃣ 常見問題解答\n\n請問有什麼可以幫您的呢？",
            ['查看課程清單', '補助資格確認', '如何報名', '聯絡客服']
        );
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
     * 更新 Session 資訊
     */
    protected function updateSessionInfo()
    {
        $this->sessionInfo = $this->sessionManager->getSessionInfo();
    }

    /**
     * 渲染組件
     */
    public function render()
    {
        return view('livewire.chatbot-widget');
    }
}
