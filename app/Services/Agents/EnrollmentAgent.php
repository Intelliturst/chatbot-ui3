<?php

namespace App\Services\Agents;

use App\Services\RAGService;

class EnrollmentAgent extends BaseAgent
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
        // 判断询问在职或待业报名流程
        $courseType = $this->detectCourseType($userMessage);

        if (!$courseType) {
            // 无法判断，询问用户
            return $this->askCourseType();
        }

        return $this->provideEnrollmentProcess($courseType);
    }

    /**
     * 检测课程类型
     */
    protected function detectCourseType($message)
    {
        if (preg_match('/(待业|待業|失业|失業|全日|全日制)/ui', $message)) {
            return 'unemployed';
        }
        if (preg_match('/(在职|在職|產投|产投|周末|週末)/ui', $message)) {
            return 'employed';
        }
        return null;
    }

    /**
     * 询问课程类型
     */
    protected function askCourseType()
    {
        return [
            'content' => "请问您想了解哪种课程的报名流程？\n\n📚 **课程类型**：\n\n**待业课程**\n• 全日制（周一至周五 9:00-17:00）\n• 需参加甄试\n• 政府补助80-100%\n\n**在职课程**\n• 周末上课\n• 线上报名即可\n• 结训后可申请80%补助",
            'quick_options' => ['待业课程报名', '在职课程报名']
        ];
    }

    /**
     * 提供报名流程
     */
    protected function provideEnrollmentProcess($courseType)
    {
        $processData = $this->ragService->getEnrollmentProcess($courseType);

        if (!$processData) {
            return $this->errorResponse();
        }

        $typeName = $courseType === 'unemployed' ? '待业' : '在职';
        $content = "📝 **{$processData['title']}**\n\n";

        foreach ($processData['steps'] as $step) {
            $content .= "**步骤 {$step['step']}：{$step['title']}**\n";
            $content .= "{$step['description']}\n";

            if (isset($step['url'])) {
                $content .= "🔗 {$step['url']}\n";
            }

            if (isset($step['documents']) && !empty($step['documents'])) {
                $content .= "📋 需准备：\n";
                foreach ($step['documents'] as $doc) {
                    $content .= "  • {$doc}\n";
                }
            }

            if (isset($step['note'])) {
                $content .= "⚠️ {$step['note']}\n";
            }

            if (isset($step['deadline'])) {
                $content .= "⏰ {$step['deadline']}\n";
            }

            $content .= "\n";
        }

        $serviceInfo = $this->ragService->getServiceInfo();
        $content .= "📞 **联络方式**\n";
        $content .= "电话：{$serviceInfo['contact']['phone']['display']}\n";
        $content .= "LINE：{$serviceInfo['contact']['line']['id']}\n";
        $content .= "地址：{$serviceInfo['contact']['address']['full']}";

        $quickOptions = $courseType === 'unemployed'
            ? ['甄试准备什么', '查看待业课程', '补助资格', '联络客服']
            : ['查看在职课程', '补助资格', '联络客服'];

        return [
            'content' => $content,
            'quick_options' => $quickOptions
        ];
    }

    /**
     * 获取系统提示词
     */
    protected function getSystemPrompt()
    {
        return <<<EOT
你是虹宇职训的报名咨询专员。你的职责是：
1. 说明报名流程
2. 回答报名相关问题
3. 引导学员完成报名

报名重点：
- 待业课程：需参加甄试，准备身分证和相关证明
- 在职课程：就业通线上报名，需缴全额学费

请用繁体中文回答，保持清晰、详细的说明。
EOT;
    }
}
