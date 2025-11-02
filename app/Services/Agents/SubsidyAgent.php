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
        // 檢查session中是否已有雇用狀態
        $employmentStatus = $this->session->getContext('employment_status');

        if (!$employmentStatus) {
            // 嘗試從訊息中判斷狀態
            $employmentStatus = $this->detectEmploymentStatus($userMessage);

            if (!$employmentStatus) {
                // 無法判斷，詢問用戶
                return $this->askEmploymentStatus();
            }

            // 保存狀態
            $this->session->setContext('employment_status', $employmentStatus);
        }

        // 根據狀態提供補助資訊
        return $this->provideSubsidyInfo($employmentStatus, $userMessage);
    }

    /**
     * 檢測雇用狀態
     */
    protected function detectEmploymentStatus($message)
    {
        if (preg_match('/(在職|在職|有工作|上班|產投|產投)/ui', $message)) {
            return 'employed';
        }
        if (preg_match('/(待業|待業|失業|失業|沒工作|沒工作|找工作|待轉|待轉)/ui', $message)) {
            return 'unemployed';
        }
        return null;
    }

    /**
     * 詢問雇用狀態
     */
    protected function askEmploymentStatus()
    {
        $this->session->setContext('pending_question', 'employment_status');

        return [
            'content' => "為了提供正確的補助資訊，請問您目前的就業狀況是？\n\n📋 **補助類型說明**：\n\n**在職者補助**\n• 適用：目前有工作，投保勞保/就保\n• 補助：80%（特定身份可100%）\n• 上課：週末全天或平日晚上\n\n**待業者補助**\n• 適用：目前失業，待業中\n• 補助：80-100%\n• 上課：週一至週五全日制",
            'quick_options' => ['我是在職者', '我是待業者', '不確定身份']
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

        // 檢查是否詢問證明文件
        if ($this->isAskingAboutDocuments($message)) {
            return $this->provideDocumentInfo($employmentStatus);
        }

        // 檢查是否詢問特定身份
        if ($this->isAskingAboutSpecialIdentity($message)) {
            return $this->provideSpecialIdentityInfo($employmentStatus, $rules);
        }

        // 提供一般補助說明
        $content = "💰 **{$typeName}者補助資訊**\n\n";

        foreach ($rules['rules'] as $rule) {
            $content .= "**{$rule['title']}**\n";
            $content .= "補助比例：{$rule['subsidy_rate']}\n";
            $content .= "{$rule['description']}\n\n";

            if (isset($rule['requirements'])) {
                $content .= "📌 **申請條件**：\n";
                foreach ($rule['requirements'] as $req) {
                    $content .= "• {$req}\n";
                }
            }

            if (isset($rule['special_identities'])) {
                $content .= "\n✨ **特定身份**（可享100%補助）：\n";
                foreach (array_slice($rule['special_identities'], 0, 5) as $identity) {
                    // 支援新舊格式：陣列格式提取 name，字串格式直接使用
                    $identityName = is_array($identity) ? $identity['name'] : $identity;
                    $content .= "• {$identityName}\n";
                }
                if (count($rule['special_identities']) > 5) {
                    $content .= "• ...等\n";
                }
            }

            $content .= "\n" . str_repeat('-', 30) . "\n\n";
        }

        if (isset($rules['rules'][0]['note'])) {
            $content .= "⚠️ **注意事項**：\n{$rules['rules'][0]['note']}";
        }

        // 設置上下文，標記為補助資訊查詢
        $this->session->setContext('last_action', 'subsidy_info');

        $quickOptions = ['查看課程', '如何報名', '聯絡客服'];

        return [
            'content' => $content,
            'quick_options' => $quickOptions
        ];
    }

    /**
     * 檢查是否詢問特定身份
     */
    protected function isAskingAboutSpecialIdentity($message)
    {
        return preg_match('/(特定身份|特定身分|全額補助|全額補助|100%|低收|中低收|原住民|身心障礙|身心障礙|中高齡|中高齡|獨力|獨力)/ui', $message);
    }

    /**
     * 檢查是否詢問證明文件
     */
    protected function isAskingAboutDocuments($message)
    {
        return preg_match('/(證明|證明|文件|檔案|資料|資料|要準備|要準備|需要什麼|需要什麼|要帶什麼|要帶什麼|申請資料|申請資料|檢附|檢附)/ui', $message);
    }

    /**
     * 提供特定身份資訊
     */
    protected function provideSpecialIdentityInfo($employmentStatus, $rules)
    {
        $typeName = $employmentStatus === 'employed' ? '在職' : '待業';

        // 找到特定身份規則（100%補助）
        $specialRule = null;
        foreach ($rules['rules'] as $rule) {
            if ($rule['subsidy_rate'] === '100%') {
                $specialRule = $rule;
                break;
            }
        }

        if (!$specialRule || !isset($specialRule['special_identities'])) {
            // 設置上下文，標記為補助問題查詢
            $this->session->setContext('last_action', 'subsidy_question');

            return [
                'content' => "目前{$typeName}者可享基本補助。\n\n如需了解更多，請聯絡客服：03-4227723",
                'quick_options' => ['查看課程', '報名流程', '聯絡客服']
            ];
        }

        $content = "✨ **{$typeName}者特定身份補助（100%）**\n\n";
        $content .= "以下身份可享**全額補助**：\n\n";

        // 顯示特定身份清單（限制顯示數量避免訊息過長）
        $identities = $specialRule['special_identities'];
        $displayLimit = $employmentStatus === 'employed' ? 10 : 15;

        foreach (array_slice($identities, 0, $displayLimit) as $index => $identity) {
            $identityName = is_array($identity) ? $identity['name'] : $identity;
            $content .= ($index + 1) . ". {$identityName}\n";

            // 如果有詳細條件，顯示簡要說明
            if (is_array($identity) && isset($identity['criteria'])) {
                $criteria = $identity['criteria'];

                // 顯示年齡範圍
                if (isset($criteria['age_range'])) {
                    $content .= "   ▸ {$criteria['age_range']}\n";
                }

                // 顯示簡要描述
                if (isset($criteria['description'])) {
                    $description = $criteria['description'];
                    // 限制描述長度
                    if (mb_strlen($description) > 50) {
                        $description = mb_substr($description, 0, 47) . '...';
                    }
                    $content .= "   ▸ {$description}\n";
                }
            }

            $content .= "\n";
        }

        if (count($identities) > $displayLimit) {
            $remaining = count($identities) - $displayLimit;
            $content .= "...以及其他 {$remaining} 種身份\n\n";
        }

        $content .= "📋 **申請提醒**：\n";
        $content .= "• 請準備相關身份證明文件\n";
        $content .= "• 證明文件有效期需包含開訓日\n";
        $content .= "• 詳細文件清單請詢問「需要什麼證明文件」\n";

        if (isset($specialRule['note'])) {
            $content .= "\n⚠️ {$specialRule['note']}";
        }

        // 設置上下文，標記為補助問題查詢
        $this->session->setContext('last_action', 'subsidy_question');

        return [
            'content' => $content,
            'quick_options' => ['需要什麼證明文件', '查看課程', '報名流程', '聯絡客服']
        ];
    }

    /**
     * 提供證明文件資訊
     */
    protected function provideDocumentInfo($employmentStatus)
    {
        $typeName = $employmentStatus === 'employed' ? '在職' : '待業';
        $documents = $this->ragService->getSubsidyDocuments($employmentStatus);

        if (!$documents) {
            return [
                'content' => "抱歉，暫時無法取得{$typeName}者的證明文件資訊。\n\n如需協助，請聯絡客服：03-4227723",
                'quick_options' => ['課程列表', '補助資格', '聯絡客服']
            ];
        }

        $content = "📋 **{$typeName}者補助證明文件**\n\n";
        $content .= "根據您的身份，可能需要準備以下證明文件：\n\n";

        $identityCount = 0;
        foreach ($documents as $identityId => $docInfo) {
            $identityCount++;

            // 限制顯示前8個身份，避免訊息過長
            if ($identityCount > 8) {
                $content .= "\n⚠️ **還有更多身份類別**\n";
                $content .= "若您的身份未列在上方，或需要更詳細的說明，請聯絡客服：03-4227723\n";
                break;
            }

            $content .= "**" . ($identityCount) . ". {$docInfo['identity_name']}**\n";

            if (isset($docInfo['required_documents']) && is_array($docInfo['required_documents'])) {
                foreach ($docInfo['required_documents'] as $doc) {
                    $content .= "  • {$doc}\n";
                }
            }

            $content .= "\n";
        }

        $content .= "💡 **貼心提醒**：\n";
        $content .= "• 所有證明文件請準備影本\n";
        $content .= "• 有效期限需包含開訓日當日\n";
        $content .= "• 實際所需文件以開課單位要求為準\n";

        // 設置上下文，標記為補助問題查詢
        $this->session->setContext('last_action', 'subsidy_question');

        return [
            'content' => $content,
            'quick_options' => ['查看課程', '如何報名', '聯絡客服']
        ];
    }

    /**
     * 獲取系統提示詞
     */
    protected function getSystemPrompt()
    {
        return <<<EOT
你是虹宇職訓的補助諮詢專員。你的職責是：
1. 協助學員了解政府補助資格
2. 說明在職/待業補助差異
3. 引導符合特定身份者申請全額補助

補助重點：
- 在職者：基本80%，特定身份100%
- 待業者：基本80%（自付20%），特定身份100%
- 特定身份包括：低收、原住民、身心障礙、中高齡等

請用繁體中文回答，保持專業、耐心的語氣。
EOT;
    }
}
