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
        // 搜尋FAQ（整合 general_faq 和 subsidy_faq）
        $faqResults = $this->searchAllFAQs($userMessage);

        if (!empty($faqResults)) {
            // 找到相关FAQ
            return $this->provideFAQAnswer($faqResults, $userMessage);
        }

        // 没找到，使用OpenAI结合知识库回答
        return $this->provideGeneralAnswer($userMessage);
    }

    /**
     * 搜尋所有 FAQ（整合 general_faq 和 subsidy_faq）
     */
    protected function searchAllFAQs($keyword)
    {
        // 1. 搜尋 general_faq
        $generalFAQs = $this->ragService->searchFAQ($keyword);

        // 2. 搜尋 subsidy_faq
        $subsidyFAQData = $this->ragService->getSubsidyFAQ();
        $subsidyFAQs = $subsidyFAQData['faqs'] ?? [];

        // 如果有關鍵字，過濾 subsidy FAQ
        if (!empty($keyword)) {
            $subsidyFAQs = array_filter($subsidyFAQs, function($faq) use ($keyword) {
                // 搜尋問題
                if (stripos($faq['question'], $keyword) !== false) {
                    return true;
                }
                // 搜尋答案
                if (stripos($faq['answer'], $keyword) !== false) {
                    return true;
                }
                // 搜尋關鍵字
                if (isset($faq['keywords'])) {
                    foreach ($faq['keywords'] as $kw) {
                        if (stripos($kw, $keyword) !== false) {
                            return true;
                        }
                    }
                }
                return false;
            });
            $subsidyFAQs = array_values($subsidyFAQs);
        }

        // 3. 合併兩個來源
        $allFAQs = array_merge($generalFAQs, $subsidyFAQs);

        // 4. 去重（根據問題）
        $seen = [];
        $uniqueFAQs = [];
        foreach ($allFAQs as $faq) {
            $question = $faq['question'];
            if (!isset($seen[$question])) {
                $seen[$question] = true;
                $uniqueFAQs[] = $faq;
            }
        }

        // 5. 按優先級排序（如果有）
        usort($uniqueFAQs, function($a, $b) {
            $priorityA = $a['priority'] ?? 999;
            $priorityB = $b['priority'] ?? 999;
            return $priorityA <=> $priorityB;
        });

        return $uniqueFAQs;
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
                : ['課程列表', '補助資格', '聯絡客服'];

            return [
                'content' => $content,
                'quick_options' => $quickOptions
            ];
        }

        // 多个結果，列出让用戶选择
        $content = "我找到以下相關問題：\n\n";

        foreach (array_slice($faqResults, 0, 4) as $index => $faq) {
            $num = $index + 1;
            $content .= "{$num}. {$faq['question']}\n";
        }

        $content .= "\n💡 請選擇您想了解的問題，或直接描述您的問題";

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
            '上課地點' => $serviceInfo['contact']['address']['full'],
            '營業時間' => $serviceInfo['service_hours']['weekdays']
        ];

        $response = $this->generateResponse($userMessage, $context);

        if ($response) {
            return [
                'content' => $response . "\n\n如需更多協助，歡迎聯絡客服：03-4227723",
                'quick_options' => ['課程列表', '補助資格', '聯絡客服']
            ];
        }

        // 无法回答，建议常見問題或聯絡客服
        $allFAQs = $this->ragService->searchFAQ();
        $commonQuestions = array_slice(array_map(function($faq) {
            return $faq['question'];
        }, $allFAQs), 0, 4);

        return [
            'content' => "很抱歉，我可能無法完全理解您的問題。\n\n以下是一些常見問題，或許能幫到您：\n\n" . implode("\n", array_map(function($q, $i) {
                return ($i + 1) . ". {$q}";
            }, $commonQuestions, array_keys($commonQuestions))),
            'quick_options' => ['課程列表', '補助資格', '聯絡客服']
        ];
    }

    /**
     * 獲取系統提示詞
     */
    protected function getSystemPrompt()
    {
        return <<<EOT
你是虹宇職訓的客服助理。你的職責是：
1. 回答學員的一般問題
2. 提供清晰、準確的資訊
3. 必要時引導學員聯絡客服

常見問題包括：
- 如何報名課程
- 上課地點與時間
- 請假規定
- 結業證書取得

請用繁體中文回答，保持友善、耐心的語氣。如果不確定答案，建議學員聯絡客服。
EOT;
    }
}
