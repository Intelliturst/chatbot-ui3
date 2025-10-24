<?php

namespace App\Services\Agents;

use App\Services\OpenAIService;
use App\Services\SessionManager;
use App\Services\RAGService;

abstract class BaseAgent
{
    protected $openAI;
    protected $session;
    protected $rag;

    public function __construct(OpenAIService $openAI, SessionManager $session, RAGService $rag)
    {
        $this->openAI = $openAI;
        $this->session = $session;
        $this->rag = $rag;
    }

    /**
     * 處理用戶訊息（子類必須實現）
     *
     * @param string $userMessage
     * @return array ['content' => string, 'quick_options' => array]
     */
    abstract public function handle($userMessage);

    /**
     * 獲取系統提示詞（子類可覆寫）
     *
     * @return string
     */
    abstract protected function getSystemPrompt();

    /**
     * 生成 AI 回覆
     *
     * @param string $userMessage
     * @param array $context 額外上下文
     * @return string|null
     */
    protected function generateResponse($userMessage, $context = [])
    {
        $messages = $this->buildMessages($userMessage, $context);

        $result = $this->openAI->agent($messages);

        if ($result['success']) {
            return $result['content'];
        }

        return null;
    }

    /**
     * 建立訊息陣列
     *
     * @param string $userMessage
     * @param array $context
     * @return array
     */
    protected function buildMessages($userMessage, $context = [])
    {
        $messages = [];

        // 系統提示詞
        $systemPrompt = $this->getSystemPrompt();
        if ($systemPrompt) {
            $messages[] = [
                'role' => 'system',
                'content' => $systemPrompt
            ];
        }

        // 加入上下文（如果有）
        if (!empty($context)) {
            $contextStr = $this->formatContext($context);
            $messages[] = [
                'role' => 'system',
                'content' => "以下是相關資訊：\n\n" . $contextStr
            ];
        }

        // 對話歷史（最近3輪）
        $history = $this->session->getHistory(6); // 3輪 = 6條訊息
        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content']
            ];
        }

        // 當前用戶訊息
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        return $messages;
    }

    /**
     * 格式化上下文資訊
     *
     * @param array $context
     * @return string
     */
    protected function formatContext($context)
    {
        if (empty($context)) {
            return '';
        }

        $formatted = [];
        foreach ($context as $key => $value) {
            if (is_array($value)) {
                $formatted[] = $key . ': ' . json_encode($value, JSON_UNESCAPED_UNICODE);
            } else {
                $formatted[] = $key . ': ' . $value;
            }
        }

        return implode("\n", $formatted);
    }

    /**
     * 錯誤回覆
     *
     * @return array
     */
    protected function errorResponse()
    {
        return [
            'content' => '抱歉，系統暫時無法處理您的請求。請稍後再試或聯絡客服：03-4227723',
            'quick_options' => ['聯絡客服']
        ];
    }
}
