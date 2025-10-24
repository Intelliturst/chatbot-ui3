<?php

namespace App\Services\Agents;

use App\Services\RAGService;

class HumanServiceAgent extends BaseAgent
{
    protected $ragService;

    public function __construct($openAI, $session, RAGService $ragService)
    {
        parent::__construct($openAI, $session, $ragService);
        $this->ragService = $ragService;
    }

    /**
     * 處理用戶訊息
     */
    public function handle($userMessage)
    {
        $serviceInfo = $this->ragService->getServiceInfo();

        // 理解用戶需求
        $userNeed = $this->understandUserNeed($userMessage);

        $content = "我會為您轉接真人客服 ☎️\n\n";

        if ($userNeed) {
            $content .= "**您的需求**：{$userNeed}\n\n";
        }

        // 使用 service_info.json 的模板
        $template = $serviceInfo['response_template']['contact_info'];

        // 替換模板變數
        $contactInfo = str_replace(
            ['{phone}', '{hours}', '{email}', '{line}', '{address}', '{note}'],
            [
                $serviceInfo['contact']['phone']['display'],
                $serviceInfo['service_hours']['weekdays'],
                $serviceInfo['contact']['email']['general'],
                $serviceInfo['contact']['line']['id'],
                $serviceInfo['contact']['address']['full'],
                $serviceInfo['contact']['address']['note']
            ],
            $template
        );

        $content .= $contactInfo;

        $content .= "\n\n💡 建議：\n";
        $content .= "• 電話聯絡最快速\n";
        $content .= "• LINE 留言我們會盡快回覆\n";
        $content .= "• 歡迎直接到中心洽詢";

        return [
            'content' => $content,
            'quick_options' => ['回主選單', '查看課程', '補助資格']
        ];
    }

    /**
     * 理解用戶需求
     */
    protected function understandUserNeed($message)
    {
        // 使用OpenAI理解用戶想諮詢什么
        $systemPrompt = "你是客服助理。請用一句话（15字以内）總结用戶想諮詢的問題。只回答總结，不要其他說明。";

        $result = $this->openAI->chat([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $message]
        ], 'gpt-3.5-turbo', [
            'temperature' => 0.3,
            'max_tokens' => 50
        ]);

        if ($result['success']) {
            return trim($result['content']);
        }

        return null;
    }

    /**
     * 獲取系統提示詞
     */
    protected function getSystemPrompt()
    {
        return <<<EOT
你是虹宇職訓的客服转接助理。你的職責是：
1. 提供真人客服聯絡資訊
2. 理解用戶需求
3. 引导用戶选择最适合的聯絡方式

請用繁体中文回答，保持友善、專業的语气。
EOT;
    }
}
