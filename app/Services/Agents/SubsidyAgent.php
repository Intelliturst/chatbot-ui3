<?php

namespace App\Services\Agents;

use App\Services\RAGService;

class SubsidyAgent extends BaseAgent
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
        // 检查session中是否已有雇用狀態
        $employmentStatus = $this->session->getContext('employment_status');

        if (!$employmentStatus) {
            // 尝试从訊息中判斷狀態
            $employmentStatus = $this->detectEmploymentStatus($userMessage);

            if (!$employmentStatus) {
                // 无法判斷，询问用戶
                return $this->askEmploymentStatus();
            }

            // 保存狀態
            $this->session->setContext('employment_status', $employmentStatus);
        }

        // 根据狀態提供補助資訊
        return $this->provideSubsidyInfo($employmentStatus, $userMessage);
    }

    /**
     * 檢測雇用狀態
     */
    protected function detectEmploymentStatus($message)
    {
        if (preg_match('/(在職|在職|有工作|上班|產投|产投)/ui', $message)) {
            return 'employed';
        }
        if (preg_match('/(待業|待業|失业|失業|没工作|沒工作|找工作|待轉|待转)/ui', $message)) {
            return 'unemployed';
        }
        return null;
    }

    /**
     * 询问雇用狀態
     */
    protected function askEmploymentStatus()
    {
        $this->session->setContext('pending_question', 'employment_status');

        return [
            'content' => "为了提供正确的補助資訊，請问您目前的就业状况是？\n\n📋 **補助類型說明**：\n\n**在職者補助**\n• 适用：目前有工作，投保劳保/就保\n• 補助：80%（特定身份可100%）\n• 上课：周末上课\n\n**待業者補助**\n• 适用：目前失业，待業中\n• 補助：80-100%\n• 上课：周一至周五全日制",
            'quick_options' => ['我是在職者', '我是待業者', '不确定身份']
        ];
    }

    /**
     * 提供補助資訊
     */
    protected function provideSubsidyInfo($employmentStatus, $message)
    {
        $rules = $this->ragService->getSubsidyRules($employmentStatus);

        if (!$rules) {
            return $this->errorResponse();
        }

        $typeName = $employmentStatus === 'employed' ? '在職' : '待業';

        // 检查是否询问特定身份
        if ($this->isAskingAboutSpecialIdentity($message)) {
            return $this->provideSpecialIdentityInfo($employmentStatus, $rules);
        }

        // 提供一般補助說明
        $content = "💰 **{$typeName}者補助資訊**\n\n";

        foreach ($rules['rules'] as $rule) {
            $content .= "**{$rule['title']}**\n";
            $content .= "補助比例：{$rule['subsidy_rate']}\n";
            $content .= "{$rule['description']}\n\n";

            $content .= "📌 **申請条件**：\n";
            foreach ($rule['requirements'] as $req) {
                $content .= "• {$req}\n";
            }

            if (isset($rule['special_identities'])) {
                $content .= "\n✨ **特定身份**（可享100%補助）：\n";
                foreach (array_slice($rule['special_identities'], 0, 5) as $identity) {
                    $content .= "• {$identity}\n";
                }
                if (count($rule['special_identities']) > 5) {
                    $content .= "• ...等\n";
                }
            }

            $content .= "\n" . str_repeat('-', 30) . "\n\n";
        }

        if (isset($rules['rules'][0]['note'])) {
            $content .= "⚠️ **注意事项**：\n{$rules['rules'][0]['note']}";
        }

        $quickOptions = $employmentStatus === 'employed'
            ? ['我符合特定身份吗', '如何申請補助', '查看課程', '聯絡客服']
            : ['我符合全额補助吗', '查看課程', '報名流程', '聯絡客服'];

        return [
            'content' => $content,
            'quick_options' => $quickOptions
        ];
    }

    /**
     * 检查是否询问特定身份
     */
    protected function isAskingAboutSpecialIdentity($message)
    {
        return preg_match('/(特定身份|特定身分|全额補助|全額補助|100%|低收|中低收|原住民|身心障碍|身心障礙|中高龄|中高齡|独力|獨力)/ui', $message);
    }

    /**
     * 提供特定身份資訊
     */
    protected function provideSpecialIdentityInfo($employmentStatus, $rules)
    {
        $typeName = $employmentStatus === 'employed' ? '在職' : '待業';

        // 找到特定身份規則
        $specialRule = null;
        foreach ($rules['rules'] as $rule) {
            if (isset($rule['special_identities']) || $rule['subsidy_rate'] === '100%') {
                $specialRule = $rule;
                break;
            }
        }

        if (!$specialRule) {
            return [
                'content' => "目前{$typeName}者可享基本補助。\n\n如需了解更多，請聯絡客服：03-4227723",
                'quick_options' => ['查看課程', '報名流程', '聯絡客服']
            ];
        }

        $content = "✨ **{$typeName}者特定身份補助（100%）**\n\n";

        if ($employmentStatus === 'unemployed') {
            $content .= "以下身份可享**全额補助**：\n\n";

            if (isset($specialRule['conditions'])) {
                foreach ($specialRule['conditions'] as $condition) {
                    $content .= "**{$condition['type']}**\n";
                    if (isset($condition['list'])) {
                        foreach ($condition['list'] as $item) {
                            $content .= "• {$item}\n";
                        }
                    } elseif (isset($condition['description'])) {
                        $content .= "• {$condition['description']}\n";
                    }
                    $content .= "\n";
                }
            }
        } else {
            $content .= "符合以下身份的在職者，可申請**100%補助**：\n\n";

            if (isset($specialRule['special_identities'])) {
                foreach ($specialRule['special_identities'] as $identity) {
                    $content .= "• {$identity}\n";
                }
            }
        }

        $content .= "\n📋 **需准备文件**：\n";
        if (isset($specialRule['documents_required'])) {
            foreach ($specialRule['documents_required'] as $doc) {
                $content .= "• {$doc}\n";
            }
        }

        if (isset($specialRule['note'])) {
            $content .= "\n⚠️ {$specialRule['note']}";
        }

        return [
            'content' => $content,
            'quick_options' => ['我符合', '我不符合', '查看課程', '聯絡客服']
        ];
    }

    /**
     * 獲取系統提示詞
     */
    protected function getSystemPrompt()
    {
        return <<<EOT
你是虹宇職訓的補助諮詢專員。你的職責是：
1. 協助学员了解政府補助資格
2. 說明在職/待業補助差异
3. 引导符合特定身份者申請全额補助

補助重点：
- 在職者：基本80%，特定身份100%
- 待業者：基本80%（自付20%），特定身份100%
- 特定身份包括：低收、原住民、身心障碍、中高龄等

請用繁体中文回答，保持專業、耐心的语气。
EOT;
    }
}
