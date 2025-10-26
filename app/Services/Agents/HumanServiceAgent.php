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

        // 檢測設備類型（PC 或手機）
        $isMobile = $this->isMobileDevice();

        // LINE 聯絡資訊（根據設備類型顯示不同內容）
        $content .= "**📱 LINE 官方帳號**\n";
        $content .= "LINE ID：{$serviceInfo['contact']['line']['id']}\n\n";

        if ($isMobile) {
            // 手機版：提供連結
            $content .= "<a href='https://lin.ee/2qmqoSH' target='_blank' style='display: inline-block; padding: 12px 24px; background: linear-gradient(to right, #06c755, #05b34a); color: white; text-decoration: none; border-radius: 8px; font-weight: bold; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>加入 LINE 官方帳號</a>\n\n";
        } else {
            // 電腦版：顯示 QR Code
            $content .= "<div style='text-align: center; margin: 20px 0;'>";
            $content .= "<img src='/images/line@.png' alt='LINE QR Code' style='width: 200px; height: 200px; border: 4px solid #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);' />";
            $content .= "<p style='margin-top: 10px; font-size: 14px; color: #666;'>使用手機掃描 QR Code 加入</p>";
            $content .= "</div>\n\n";
        }

        // 其他聯絡方式
        $content .= "**☎️ 電話**：{$serviceInfo['contact']['phone']['display']}\n";
        $content .= "服務時間：{$serviceInfo['service_hours']['weekdays']}\n\n";

        $content .= "**✉️ Email**：{$serviceInfo['contact']['email']['general']}\n\n";

        $content .= "**🏢 地址**：{$serviceInfo['contact']['address']['full']}\n";
        $content .= "{$serviceInfo['contact']['address']['note']}\n\n";

        $content .= "💡 建議：\n";
        $content .= "• 電話聯絡最快速\n";
        $content .= "• LINE 留言我們會盡快回覆\n";
        $content .= "• 歡迎直接到中心洽詢";

        return [
            'content' => $content,
            'quick_options' => ['課程列表', '補助資格', '聯絡客服']
        ];
    }

    /**
     * 檢測是否為手機設備
     */
    protected function isMobileDevice()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        return preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $userAgent);
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
你是虹宇職訓的客服轉接助理。你的職責是：
1. 提供真人客服聯絡資訊
2. 理解用戶需求
3. 引導用戶選擇最適合的聯絡方式

請用繁體中文回答，保持友善、專業的語氣。
EOT;
    }
}
