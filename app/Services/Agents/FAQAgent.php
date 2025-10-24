<?php

namespace App\Services\Agents;

use App\Services\RAGService;

class FAQAgent extends BaseAgent
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
        // 搜尋FAQ
        $faqResults = $this->ragService->searchFAQ($userMessage);

        if (!empty($faqResults)) {
            // 找到相关FAQ
            return $this->provideFAQAnswer($faqResults, $userMessage);
        }

        // 没找到，使用OpenAI结合知识库回答
        return $this->provideGeneralAnswer($userMessage);
    }

    /**
     * 提供FAQ答案
     */
    protected function provideFAQAnswer($faqResults, $userMessage)
    {
        if (count($faqResults) == 1) {
            // 只有一个結果，直接回答
            $faq = $faqResults[0];

            $content = "**{$faq['question']}**\n\n";
            $content .= $faq['answer'];

            $quickOptions = isset($faq['related_questions'])
                ? array_slice($faq['related_questions'], 0, 4)
                : ['其他問題', '查看課程', '補助資格', '聯絡客服'];

            return [
                'content' => $content,
                'quick_options' => $quickOptions
            ];
        }

        // 多个結果，列出让用戶选择
        $content = "我找到以下相关問題：\n\n";

        foreach (array_slice($faqResults, 0, 4) as $index => $faq) {
            $num = $index + 1;
            $content .= "{$num}. {$faq['question']}\n";
        }

        $content .= "\n💡 請选择您想了解的問題，或直接描述您的問題";

        // 将FAQ結果缓存到session
        $this->session->setContext('faq_results', $faqResults);

        return [
            'content' => $content,
            'quick_options' => array_map(function($index) {
                return (string)($index + 1);
            }, range(0, min(3, count($faqResults) - 1)))
        ];
    }

    /**
     * 提供一般回答
     */
    protected function provideGeneralAnswer($userMessage)
    {
        // 从知识库獲取相关資訊
        $serviceInfo = $this->ragService->getServiceInfo();
        $subsidyFAQ = $this->ragService->getSubsidyFAQ();

        $context = [
            '聯絡資訊' => "電話：{$serviceInfo['contact']['phone']['display']}，LINE：{$serviceInfo['contact']['line']['id']}",
            '上课地点' => $serviceInfo['contact']['address']['full'],
            '营业時間' => $serviceInfo['service_hours']['weekdays']
        ];

        $response = $this->generateResponse($userMessage, $context);

        if ($response) {
            return [
                'content' => $response . "\n\n如需更多協助，欢迎聯絡客服：03-4227723",
                'quick_options' => ['查看課程', '補助資格', '報名流程', '聯絡客服']
            ];
        }

        // 无法回答，建议常見問題或聯絡客服
        $allFAQs = $this->ragService->searchFAQ();
        $commonQuestions = array_slice(array_map(function($faq) {
            return $faq['question'];
        }, $allFAQs), 0, 4);

        return [
            'content' => "很抱歉，我可能无法完全理解您的問題。\n\n以下是一些常見問題，或许能帮到您：\n\n" . implode("\n", array_map(function($q, $i) {
                return ($i + 1) . ". {$q}";
            }, $commonQuestions, array_keys($commonQuestions))),
            'quick_options' => ['聯絡客服', '查看課程', '補助資格']
        ];
    }

    /**
     * 獲取系統提示詞
     */
    protected function getSystemPrompt()
    {
        return <<<EOT
你是虹宇職訓的客服助理。你的職責是：
1. 回答学员的一般問題
2. 提供清晰、準確的資訊
3. 必要时引导学员聯絡客服

常見問題包括：
- 如何報名課程
- 上课地点与時間
- 請假规定
- 结业证书取得

請用繁体中文回答，保持友善、耐心的语气。如果不确定答案，建议学员聯絡客服。
EOT;
    }
}
