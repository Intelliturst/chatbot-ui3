<?php

namespace App\Services\Agents;

use App\Services\RAGService;

class SubsidyAgent extends BaseAgent
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
        // 检查session中是否已有雇用状态
        $employmentStatus = $this->session->getContext('employment_status');

        if (!$employmentStatus) {
            // 尝试从消息中判断状态
            $employmentStatus = $this->detectEmploymentStatus($userMessage);

            if (!$employmentStatus) {
                // 无法判断，询问用户
                return $this->askEmploymentStatus();
            }

            // 保存状态
            $this->session->setContext('employment_status', $employmentStatus);
        }

        // 根据状态提供补助资讯
        return $this->provideSubsidyInfo($employmentStatus, $userMessage);
    }

    /**
     * 检测雇用状态
     */
    protected function detectEmploymentStatus($message)
    {
        if (preg_match('/(在职|在職|有工作|上班|產投|产投)/ui', $message)) {
            return 'employed';
        }
        if (preg_match('/(待业|待業|失业|失業|没工作|沒工作|找工作|待轉|待转)/ui', $message)) {
            return 'unemployed';
        }
        return null;
    }

    /**
     * 询问雇用状态
     */
    protected function askEmploymentStatus()
    {
        $this->session->setContext('pending_question', 'employment_status');

        return [
            'content' => "为了提供正确的补助资讯，请问您目前的就业状况是？\n\n📋 **补助类型说明**：\n\n**在职者补助**\n• 适用：目前有工作，投保劳保/就保\n• 补助：80%（特定身份可100%）\n• 上课：周末上课\n\n**待业者补助**\n• 适用：目前失业，待业中\n• 补助：80-100%\n• 上课：周一至周五全日制",
            'quick_options' => ['我是在职者', '我是待业者', '不确定身份']
        ];
    }

    /**
     * 提供补助资讯
     */
    protected function provideSubsidyInfo($employmentStatus, $message)
    {
        $rules = $this->ragService->getSubsidyRules($employmentStatus);

        if (!$rules) {
            return $this->errorResponse();
        }

        $typeName = $employmentStatus === 'employed' ? '在职' : '待业';

        // 检查是否询问特定身份
        if ($this->isAskingAboutSpecialIdentity($message)) {
            return $this->provideSpecialIdentityInfo($employmentStatus, $rules);
        }

        // 提供一般补助说明
        $content = "💰 **{$typeName}者补助资讯**\n\n";

        foreach ($rules['rules'] as $rule) {
            $content .= "**{$rule['title']}**\n";
            $content .= "补助比例：{$rule['subsidy_rate']}\n";
            $content .= "{$rule['description']}\n\n";

            $content .= "📌 **申请条件**：\n";
            foreach ($rule['requirements'] as $req) {
                $content .= "• {$req}\n";
            }

            if (isset($rule['special_identities'])) {
                $content .= "\n✨ **特定身份**（可享100%补助）：\n";
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
            ? ['我符合特定身份吗', '如何申请补助', '查看课程', '联络客服']
            : ['我符合全额补助吗', '查看课程', '报名流程', '联络客服'];

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
        return preg_match('/(特定身份|特定身分|全额补助|全額補助|100%|低收|中低收|原住民|身心障碍|身心障礙|中高龄|中高齡|独力|獨力)/ui', $message);
    }

    /**
     * 提供特定身份资讯
     */
    protected function provideSpecialIdentityInfo($employmentStatus, $rules)
    {
        $typeName = $employmentStatus === 'employed' ? '在职' : '待业';

        // 找到特定身份规则
        $specialRule = null;
        foreach ($rules['rules'] as $rule) {
            if (isset($rule['special_identities']) || $rule['subsidy_rate'] === '100%') {
                $specialRule = $rule;
                break;
            }
        }

        if (!$specialRule) {
            return [
                'content' => "目前{$typeName}者可享基本补助。\n\n如需了解更多，请联络客服：03-4227723",
                'quick_options' => ['查看课程', '报名流程', '联络客服']
            ];
        }

        $content = "✨ **{$typeName}者特定身份补助（100%）**\n\n";

        if ($employmentStatus === 'unemployed') {
            $content .= "以下身份可享**全额补助**：\n\n";

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
            $content .= "符合以下身份的在职者，可申请**100%补助**：\n\n";

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
            'quick_options' => ['我符合', '我不符合', '查看课程', '联络客服']
        ];
    }

    /**
     * 获取系统提示词
     */
    protected function getSystemPrompt()
    {
        return <<<EOT
你是虹宇职训的补助咨询专员。你的职责是：
1. 协助学员了解政府补助资格
2. 说明在职/待业补助差异
3. 引导符合特定身份者申请全额补助

补助重点：
- 在职者：基本80%，特定身份100%
- 待业者：基本80%（自付20%），特定身份100%
- 特定身份包括：低收、原住民、身心障碍、中高龄等

请用繁体中文回答，保持专业、耐心的语气。
EOT;
    }
}
