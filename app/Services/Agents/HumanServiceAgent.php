<?php

namespace App\Services\Agents;

use App\Services\RAGService;

class HumanServiceAgent extends BaseAgent
{
    protected $ragService;

    public function __construct($openAI, $session, RAGService $ragService)
    {
        parent::__construct($openAI, $session);
        $this->ragService = $ragService;
    }

    /**
     * 处理用户消息
     */
    public function handle($userMessage)
    {
        $serviceInfo = $this->ragService->getServiceInfo();

        // 理解用户需求
        $userNeed = $this->understandUserNeed($userMessage);

        $content = "我会为您转接真人客服 ☎️\n\n";

        if ($userNeed) {
            $content .= "**您的需求**：{$userNeed}\n\n";
        }

        $content .= "📞 **联络方式**\n\n";
        $content .= "**电话**：\n";
        foreach ($serviceInfo['contact']['phone']['main'] as $phone) {
            $content .= "• {$phone}\n";
        }
        $content .= "营业时间：{$serviceInfo['contact']['phone']['available_hours']}\n\n";

        $content .= "**LINE 官方帐号**：\n";
        $content .= "• ID：{$serviceInfo['contact']['line']['id']}\n";
        $content .= "• 连结：{$serviceInfo['contact']['line']['link']}\n\n";

        $content .= "**Email**：\n";
        $content .= "• {$serviceInfo['contact']['email']['general']}\n\n";

        $content .= "**地址**：\n";
        $content .= "{$serviceInfo['contact']['address']['full']}\n";
        $content .= "({$serviceInfo['contact']['address']['note']})\n\n";

        $content .= "💡 建议：\n";
        $content .= "• 电话联络最快速\n";
        $content .= "• LINE 留言我们会尽快回复\n";
        $content .= "• 欢迎直接到中心洽询";

        return [
            'content' => $content,
            'quick_options' => ['回到主选单', '查看课程', '补助资格']
        ];
    }

    /**
     * 理解用户需求
     */
    protected function understandUserNeed($message)
    {
        // 使用OpenAI理解用户想咨询什么
        $systemPrompt = "你是客服助理。请用一句话（15字以内）总结用户想咨询的问题。只回答总结，不要其他说明。";

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
     * 获取系统提示词
     */
    protected function getSystemPrompt()
    {
        return <<<EOT
你是虹宇职训的客服转接助理。你的职责是：
1. 提供真人客服联络资讯
2. 理解用户需求
3. 引导用户选择最适合的联络方式

请用繁体中文回答，保持友善、专业的语气。
EOT;
    }
}
