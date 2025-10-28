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
            case 'course_question':
                return $this->handleCourseQuestion($userMessage);

            case 'course_content':
                return $this->handleCourseContent();

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
        // 優先檢測課程相關問題（當有 last_course 上下文時）
        if (preg_match('/(報名截止|截止時間|開課日期|什麼時候開課|上課地點|在哪上課|地點在哪|課程費用|學費|費用多少|多少錢|時數|總時數|上課時間|幾點上課|需要|需不需要|要不要|需具備|基礎|先備|前置|條件|資格|適合|對象|招生|名額|人數|甄試|面試)/ui', $message)) {
            $lastCourse = $this->session->getContext('last_course');
            if ($lastCourse) {
                return 'course_question';
            }
        }

        // 優先檢測課程內容查詢（當有 last_course 上下文時）
        if (preg_match('/(課程內容|課程詳情|詳細內容|完整內容|課程介紹|教什麼|學什麼)/ui', $message)) {
            $lastCourse = $this->session->getContext('last_course');
            if ($lastCourse) {
                return 'course_content';
            }
        }

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
        // 支援「行銷課程」或單純「行銷」的搜尋
        if (preg_match('/(AI|人工智慧|行銷|設計|程式設計|程式|Python|Java|管理|UI|UX|數位行銷|數位|影片|剪輯|Excel|平面|網頁|前端|後端|資料|大數據)/ui', $message)) {
            // 排除一般性查詢詞（如「有哪些」、「查看」、「列表」等）
            if (!preg_match('/(有哪些|查看|顯示|列表|清單|所有)/ui', $message)) {
                return 'search';
            }
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
     * 處理特定課程查詢（上下文感知：支援相對/絕對編號）
     */
    protected function handleSpecificCourse($message)
    {
        // 提取課程編號
        preg_match('/[0-9]+/', $message, $matches);

        if (empty($matches)) {
            return $this->handleGeneralInquiry($message);
        }

        $number = (int)$matches[0];

        // 從 Session 中取得當前課程清單和顯示 offset
        $courseList = $this->session->getContext('current_course_list');
        $currentOffset = $this->session->getContext('display_offset', 0);

        if (empty($courseList)) {
            return [
                'content' => "請先查看課程清單，再輸入編號查詢。\n\n您可以：",
                'quick_options' => ['待業課程', '在職課程', '精選課程']
            ];
        }

        $totalCourses = count($courseList);
        $pageSize = 5;

        // 計算當前頁面顯示的範圍
        $currentPageStart = $currentOffset + 1;
        $currentPageEnd = min($currentOffset + $pageSize, $totalCourses);
        $currentPageSize = $currentPageEnd - $currentPageStart + 1;

        // 智能判斷用戶輸入的編號類型
        if ($number >= $currentPageStart && $number <= $currentPageEnd) {
            // 情況1：用戶輸入的編號在當前頁面顯示範圍內（絕對編號）
            $courseIndex = $number - 1;
        } elseif ($number >= 1 && $number <= $currentPageSize) {
            // 情況2：用戶輸入的編號在當前頁面項目數範圍內（相對編號）
            // 理解為「當前頁面的第N個項目」
            $courseIndex = $currentOffset + $number - 1;
        } else {
            // 情況3：編號超出有效範圍
            return [
                'content' => "編號 {$number} 不在當前頁面範圍內。\n\n💡 **當前頁面顯示**：編號 {$currentPageStart}-{$currentPageEnd}\n或輸入 1-{$currentPageSize} 查看當前頁面的第 N 個課程。",
                'quick_options' => $currentOffset + $pageSize < $totalCourses
                    ? ['更多', '補助資格', '聯絡客服']
                    : ['補助資格', '如何報名', '聯絡客服']
            ];
        }

        // 安全檢查：確保索引有效
        if (!isset($courseList[$courseIndex])) {
            return [
                'content' => "發生錯誤：無法找到課程資料。\n\n請重新查看課程清單。",
                'quick_options' => ['待業課程', '在職課程', '精選課程']
            ];
        }

        $course = $courseList[$courseIndex];

        return $this->formatCourseDetail($course);
    }

    /**
     * 格式化課程詳情
     */
    protected function formatCourseDetail($course)
    {
        // 保存當前課程到 Session（用於後續查詢完整內容）
        $this->session->setContext('last_course', $course);

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

        // 課程內容（預覽）
        if (isset($course['content'])) {
            $contentPreview = mb_substr($course['content'], 0, 150);
            if (mb_strlen($course['content']) > 150) {
                $contentPreview .= '...';
            }
            $content .= "**📖 課程內容**\n{$contentPreview}\n\n";
        }

        $content .= "🔗 詳細資訊：{$course['url']}";

        // 動態快速按鈕
        $quickOptions = [];

        // 如果有完整內容，添加「查看完整內容」按鈕
        if (isset($course['content']) && mb_strlen($course['content']) > 150) {
            $quickOptions[] = '查看完整內容';
        }

        // 使用標準快速按鈕（保證系統都能理解）
        $standardOptions = $this->getStandardCourseQuickOptions();
        $quickOptions = array_merge($quickOptions, $standardOptions);

        return [
            'content' => $content,
            'quick_options' => $quickOptions
        ];
    }

    /**
     * 處理課程相關問題（報名截止、上課地點、費用等）
     */
    protected function handleCourseQuestion($message)
    {
        $course = $this->session->getContext('last_course');

        if (!$course) {
            return [
                'content' => "請先選擇一門課程，再詢問課程相關問題。\n\n您可以：",
                'quick_options' => ['待業課程', '在職課程', '精選課程']
            ];
        }

        $typeName = $course['type'] === 'unemployed' ? '待業' : '在職';
        $featured = isset($course['featured']) && $course['featured'] ? '⭐ ' : '';
        $courseName = $course['course_name'];

        // 判斷問題類型並返回對應資訊
        if (preg_match('/(報名截止|截止時間)/ui', $message)) {
            $content = "📅 **{$featured}{$courseName}**\n\n";
            $content .= "**報名截止時間**\n";
            if (isset($course['schedule']['enrollment_deadline'])) {
                $content .= "• {$course['schedule']['enrollment_deadline']}\n\n";
            } else {
                $content .= "• 請洽詢客服確認最新報名截止日期\n\n";
            }
            $content .= "💡 **提醒**：報名截止日期可能因班級狀況調整，建議盡早報名。";

        } elseif (preg_match('/(開課日期|什麼時候開課)/ui', $message)) {
            $content = "📅 **{$featured}{$courseName}**\n\n";
            $content .= "**開課日期**\n";
            if (isset($course['schedule']['start_date'])) {
                $content .= "• {$course['schedule']['start_date']}\n\n";
            } else {
                $content .= "• 請洽詢客服確認最新開課日期\n\n";
            }
            $content .= "💡 **上課時間**：{$course['schedule']['class_time']}";

        } elseif (preg_match('/(上課地點|在哪上課|地點在哪)/ui', $message)) {
            $content = "📍 **{$featured}{$courseName}**\n\n";
            $content .= "**上課地點**\n";
            $content .= "• {$course['location']['address']}\n\n";
            if (isset($course['location']['note'])) {
                $content .= "💡 {$course['location']['note']}";
            }

        } elseif (preg_match('/(課程費用|學費|費用多少|多少錢)/ui', $message)) {
            $content = "💰 **{$featured}{$courseName}**\n\n";
            $content .= "**課程費用**\n";
            $content .= "• {$course['fee']['amount']}\n\n";
            if (isset($course['fee']['note'])) {
                $content .= "💡 {$course['fee']['note']}";
            }

        } elseif (preg_match('/(時數|總時數|上課時間|幾點上課)/ui', $message)) {
            $content = "⏰ **{$featured}{$courseName}**\n\n";
            $content .= "**課程時數**\n";
            $content .= "• 總時數：{$course['schedule']['total_hours']}小時\n";
            $content .= "• 上課時間：{$course['schedule']['class_time']}\n\n";
            if (isset($course['schedule']['start_date'])) {
                $content .= "• 開課日期：{$course['schedule']['start_date']}";
            }

        } elseif (preg_match('/(需要|需不需要|要不要|需具備|基礎|先備|前置|條件|資格)/ui', $message)) {
            $content = "📋 **{$featured}{$courseName}**\n\n";
            $content .= "**報名條件**\n";
            if (isset($course['enrollment']['requirements'])) {
                $content .= "• {$course['enrollment']['requirements']}\n\n";
            } else {
                $content .= "• 本課程無特殊條件限制\n\n";
            }
            $content .= "💡 **建議**：如有疑問建議聯絡客服確認您是否符合報名資格。";

        } elseif (preg_match('/(適合|對象|誰可以)/ui', $message)) {
            $content = "👥 **{$featured}{$courseName}**\n\n";
            $content .= "**適合對象**\n";
            if (isset($course['enrollment']['requirements'])) {
                $content .= "• {$course['enrollment']['requirements']}\n\n";
            } else {
                $content .= "• 本課程適合對該領域有興趣的學員\n\n";
            }
            if ($typeName === '待業') {
                $content .= "💡 **補充**：待業課程適合15歲以上失業者，需具工作意願。";
            } else {
                $content .= "💡 **補充**：在職課程適合在職勞工，需投保勞保/就保。";
            }

        } elseif (preg_match('/(招生|名額|人數|幾個人|多少人)/ui', $message)) {
            $content = "👨‍🎓 **{$featured}{$courseName}**\n\n";
            $content .= "**招生資訊**\n";
            if (isset($course['enrollment']['capacity'])) {
                $content .= "• 招生人數：{$course['enrollment']['capacity']}人\n\n";
            } else {
                $content .= "• 請洽詢客服確認招生人數\n\n";
            }
            $content .= "💡 **提醒**：名額有限，建議盡早報名以免向隅。";

        } elseif (preg_match('/(甄試|面試|測驗)/ui', $message)) {
            $content = "📝 **{$featured}{$courseName}**\n\n";
            $content .= "**甄試資訊**\n";
            if (isset($course['schedule']['interview_date'])) {
                $content .= "• 甄試時間：{$course['schedule']['interview_date']}\n\n";
                $content .= "💡 **提醒**：請準時出席甄試，未出席者視同放棄。";
            } else {
                $content .= "• 本課程報名後將另行通知甄試時間\n\n";
                $content .= "💡 **提醒**：請留意簡訊或email通知。";
            }

        } else {
            // 預設返回課程詳情
            return $this->formatCourseDetail($course);
        }

        return [
            'content' => $content,
            'quick_options' => $this->getStandardCourseQuickOptions()
        ];
    }

    /**
     * 處理課程完整內容查詢
     */
    public function handleCourseContent()
    {
        $course = $this->session->getContext('last_course');

        if (!$course) {
            return [
                'content' => "請先查看課程詳情，再查詢課程內容。\n\n您可以：",
                'quick_options' => ['待業課程', '在職課程', '精選課程']
            ];
        }

        $typeName = $course['type'] === 'unemployed' ? '待業' : '在職';
        $featured = isset($course['featured']) && $course['featured'] ? '⭐ ' : '';

        $content = "📖 **{$featured}{$course['course_name']} - 完整課程內容**\n\n";
        $content .= "**課程類型**：{$typeName}課程\n\n";

        // 完整課程內容
        if (isset($course['content']) && !empty($course['content'])) {
            $content .= "**📚 課程內容**\n\n";
            $content .= $course['content'] . "\n\n";
        } else {
            $content .= "📚 課程內容資訊請參考官網：\n";
        }

        $content .= "🔗 詳細資訊：{$course['url']}";

        return [
            'content' => $content,
            'quick_options' => ['補助資格', '如何報名', '更多課程']
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
     * 統一的課程頁面渲染（使用相對編號 - 上下文感知）
     */
    protected function renderCoursePage($courses, $offset = 0, $title = '課程清單', $showFeatured = false)
    {
        $pageSize = 5;
        $totalCourses = count($courses);
        $coursesToShow = array_slice($courses, $offset, $pageSize);

        $content = "📚 **{$title}**\n\n";
        $content .= "找到 " . $totalCourses . " 門課程";

        if ($totalCourses > $pageSize) {
            $currentEnd = min($offset + $pageSize, $totalCourses);
            $content .= "（顯示 " . ($offset + 1) . "-{$currentEnd} 筆）";
        }
        $content .= "：\n\n";

        // 使用相對編號（從 offset + 1 開始）
        // 使用 array_values() 確保索引從 0 開始
        $coursesToShow = array_values($coursesToShow);

        foreach ($coursesToShow as $index => $course) {
            // 相對編號 = offset + index + 1
            $relativeNum = $offset + $index + 1;

            $featured = isset($course['featured']) && $course['featured'] ? '⭐ ' : '';
            $typeName = $course['type'] === 'unemployed' ? '待業' : '在職';

            $content .= "{$relativeNum}. {$featured}{$course['course_name']}";
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

        // 提示文字
        if ($offset + $pageSize < $totalCourses) {
            $remaining = $totalCourses - ($offset + $pageSize);
            $content .= "...還有 {$remaining} 門課程（點選「更多」繼續查看）\n\n";
        }

        $content .= "💡 點選課程編號查看詳情";

        // 動態生成快速按鈕
        $quickOptions = [];

        // 添加當前頁面課程編號按鈕（1-5 或更少）
        $coursesOnPage = count($coursesToShow);
        for ($i = 0; $i < $coursesOnPage; $i++) {
            $courseNum = $offset + $i + 1;
            $quickOptions[] = (string)$courseNum;
        }

        // 如果有第二頁，添加「更多」按鈕
        if ($offset + $pageSize < $totalCourses) {
            $quickOptions[] = '更多';
        }

        // 如果快速按鈕數量還有空間，添加其他常用按鈕
        // 一般前端限制在 8 個按鈕以內
        if (count($quickOptions) < 7) {
            $quickOptions[] = '補助資格';
        }
        if (count($quickOptions) < 8) {
            $quickOptions[] = '聯絡客服';
        }

        // 【重要】最後才更新 Session offset，確保與顯示內容和快速按鈕一致
        $this->session->setContext('display_offset', $offset);

        // 添加詳細日誌，用於調試分頁問題
        \Log::info('CourseAgent::renderCoursePage completed', [
            'offset' => $offset,
            'total_courses' => $totalCourses,
            'courses_on_page' => $coursesOnPage,
            'quick_options' => $quickOptions
        ]);

        return [
            'content' => $content,
            'quick_options' => $quickOptions
        ];
    }


    /**
     * 處理分頁請求（更多、剩下的課程）
     */
    public function handlePagination()
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

    /**
     * 取得標準課程快速按鈕（保證系統能理解）
     *
     * @return array
     */
    protected function getStandardCourseQuickOptions()
    {
        // 使用標準快速按鈕（來自 button_config.json）
        // 這些按鈕保證系統都能理解和處理
        return ['報名截止時間', '上課地點', '課程費用', '補助資訊', '如何報名'];
    }
}
