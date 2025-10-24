<?php

namespace App\Services\Agents;

use App\Services\RAGService;

class EnrollmentAgent extends BaseAgent
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
        // 判斷询问在職或待業報名流程
        $courseType = $this->detectCourseType($userMessage);

        if (!$courseType) {
            // 无法判斷，询问用戶
            return $this->askCourseType();
        }

        return $this->provideEnrollmentProcess($courseType);
    }

    /**
     * 檢測課程類型
     */
    protected function detectCourseType($message)
    {
        if (preg_match('/(待業|待業|失业|失業|全日|全日制)/ui', $message)) {
            return 'unemployed';
        }
        if (preg_match('/(在職|在職|產投|产投|周末|週末)/ui', $message)) {
            return 'employed';
        }
        return null;
    }

    /**
     * 询问課程類型
     */
    protected function askCourseType()
    {
        return [
            'content' => "請问您想了解哪种課程的報名流程？\n\n📚 **課程類型**：\n\n**待業課程**\n• 全日制（周一至周五 9:00-17:00）\n• 需参加甄试\n• 政府補助80-100%\n\n**在職課程**\n• 周末上课\n• 线上報名即可\n• 结训后可申請80%補助",
            'quick_options' => ['待業課程報名', '在職課程報名']
        ];
    }

    /**
     * 提供報名流程
     */
    protected function provideEnrollmentProcess($courseType)
    {
        $processData = $this->ragService->getEnrollmentProcess($courseType);

        if (!$processData) {
            return $this->errorResponse();
        }

        $typeName = $courseType === 'unemployed' ? '待業' : '在職';
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
        $content .= "📞 **聯絡方式**\n";
        $content .= "電話：{$serviceInfo['contact']['phone']['display']}\n";
        $content .= "LINE：{$serviceInfo['contact']['line']['id']}\n";
        $content .= "地址：{$serviceInfo['contact']['address']['full']}";

        $quickOptions = $courseType === 'unemployed'
            ? ['甄试准备什么', '查看待業課程', '補助資格', '聯絡客服']
            : ['查看在職課程', '補助資格', '聯絡客服'];

        return [
            'content' => $content,
            'quick_options' => $quickOptions
        ];
    }

    /**
     * 獲取系統提示詞
     */
    protected function getSystemPrompt()
    {
        return <<<EOT
你是虹宇職訓的報名諮詢專員。你的職責是：
1. 說明報名流程
2. 回答報名相关問題
3. 引导学员完成報名

報名重点：
- 待業課程：需参加甄试，准备身分证和相关证明
- 在職課程：就业通线上報名，需缴全额学费

請用繁体中文回答，保持清晰、詳細的說明。
EOT;
    }
}
