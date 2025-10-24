<?php

namespace App\Services\Agents;

class ClassificationAgent extends BaseAgent
{
    /**
     * 處理用戶訊息
     */
    public function handle($userMessage)
    {
        // 分類用戶意圖
        $category = $this->classifyIntent($userMessage);

        // 根據分類處理
        switch ($category) {
            case 0: // 打招呼/閒聊
                return $this->handleGreeting($userMessage);

            case 1: // 課程內容查詢
            case 2: // 課程清單查詢
            case 6: // 精選課程
            case 7: // 課程搜尋
                return $this->handleCourse($userMessage);

            case 3: // 補助資格判斷
                return $this->handleSubsidy($userMessage);

            case 4: // 常見問題
                return $this->handleFAQ($userMessage);

            case 5: // 報名流程
                return $this->handleEnrollment($userMessage);

            case 8: // 真人客服
                return $this->handleHumanService($userMessage);

            case 9: // 未知/其他
                return $this->handleUnknown($userMessage);

            default:
                return $this->errorResponse();
        }
    }

    /**
     * 處理課程相關查詢
     */
    protected function handleCourse($userMessage)
    {
        $courseAgent = app(\App\Services\Agents\CourseAgent::class);
        return $courseAgent->handle($userMessage);
    }

    /**
     * 處理補助查詢
     */
    protected function handleSubsidy($userMessage)
    {
        $subsidyAgent = app(\App\Services\Agents\SubsidyAgent::class);
        return $subsidyAgent->handle($userMessage);
    }

    /**
     * 處理常見問題
     */
    protected function handleFAQ($userMessage)
    {
        $faqAgent = app(\App\Services\Agents\FAQAgent::class);
        return $faqAgent->handle($userMessage);
    }

    /**
     * 處理報名流程
     */
    protected function handleEnrollment($userMessage)
    {
        $enrollmentAgent = app(\App\Services\Agents\EnrollmentAgent::class);
        return $enrollmentAgent->handle($userMessage);
    }

    /**
     * 處理真人客服轉接
     */
    protected function handleHumanService($userMessage)
    {
        $humanServiceAgent = app(\App\Services\Agents\HumanServiceAgent::class);
        return $humanServiceAgent->handle($userMessage);
    }

    /**
     * 分類用戶意圖
     *
     * @param string $userMessage
     * @return int 0-9
     */
    protected function classifyIntent($userMessage)
    {
        $systemPrompt = $this->getClassificationPrompt();

        $result = $this->openAI->classify($userMessage, $systemPrompt);

        if ($result['success']) {
            $response = trim($result['content']);

            // 提取數字
            if (preg_match('/^(\d+)/', $response, $matches)) {
                $category = (int)$matches[1];
                if ($category >= 0 && $category <= 9) {
                    return $category;
                }
            }
        }

        // 預設為未知分類
        return 9;
    }

    /**
     * 取得分類提示詞
     */
    protected function getClassificationPrompt()
    {
        return <<<EOT
你是虹宇職訓的智能客服分類系統。請將用戶問題分類為以下類別之一，只需回覆數字：

0 - 打招呼/閒聊（例如：你好、早安、謝謝）
1 - 課程內容查詢（例如：這個課程教什麼、課程內容、上課地點、報名截止時間）
2 - 課程清單查詢（例如：有哪些課程、待業課程、在職課程、課程列表）
3 - 補助資格判斷（例如：我可以申請補助嗎、補助資格、政府補助）
4 - 常見問題（例如：如何報名、需要準備什麼、上課時間）
5 - 報名流程說明（例如：怎麼報名、報名步驟、報名方式）
6 - 精選課程查詢（例如：推薦課程、熱門課程、最新課程）
7 - 課程搜尋（例如：AI課程、行銷課程、包含關鍵字的搜尋）
8 - 真人客服轉接（例如：我要找客服、轉真人、聯絡客服）
9 - 未知/其他（無法歸類的問題）

請務必只回覆一個數字（0-9），不要有其他說明。
EOT;
    }

    /**
     * 處理打招呼
     */
    protected function handleGreeting($userMessage)
    {
        // 使用 RAGService 讀取 JSON 檔案中的回應
        $greetingData = $this->rag->getDefaultResponse('greetings');

        if ($greetingData) {
            return [
                'content' => $greetingData['response'] ?? '您好！',
                'quick_options' => $greetingData['quick_options'] ?? []
            ];
        }

        // 備用回應（如果 JSON 讀取失敗）
        return [
            'content' => "您好！我是虹宇職訓的智能客服小幫手 👋\n\n請問有什麼可以幫您的呢？",
            'quick_options' => ['查看課程清單', '補助資格確認', '如何報名', '聯絡客服']
        ];
    }

    /**
     * 處理未知問題
     */
    protected function handleUnknown($userMessage)
    {
        // 使用 OpenAI 嘗試回答
        $response = $this->generateResponse($userMessage, [
            '回答規則' => '請簡潔回答用戶問題，如果與虹宇職訓、課程、補助無關，請禮貌地引導用戶詢問相關問題。'
        ]);

        if ($response) {
            return [
                'content' => $response . "\n\n💡 如果您想了解課程或補助相關資訊，我可以為您提供更詳細的協助！",
                'quick_options' => ['查看課程清單', '補助資格確認', '常見問題']
            ];
        }

        return [
            'content' => "抱歉，我不太確定如何回答這個問題。\n\n不過，我可以協助您：\n• 查詢課程資訊\n• 了解補助資格\n• 報名流程說明\n\n請問您想了解哪方面的資訊呢？",
            'quick_options' => ['查看課程清單', '補助資格確認', '如何報名', '聯絡客服']
        ];
    }


    /**
     * 獲取系統提示詞
     */
    protected function getSystemPrompt()
    {
        return <<<EOT
你是虹宇職訓的智能客服助手。你的職責是：
1. 友善、專業地回答用戶問題
2. 提供清晰、準確的資訊
3. 引導用戶找到所需資訊

虹宇職訓提供：
- 待業者職業訓練課程（政府全額補助）
- 在職者進修課程（政府部分補助）
- AI、行銷、設計等多元課程

請用繁體中文回答，保持簡潔友善的語氣。
EOT;
    }
}
