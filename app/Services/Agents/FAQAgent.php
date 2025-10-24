<?php

namespace App\Services\Agents;

use App\Services\RAGService;

class FAQAgent extends BaseAgent
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
        // 搜索FAQ
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
            // 只有一个结果，直接回答
            $faq = $faqResults[0];

            $content = "**{$faq['question']}**\n\n";
            $content .= $faq['answer'];

            $quickOptions = isset($faq['related_questions'])
                ? array_slice($faq['related_questions'], 0, 4)
                : ['其他问题', '查看课程', '补助资格', '联络客服'];

            return [
                'content' => $content,
                'quick_options' => $quickOptions
            ];
        }

        // 多个结果，列出让用户选择
        $content = "我找到以下相关问题：\n\n";

        foreach (array_slice($faqResults, 0, 4) as $index => $faq) {
            $num = $index + 1;
            $content .= "{$num}. {$faq['question']}\n";
        }

        $content .= "\n💡 请选择您想了解的问题，或直接描述您的问题";

        // 将FAQ结果缓存到session
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
        // 从知识库获取相关资讯
        $serviceInfo = $this->ragService->getServiceInfo();
        $subsidyFAQ = $this->ragService->getSubsidyFAQ();

        $context = [
            '联络资讯' => "电话：{$serviceInfo['contact']['phone']['display']}，LINE：{$serviceInfo['contact']['line']['id']}",
            '上课地点' => $serviceInfo['contact']['address']['full'],
            '营业时间' => $serviceInfo['service_hours']['weekdays']
        ];

        $response = $this->generateResponse($userMessage, $context);

        if ($response) {
            return [
                'content' => $response . "\n\n如需更多协助，欢迎联络客服：03-4227723",
                'quick_options' => ['查看课程', '补助资格', '报名流程', '联络客服']
            ];
        }

        // 无法回答，建议常见问题或联络客服
        $allFAQs = $this->ragService->searchFAQ();
        $commonQuestions = array_slice(array_map(function($faq) {
            return $faq['question'];
        }, $allFAQs), 0, 4);

        return [
            'content' => "很抱歉，我可能无法完全理解您的问题。\n\n以下是一些常见问题，或许能帮到您：\n\n" . implode("\n", array_map(function($q, $i) {
                return ($i + 1) . ". {$q}";
            }, $commonQuestions, array_keys($commonQuestions))),
            'quick_options' => ['联络客服', '查看课程', '补助资格']
        ];
    }

    /**
     * 获取系统提示词
     */
    protected function getSystemPrompt()
    {
        return <<<EOT
你是虹宇职训的客服助理。你的职责是：
1. 回答学员的一般问题
2. 提供清晰、准确的资讯
3. 必要时引导学员联络客服

常见问题包括：
- 如何报名课程
- 上课地点与时间
- 请假规定
- 结业证书取得

请用繁体中文回答，保持友善、耐心的语气。如果不确定答案，建议学员联络客服。
EOT;
    }
}
