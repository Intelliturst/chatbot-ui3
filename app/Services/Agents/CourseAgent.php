<?php

namespace App\Services\Agents;

use App\Services\RAGService;

class CourseAgent extends BaseAgent
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
        // 判斷查詢類型
        $queryType = $this->detectQueryType($userMessage);

        switch ($queryType) {
            case 'pagination':
                return $this->handlePagination();

            case 'list_unemployed':
                return $this->handleCourseList('unemployed');

            case 'list_employed':
                return $this->handleCourseList('employed');

            case 'featured':
                return $this->handleFeaturedCourses();

            case 'search':
                return $this->handleCourseSearch($userMessage);

            case 'specific':
                return $this->handleSpecificCourse($userMessage);

            default:
                return $this->handleGeneralInquiry($userMessage);
        }
    }

    /**
     * 檢測查詢類型（優化版：先判斷用戶意圖，避免過早搜尋）
     */
    protected function detectQueryType($message)
    {
        // 優先檢測分頁請求（更多、剩下的）
        if (preg_match('/(更多|剩下|還有|繼續)/ui', $message)) {
            $lastAction = $this->session->getContext('last_action');
            if (in_array($lastAction, ['course_list', 'search_result', 'featured_list'])) {
                return 'pagination';
            }
        }

        // 【優先級 1】明確的類型查詢（待業/在職）
        if (preg_match('/(待業|失業).*課程|課程.*(待業|失業)/ui', $message)) {
            return 'list_unemployed';
        }
        if (preg_match('/(在職|產投).*課程|課程.*(在職)/ui', $message)) {
            return 'list_employed';
        }

        // 【優先級 2】精選課程
        if (preg_match('/(精選|熱門|推薦)/ui', $message)) {
            return 'featured';
        }

        // 【優先級 3】課程編號查詢
        if (preg_match('/課程.*[0-9]+|[0-9]+.*課程|編號/ui', $message)) {
            return 'specific';
        }
        // 純數字輸入 - 檢查 Session 上下文
        if (preg_match('/^[0-9]+$/', trim($message))) {
            $lastAction = $this->session->getContext('last_action');
            if (in_array($lastAction, ['course_list', 'featured_list', 'search_result'])) {
                return 'specific';
            }
        }

        // 【優先級 4】一般性清單查詢（引導用戶選擇類型）
        // 這些關鍵字應該引導用戶，而不是直接搜尋
        if (preg_match('/(課程清單|課程列表|有哪些課程|所有課程|課程有什麼|查看課程|顯示課程)/ui', $message)) {
            return 'general';  // 引導用戶選擇待業/在職
        }

        // 【優先級 5】課程搜尋（必須有具體關鍵字）
        // 更嚴格的匹配：避免把「查看」、「清單」當作搜尋
        if (preg_match('/(搜尋|搜索).*(課程)/ui', $message)) {
            return 'search';
        }
        // 或者：明確包含課程領域關鍵字（AI、行銷、設計等）
        if (preg_match('/(AI|人工智慧|行銷|設計|程式|Python|Java|管理|UI|UX|數位|影片|剪輯|Excel|平面|網頁|前端|後端|資料|大數據).*(課程)/ui', $message)) {
            return 'search';
        }

        // 【預設】一般諮詢（引導用戶）
        return 'general';
    }

    /**
     * 處理課程清單查詢
     */
    protected function handleCourseList($type)
    {
        $courses = $this->ragService->queryCourses(['type' => $type]);
        $typeName = $type === 'unemployed' ? '待業' : '在職';

        if (empty($courses)) {
            return [
                'content' => "目前沒有{$typeName}課程資料。",
                'quick_options' => ['查看其他課程', '聯絡客服']
            ];
        }

        // 保存課程列表到 Session
        $this->session->setContext('current_course_list', $courses);
        $this->session->setContext('display_offset', 0);  // 重置 offset
        $this->session->setContext('last_action', 'course_list');

        // 使用統一的渲染方法
        return $this->renderCoursePage($courses, 0, $typeName . '課程清單');
    }

    /**
     * 處理精選課程
     */
    protected function handleFeaturedCourses()
    {
        $courses = $this->ragService->queryCourses(['featured' => true]);

        if (empty($courses)) {
            return [
                'content' => "目前沒有精選課程。",
                'quick_options' => ['查看所有課程', '聯絡客服']
            ];
        }

        // 保存課程列表到 Session
        $this->session->setContext('current_course_list', $courses);
        $this->session->setContext('display_offset', 0);  // 重置 offset
        $this->session->setContext('last_action', 'featured_list');

        // 使用統一的渲染方法
        return $this->renderCoursePage($courses, 0, '精選熱門課程', true);
    }

    /**
     * 處理課程搜尋
     */
    protected function handleCourseSearch($message)
    {
        // 提取關鍵字
        $keyword = $this->extractKeyword($message);

        if (empty($keyword)) {
            return [
                'content' => "請告訴我您想搜尋什麼課程？\n\n例如：AI課程、行銷課程、Python課程等",
                'quick_options' => ['AI課程', '行銷課程', '設計課程', '管理課程']
            ];
        }

        $courses = $this->ragService->queryCourses(['keyword' => $keyword]);

        if (empty($courses)) {
            return [
                'content' => "很抱歉，找不到與「{$keyword}」相關的課程。\n\n您可以：\n• 嘗試其他關鍵字\n• 查看所有課程清單\n• 聯絡客服詢問",
                'quick_options' => ['待業課程', '在職課程', '精選課程', '聯絡客服']
            ];
        }

        // 保存課程列表和搜尋關鍵字到 Session
        $this->session->setContext('current_course_list', $courses);
        $this->session->setContext('display_offset', 0);  // 重置 offset
        $this->session->setContext('search_keyword', $keyword);
        $this->session->setContext('last_action', 'search_result');

        // 使用統一的渲染方法
        return $this->renderCoursePage($courses, 0, "搜尋結果：{$keyword}");
    }

    /**
     * 提取搜尋關鍵字
     */
    protected function extractKeyword($message)
    {
        // 移除常见的查詢词
        $cleanMessage = preg_replace('/(我想|想要|想学|想學|搜尋|搜尋|查詢|查詢|課程|課程|有沒有|有沒有|找)/ui', '', $message);
        $cleanMessage = trim($cleanMessage);

        return $cleanMessage;
    }

    /**
     * 處理特定課程查詢
     */
    protected function handleSpecificCourse($message)
    {
        // 提取課程編號
        preg_match('/[0-9]+/', $message, $matches);

        if (empty($matches)) {
            return $this->handleGeneralInquiry($message);
        }

        $number = (int)$matches[0];

        // 【唯一正確方式】使用全局編號系統
        $courseId = $this->ragService->getCourseIdByNumber($number);

        if (!$courseId) {
            return [
                'content' => "找不到編號 {$number} 的課程。\n\n請輸入正確的課程編號，或查看課程清單。",
                'quick_options' => ['待業課程', '在職課程', '精選課程']
            ];
        }

        $course = $this->ragService->getCourseById($courseId);

        if (!$course) {
            return [
                'content' => "無法載入課程資料，請稍後再試。",
                'quick_options' => ['聯絡客服']
            ];
        }

        return $this->formatCourseDetail($course);
    }

    /**
     * 格式化課程詳情
     */
    protected function formatCourseDetail($course)
    {
        $typeName = $course['type'] === 'unemployed' ? '待業' : '在職';
        $featured = isset($course['featured']) && $course['featured'] ? '⭐ ' : '';

        $content = "📚 **{$featured}{$course['course_name']}**\n\n";
        $content .= "**課程類型**：{$typeName}課程\n\n";

        // 時間資訊
        $content .= "**⏰ 時間資訊**\n";
        $content .= "• 總時數：{$course['schedule']['total_hours']}小時\n";
        $content .= "• 上課時間：{$course['schedule']['class_time']}\n";

        if (isset($course['schedule']['start_date'])) {
            $content .= "• 開課日期：{$course['schedule']['start_date']}\n";
        }
        if (isset($course['schedule']['enrollment_deadline'])) {
            $content .= "• 報名截止：{$course['schedule']['enrollment_deadline']}\n";
        }
        $content .= "\n";

        // 費用資訊
        $content .= "**💰 費用資訊**\n";
        $content .= "• {$course['fee']['amount']}\n";
        if (isset($course['fee']['note'])) {
            $content .= "• {$course['fee']['note']}\n";
        }
        $content .= "\n";

        // 上課地點
        $content .= "**📍 上課地點**\n";
        $content .= "{$course['location']['address']}\n\n";

        // 課程內容
        if (isset($course['content'])) {
            $contentPreview = mb_substr($course['content'], 0, 150);
            if (mb_strlen($course['content']) > 150) {
                $contentPreview .= '...';
            }
            $content .= "**📖 課程內容**\n{$contentPreview}\n\n";
        }

        $content .= "🔗 詳細資訊：{$course['url']}";

        $quickOptions = $course['related_questions'] ?? ['補助資格', '如何報名', '更多課程'];

        return [
            'content' => $content,
            'quick_options' => $quickOptions
        ];
    }

    /**
     * 處理一般課程諮詢（優化版：更友善地引導用戶）
     */
    protected function handleGeneralInquiry($message)
    {
        // 檢查是否為課程清單相關查詢
        if (preg_match('/(課程清單|課程列表|有哪些課程|查看課程|顯示課程)/ui', $message)) {
            return [
                'content' => "好的！虹宇職訓提供以下兩類課程：\n\n📚 **待業課程**\n• 政府全額或部分補助\n• 全日制密集訓練\n• 適合待業、轉職者\n\n💼 **在職課程**\n• 週末上課，不影響工作\n• 結訓後可申請80%補助\n• 適合在職進修\n\n請問您想查看哪一類課程呢？",
                'quick_options' => ['待業課程', '在職課程', '精選課程']
            ];
        }

        // 其他一般諮詢使用 OpenAI 回答
        $context = [
            '課程資訊' => '虹宇職訓提供待業和在職兩類課程，涵蓋AI、程式設計、行銷、設計等領域。'
        ];

        $response = $this->generateResponse($message, $context);

        if ($response) {
            return [
                'content' => $response,
                'quick_options' => ['待業課程', '在職課程', '精選課程']
            ];
        }

        // 預設回應
        return [
            'content' => "我可以協助您：\n\n1️⃣ 查看待業課程清單\n2️⃣ 查看在職課程清單\n3️⃣ 查看精選課程\n4️⃣ 搜尋特定課程（如：AI課程、行銷課程）\n\n請問您想了解什麼呢？",
            'quick_options' => ['待業課程', '在職課程', '精選課程']
        ];
    }

    /**
     * 統一的課程頁面渲染（使用全局編號）
     */
    protected function renderCoursePage($courses, $offset = 0, $title = '課程清單', $showFeatured = false)
    {
        $pageSize = 5;
        $totalCourses = count($courses);
        $coursesToShow = array_slice($courses, $offset, $pageSize);

        // 【DEBUG】記錄渲染資訊
        \Log::info('CourseAgent::renderCoursePage', [
            'offset' => $offset,
            'total_courses' => $totalCourses,
            'page_size' => $pageSize,
            'courses_to_show_count' => count($coursesToShow),
            'course_ids' => array_column($coursesToShow, 'id')
        ]);

        $content = "📚 **{$title}**\n\n";
        $content .= "找到 " . $totalCourses . " 門課程";

        if ($totalCourses > $pageSize) {
            $currentEnd = min($offset + $pageSize, $totalCourses);
            $content .= "（顯示 " . ($offset + 1) . "-{$currentEnd} 筆）";
        }
        $content .= "：\n\n";

        $globalNumbers = []; // 【DEBUG】記錄全局編號
        foreach ($coursesToShow as $course) {
            // 使用全局編號（從 course_mapping.json）
            $globalNum = $this->getGlobalNumber($course['id']);

            // 【DEBUG】記錄編號映射
            $globalNumbers[] = [
                'course_id' => $course['id'],
                'global_num' => $globalNum,
                'course_name' => $course['course_name']
            ];

            if ($globalNum === null) {
                \Log::warning('CourseAgent: Global number not found', [
                    'course_id' => $course['id'],
                    'course_name' => $course['course_name']
                ]);
                // 如果找不到全局編號，跳過這門課程
                continue;
            }

            $featured = isset($course['featured']) && $course['featured'] ? '⭐ ' : '';
            $typeName = $course['type'] === 'unemployed' ? '待業' : '在職';

            $content .= "{$globalNum}. {$featured}{$course['course_name']}";
            if ($showFeatured) {
                $content .= " ({$typeName})";
            }
            $content .= "\n";
            $content .= "   時數：{$course['schedule']['total_hours']}小時\n";

            if (isset($course['schedule']['start_date'])) {
                $content .= "   開課：{$course['schedule']['start_date']}\n";
            }

            if ($showFeatured && isset($course['keywords'])) {
                $content .= "   特色：" . implode('、', array_slice($course['keywords'], 0, 3)) . "\n";
            }

            $content .= "\n";
        }

        // 【DEBUG】記錄所有編號
        \Log::info('CourseAgent: Global numbers used', $globalNumbers);

        // 提示文字
        if ($offset + $pageSize < $totalCourses) {
            $remaining = $totalCourses - ($offset + $pageSize);
            $content .= "...還有 {$remaining} 門課程（輸入「更多」繼續查看）\n\n";
        }

        $content .= "💡 請輸入課程編號查看詳情";

        // 更新 Session offset
        $this->session->setContext('display_offset', $offset);

        // 【DEBUG】記錄 Session 狀態
        \Log::info('CourseAgent: Session updated', [
            'display_offset' => $offset,
            'last_action' => $this->session->getContext('last_action')
        ]);

        return [
            'content' => $content,
            'quick_options' => ['補助資格', '如何報名', '聯絡客服']
        ];
    }

    /**
     * 根據 course_id 查找全局編號
     * 從 course_mapping.json 的 number_to_id 反向查找
     */
    protected function getGlobalNumber($courseId)
    {
        try {
            $mapping = $this->ragService->getCourseMapping();
            $numberToId = $mapping['number_to_id'] ?? [];

            foreach ($numberToId as $num => $id) {
                if ($id == $courseId) {
                    return (int)$num;
                }
            }
        } catch (\Exception $e) {
            // 如果讀取失敗，返回 null
            return null;
        }

        return null;
    }

    /**
     * 處理分頁請求（更多、剩下的課程）
     */
    protected function handlePagination()
    {
        $courseList = $this->session->getContext('current_course_list');
        $currentOffset = $this->session->getContext('display_offset', 0);
        $lastAction = $this->session->getContext('last_action');

        // 【DEBUG】記錄分頁請求
        \Log::info('CourseAgent::handlePagination', [
            'course_list_count' => $courseList ? count($courseList) : 0,
            'current_offset' => $currentOffset,
            'last_action' => $lastAction
        ]);

        if (empty($courseList)) {
            \Log::warning('CourseAgent: No course list in session');
            return [
                'content' => "沒有找到課程列表，請重新查詢。",
                'quick_options' => ['待業課程', '在職課程', '精選課程']
            ];
        }

        // 計算新的 offset
        $newOffset = $currentOffset + 5;

        \Log::info('CourseAgent: Pagination offset', [
            'old_offset' => $currentOffset,
            'new_offset' => $newOffset,
            'total_courses' => count($courseList)
        ]);

        if ($newOffset >= count($courseList)) {
            \Log::info('CourseAgent: Reached end of list');
            return [
                'content' => "已經顯示所有課程了！\n\n💡 您可以重新搜尋或查看其他類型的課程。",
                'quick_options' => ['待業課程', '在職課程', '精選課程']
            ];
        }

        // 根據 last_action 決定標題
        $title = '課程清單';
        if ($lastAction === 'search_result') {
            $keyword = $this->session->getContext('search_keyword', '');
            $title = "搜尋結果：{$keyword}";
        } elseif ($lastAction === 'featured_list') {
            $title = '精選熱門課程';
        }

        // 渲染下一頁
        return $this->renderCoursePage($courseList, $newOffset, $title);
    }

    /**
     * 獲取系統提示詞
     */
    protected function getSystemPrompt()
    {
        return <<<EOT
你是虹宇職訓的課程諮詢專員。你的職責是：
1. 協助學員了解課程資訊
2. 提供清晰、準確的課程說明
3. 引導學員找到適合的課程

虹宇職訓課程特色：
- 待業課程：政府全額或部分補助，全日制密集訓練
- 在職課程：週末上課，結訓後可申請80%補助
- 課程領域：AI、程式設計、行銷、設計、管理等

請用繁體中文回答，保持專業友善的語氣。
EOT;
    }
}
