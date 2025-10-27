<?php

namespace App\Services\Agents;

class ClassificationAgent extends BaseAgent
{
    /**
     * 快速按鈕路由映射表
     *
     * 格式：'按鈕文字' => ['agent' => '代理名稱', 'action' => '方法名', 'type' => '類型', ...]
     */
    protected $quickButtonRoutes = [
        // Main Menu
        '課程查詢' => ['action' => 'showCourseMenu'],
        '補助諮詢' => ['action' => 'showSubsidyMenu'],
        '報名流程' => ['agent' => 'enrollment'],
        '聯絡客服' => ['agent' => 'human_service'],

        // Course Menu
        '待業課程' => ['agent' => 'course', 'type' => 'unemployed'],
        '在職課程' => ['agent' => 'course', 'type' => 'employed'],
        '熱門課程' => ['agent' => 'course', 'type' => 'featured'],
        '搜尋課程' => ['action' => 'promptCourseSearch'],
        '更多' => ['agent' => 'course', 'action' => 'pagination'],
        '查看完整內容' => ['agent' => 'course', 'action' => 'course_content'],
        '課程內容' => ['agent' => 'course', 'action' => 'course_content'],  // 向後兼容
        '課程內容詳情' => ['agent' => 'course', 'action' => 'course_content'],
        '更多課程' => ['agent' => 'course', 'type' => 'featured'],
        '查看其他課程' => ['action' => 'showCourseMenu'],
        '查看所有課程' => ['action' => 'showCourseMenu'],
        'AI課程' => ['agent' => 'course', 'keyword' => 'AI'],
        '行銷課程' => ['agent' => 'course', 'keyword' => '行銷'],
        '設計課程' => ['agent' => 'course', 'keyword' => '設計'],
        '管理課程' => ['agent' => 'course', 'keyword' => '管理'],
        '精選課程' => ['agent' => 'course', 'type' => 'featured'],

        // Course Search Keywords (簡短關鍵字 for promptCourseSearch)
        'AI' => ['agent' => 'course', 'keyword' => 'AI'],
        '行銷' => ['agent' => 'course', 'keyword' => '行銷'],
        '設計' => ['agent' => 'course', 'keyword' => '設計'],
        '程式設計' => ['agent' => 'course', 'keyword' => '程式設計'],
        '數位行銷' => ['agent' => 'course', 'keyword' => '數位行銷'],
        'Python' => ['agent' => 'course', 'keyword' => 'Python'],
        'Java' => ['agent' => 'course', 'keyword' => 'Java'],
        '管理' => ['agent' => 'course', 'keyword' => '管理'],

        // Subsidy Menu
        '我是在職者' => ['agent' => 'subsidy', 'status' => 'employed'],
        '我是待業者' => ['agent' => 'subsidy', 'status' => 'unemployed'],
        '不確定身份' => ['action' => 'showSubsidyHelp'],
        '不确定身份' => ['action' => 'showSubsidyHelp'],  // 简体兼容

        // Simplified Subsidy Buttons
        '在職者' => ['agent' => 'subsidy', 'status' => 'employed'],
        '待業者' => ['agent' => 'subsidy', 'status' => 'unemployed'],

        // Greeting Quick Options
        '查看課程清單' => ['action' => 'showCourseMenu'],
        '補助資格確認' => ['action' => 'showSubsidyMenu'],
        '常見問題' => ['action' => 'showFAQList'],
        '查看更多課程' => ['agent' => 'course', 'type' => 'featured'],
        '回到主選單' => ['action' => 'showMainMenu'],
        '聯絡真人客服' => ['agent' => 'human_service'],

        // General Quick Options (課程列表, 補助資格)
        '課程列表' => ['action' => 'showCourseMenu'],
        '補助資格' => ['action' => 'showSubsidyMenu'],
        '查看課程' => ['action' => 'showCourseMenu'],

        // Related Questions - Course（課程相關問題優先路由到 CourseAgent）
        '報名截止時間' => ['agent' => 'course'],
        '上課地點' => ['agent' => 'course'],
        '課程費用' => ['agent' => 'course'],
        '補助資訊' => ['agent' => 'subsidy'],
        '如何報名' => ['agent' => 'enrollment'],

        // Related Questions - Subsidy
        '需要什麼文件' => ['agent' => 'subsidy', 'keyword' => '證明'],
        '需要什麼證明文件' => ['agent' => 'subsidy', 'keyword' => '證明'],
        '補助多少錢' => ['agent' => 'faq', 'keyword' => '補助金額'],
        '何時撥款' => ['agent' => 'faq', 'keyword' => '撥款'],
        '申請流程' => ['agent' => 'subsidy'],

        // Related Questions - Enrollment
        '報名方式' => ['agent' => 'enrollment'],
        '需要準備什麼' => ['agent' => 'faq', 'keyword' => '準備'],
        '甄試流程' => ['agent' => 'faq', 'keyword' => '甄試'],
        '錄取通知' => ['agent' => 'faq', 'keyword' => '錄取'],
        '待業課程報名' => ['agent' => 'enrollment', 'course_type' => 'unemployed'],
        '在職課程報名' => ['agent' => 'enrollment', 'course_type' => 'employed'],
    ];

    /**
     * 處理用戶訊息
     */
    public function handle($userMessage)
    {
        $lastAction = $this->session->getContext('last_action');
        $trimmed = trim($userMessage);

        // 【優先 0】快速按鈕檢查（最高優先級）
        if ($route = $this->matchQuickButton($trimmed)) {
            return $this->routeQuickButton($route, $trimmed);
        }

        // 【優先 1】純數字 + FAQ 列表上下文
        if (preg_match('/^[0-9]+$/', $trimmed) && $lastAction === 'faq_list') {
            // 用戶選擇 FAQ 編號
            return $this->handleFAQSelection($trimmed);
        }

        // 【優先 2】純數字 + 課程上下文
        if (preg_match('/^[0-9]+$/', $trimmed) &&
            in_array($lastAction, ['course_list', 'featured_list', 'search_result'])) {
            // 直接路由到課程代理（用戶選擇課程編號）
            return $this->handleCourse($trimmed);
        }

        // 【優先 2】上下文關鍵字（更多、剩下的、同上）
        if (preg_match('/(更多|剩下|還有|繼續|同上)/ui', $trimmed)) {
            if (in_array($lastAction, ['course_list', 'search_result', 'featured_list'])) {
                // 用戶想看更多課程
                return $this->handleCourse($trimmed);
            }
            // 其他情況繼續讓 OpenAI 分類
        }

        // 【優先 2.4】補助上下文 + 文件/身份相關問題（上下文感知）
        $employmentStatus = $this->session->getContext('employment_status');
        if ($employmentStatus || in_array($lastAction, ['subsidy_info', 'subsidy_question'])) {
            // 檢查是否為補助相關問題（文件、特定身份、資格等）
            if (preg_match('/(證明|文件|資料|要準備|需要什麼|要帶什麼|申請資料|檢附|原住民|身心障礙|中高齡|低收|獨力負擔|新住民|更生人|二度就業|長期失業|身份|特定對象)/ui', $trimmed)) {
                // 有補助上下文且問題相關，直接路由到補助代理
                \Log::info('ClassificationAgent: Routing to SubsidyAgent due to subsidy context', [
                    'employment_status' => $employmentStatus,
                    'last_action' => $lastAction,
                    'message' => $trimmed
                ]);
                return $this->handleSubsidy($trimmed);
            }
        }

        // 【優先 2.5】課程上下文 + 課程相關問題（上下文感知）
        $lastCourse = $this->session->getContext('last_course');
        if ($lastCourse) {
            // 檢查是否為課程相關問題（但排除補助文件相關）
            // 避免「需要帶什麼文件」這類補助問題被誤判為課程問題
            $isCourseQuestion = preg_match('/(需要|需不需要|要不要|需具備|基礎|先備|前置|條件|資格|適合|對象|招生|甄試|內容|教什麼|學什麼|地點|在哪|費用|多少錢|時間|時數|截止|開課|報名)/ui', $trimmed);
            $isSubsidyDocument = preg_match('/(證明|文件|資料|要準備|要帶什麼|申請資料|檢附)/ui', $trimmed);

            if ($isCourseQuestion && !$isSubsidyDocument) {
                // 有課程上下文且問題相關（非補助文件），直接路由到課程代理
                return $this->handleCourse($trimmed);
            }
        }

        // 【其他情況】使用 OpenAI 分類
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
                return $this->handleUnknownFromJSON($userMessage);

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
        $mainMenu = $this->getQuickOptionsFromConfig('main_menu');
        return [
            'content' => "您好！我是虹宇職訓的智能客服小幫手 👋\n\n請問有什麼可以幫您的呢？",
            'quick_options' => $mainMenu
        ];
    }

    /**
     * 處理未知問題（先查詢 FAQ，再使用 JSON 配置）
     */
    protected function handleUnknownFromJSON($userMessage)
    {
        // 【優化 1】先嘗試從 FAQ 中查找答案
        try {
            $faqAgent = app(\App\Services\Agents\FAQAgent::class);
            $faqResult = $faqAgent->handle($userMessage);

            // 如果 FAQ 找到相關答案，直接返回
            if ($faqResult && isset($faqResult['content'])) {
                // 檢查是否為「找不到」的回應（避免循環回退）
                if (stripos($faqResult['content'], '很抱歉') === false &&
                    stripos($faqResult['content'], '無法完全理解') === false) {
                    return $faqResult;
                }
            }
        } catch (\Exception $e) {
            // FAQ 查詢失敗，繼續執行原本的回退機制
        }

        // 【優化 2】如果 FAQ 沒找到，使用原本的未知回應
        $unknownData = $this->rag->getDefaultResponse('unknown');

        if ($unknownData) {
            return [
                'content' => $unknownData['default'] ?? '抱歉，我無法理解您的問題。',
                'quick_options' => $unknownData['quick_options'] ?? []
            ];
        }

        // 備用回應（如果 JSON 讀取失敗）
        $mainMenu = $this->getQuickOptionsFromConfig('main_menu');
        return [
            'content' => "抱歉，我不太確定如何回答這個問題。\n\n不過，我可以協助您：\n• 查詢課程資訊\n• 了解補助資格\n• 報名流程說明\n\n請問您想了解哪方面的資訊呢？",
            'quick_options' => $mainMenu
        ];
    }

    /**
     * 從 button_config.json 讀取快速選單
     */
    protected function getQuickOptionsFromConfig($menuType = 'main_menu')
    {
        try {
            $menuData = $this->rag->getQuickOptions($menuType);
            if (is_array($menuData) && !empty($menuData)) {
                // 提取 label 欄位
                return array_values(array_map(function($item) {
                    return $item['label'] ?? $item;
                }, $menuData));
            }
        } catch (\Exception $e) {
            // 讀取失敗，返回預設選項
        }

        // 預設回應
        return ['課程查詢', '補助諮詢', '報名流程', '聯絡客服'];
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

    /**
     * 匹配快速按鈕
     *
     * @param string $message 用戶訊息
     * @return array|null 路由配置或 null
     */
    protected function matchQuickButton($message)
    {
        $trimmed = trim($message);

        // 精確匹配
        if (isset($this->quickButtonRoutes[$trimmed])) {
            return $this->quickButtonRoutes[$trimmed];
        }

        // 模糊匹配（去除空格、標點符號）
        $normalized = preg_replace('/[\s\p{P}]/u', '', $trimmed);

        foreach ($this->quickButtonRoutes as $button => $route) {
            $normalizedButton = preg_replace('/[\s\p{P}]/u', '', $button);
            if ($normalized === $normalizedButton) {
                return $route;
            }
        }

        return null;
    }

    /**
     * 路由快速按鈕到對應處理
     *
     * @param array $route 路由配置
     * @param string $message 原始訊息
     * @return array
     */
    protected function routeQuickButton($route, $message)
    {
        // 如果有 action，調用本地方法
        if (isset($route['action'])) {
            $action = $route['action'];
            if (method_exists($this, $action)) {
                return $this->$action();
            }
        }

        // 如果有 agent，路由到對應代理
        if (isset($route['agent'])) {
            switch ($route['agent']) {
                case 'course':
                    return $this->routeToCourseAgent($route, $message);

                case 'subsidy':
                    return $this->routeToSubsidyAgent($route, $message);

                case 'faq':
                    return $this->routeToFAQAgent($route, $message);

                case 'enrollment':
                    return $this->handleEnrollment($message);

                case 'human_service':
                    return $this->handleHumanService($message);
            }
        }

        // 備用：返回錯誤
        return $this->errorResponse();
    }

    /**
     * 路由到課程代理
     */
    protected function routeToCourseAgent($route, $message)
    {
        $courseAgent = app(\App\Services\Agents\CourseAgent::class);

        // 如果有 action，直接調用對應方法
        if (isset($route['action'])) {
            switch ($route['action']) {
                case 'course_content':
                    // 查看完整課程內容
                    return $courseAgent->handleCourseContent();

                case 'pagination':
                    // 分頁（更多課程）
                    return $courseAgent->handlePagination();
            }
        }

        // 如果有 keyword，使用 keyword 搜尋課程
        if (isset($route['keyword'])) {
            return $courseAgent->handle($route['keyword']);
        }

        // 根據 type 設定 session 上下文或修改訊息
        if (isset($route['type'])) {
            switch ($route['type']) {
                case 'unemployed':
                    return $courseAgent->handle('待業課程');

                case 'employed':
                    return $courseAgent->handle('在職課程');

                case 'featured':
                    return $courseAgent->handle('熱門課程');
            }
        }

        return $courseAgent->handle($message);
    }

    /**
     * 路由到補助代理
     */
    protected function routeToSubsidyAgent($route, $message)
    {
        $subsidyAgent = app(\App\Services\Agents\SubsidyAgent::class);

        // 如果有 status，設定 session
        if (isset($route['status'])) {
            $this->session->setContext('employment_status', $route['status']);
        }

        // 如果有 keyword，用於證明文件查詢
        if (isset($route['keyword'])) {
            return $subsidyAgent->handle($route['keyword']);
        }

        return $subsidyAgent->handle($message);
    }

    /**
     * 路由到 FAQ 代理
     */
    protected function routeToFAQAgent($route, $message)
    {
        $faqAgent = app(\App\Services\Agents\FAQAgent::class);

        // 如果有 keyword，使用關鍵字搜尋
        if (isset($route['keyword'])) {
            return $faqAgent->handle($route['keyword']);
        }

        return $faqAgent->handle($message);
    }

    /**
     * 顯示課程選單
     */
    protected function showCourseMenu()
    {
        $courseMenu = $this->getQuickOptionsFromConfig('course_menu');

        return [
            'content' => "📚 **課程查詢**\n\n虹宇職訓提供多元化的職業訓練課程，包括：\n\n• **待業課程**：適合目前待業或失業者，政府提供80-100%補助\n• **在職課程**：適合在職勞工進修，政府補助80-100%\n• **熱門課程**：查看最受歡迎的精選課程\n\n請選擇您想查看的課程類型：",
            'quick_options' => $courseMenu
        ];
    }

    /**
     * 顯示補助選單
     */
    protected function showSubsidyMenu()
    {
        $subsidyMenu = $this->getQuickOptionsFromConfig('subsidy_menu');

        return [
            'content' => "💰 **補助諮詢**\n\n為了提供正確的補助資訊，請問您目前的就業狀況是？\n\n📋 **補助類型說明**：\n\n**在職者補助**\n• 適用：目前有工作，投保勞保/就保\n• 補助：80%（特定身份可100%）\n• 上課：週末上課\n\n**待業者補助**\n• 適用：目前失業，待業中\n• 補助：80-100%\n• 上課：週一至週五全日制",
            'quick_options' => $subsidyMenu
        ];
    }

    /**
     * 顯示主選單
     */
    protected function showMainMenu()
    {
        $mainMenu = $this->getQuickOptionsFromConfig('main_menu');

        return [
            'content' => "🏠 **主選單**\n\n您好！我是虹宇職訓的智能客服小幫手 👋\n\n我可以協助您：\n• 📚 查詢課程資訊\n• 💰 了解補助資格\n• 📝 報名流程說明\n• ☎️ 聯絡真人客服\n\n請問有什麼可以幫您的呢？",
            'quick_options' => $mainMenu
        ];
    }

    /**
     * 顯示補助身份判斷引導
     */
    protected function showSubsidyHelp()
    {
        return [
            'content' => "💡 **如何判斷您的就業身份**\n\n**在職者**\n✅ 目前有工作\n✅ 有投保勞保、就保、職災保或農保\n✅ 課程通常在週末上課\n\n**待業者**\n✅ 目前沒有工作\n✅ 正在找工作或待業中\n✅ 課程通常是全日制（週一至週五）\n\n**還是不確定？**\n您可以：\n1. 聯絡客服：03-4227723\n2. LINE：@ouy9482x\n3. 我們會協助您判斷適合的補助類型",
            'quick_options' => ['我是在職者', '我是待業者', '聯絡客服']
        ];
    }

    /**
     * 顯示常見問題列表
     */
    protected function showFAQList()
    {
        // 從 RAG 服務獲取所有 FAQ
        $allFAQs = $this->rag->searchFAQ();

        // 取前 8 個常見問題
        $topFAQs = array_slice($allFAQs, 0, 8);

        $content = "❓ **常見問題**\n\n以下是常見的問題，請選擇您想了解的：\n\n";

        foreach ($topFAQs as $index => $faq) {
            $num = $index + 1;
            $content .= "{$num}. {$faq['question']}\n";
        }

        $content .= "\n💡 您也可以直接輸入您的問題，我會為您查找答案。";

        // 將 FAQ 結果緩存到 session，供後續選擇使用
        $this->session->setContext('faq_results', $topFAQs);
        $this->session->setContext('last_action', 'faq_list');

        return [
            'content' => $content,
            'quick_options' => array_map(function($i) {
                return (string)($i + 1);
            }, range(0, min(7, count($topFAQs) - 1)))
        ];
    }

    /**
     * 處理 FAQ 選擇
     */
    protected function handleFAQSelection($number)
    {
        $faqResults = $this->session->getContext('faq_results');

        if (!$faqResults) {
            // 如果沒有緩存的 FAQ，重新顯示列表
            return $this->showFAQList();
        }

        $index = intval($number) - 1;

        if ($index < 0 || $index >= count($faqResults)) {
            return [
                'content' => "❌ 選擇的編號超出範圍，請重新選擇。",
                'quick_options' => ['常見問題', '課程列表', '聯絡客服']
            ];
        }

        $faq = $faqResults[$index];

        $content = "**{$faq['question']}**\n\n";
        $content .= $faq['answer'];

        // 清除 FAQ 上下文
        $this->session->setContext('last_action', null);
        $this->session->setContext('faq_results', null);

        // 從答案中提取相關快速按鈕（如果有的話）
        $quickOptions = ['課程列表', '補助資格', '聯絡客服'];

        // 如果答案中提到特定主題，添加相關按鈕
        if (stripos($faq['answer'], '課程') !== false) {
            $quickOptions = ['查看課程', '補助資格', '常見問題'];
        } elseif (stripos($faq['answer'], '補助') !== false || stripos($faq['answer'], '補貼') !== false) {
            $quickOptions = ['補助資格', '查看課程', '常見問題'];
        } elseif (stripos($faq['answer'], '報名') !== false) {
            $quickOptions = ['報名流程', '查看課程', '常見問題'];
        }

        return [
            'content' => $content,
            'quick_options' => $quickOptions
        ];
    }

    /**
     * 提示用戶輸入課程搜尋關鍵字
     */
    protected function promptCourseSearch()
    {
        $this->session->setContext('last_action', 'prompt_search');

        return [
            'content' => "🔍 **課程搜尋**\n\n請輸入您想搜尋的關鍵字，或點選下方熱門類別：",
            'quick_options' => ['AI', '行銷', '設計', 'Python']
        ];
    }
}
