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
     * 檢測查詢類型
     */
    protected function detectQueryType($message)
    {
        if (preg_match('/(待業|失業).*課程|課程.*(待業|失業)/ui', $message)) {
            return 'list_unemployed';
        }
        if (preg_match('/(在職|產投).*課程|課程.*(在職)/ui', $message)) {
            return 'list_employed';
        }
        if (preg_match('/(精選|熱門|推薦)/ui', $message)) {
            return 'featured';
        }
        if (preg_match('/(搜尋|找|查).*課程/ui', $message)) {
            return 'search';
        }

        // 檢查是否為課程編號查詢（包含純數字）
        if (preg_match('/課程.*[0-9]+|[0-9]+.*課程|編號/ui', $message)) {
            return 'specific';
        }

        // 純數字輸入 - 檢查 Session 上下文
        if (preg_match('/^[0-9]+$/', trim($message))) {
            $lastAction = $this->session->getContext('last_action');
            // 如果上一個動作是課程清單，視為課程編號查詢
            if (in_array($lastAction, ['course_list', 'featured_list', 'search_result'])) {
                return 'specific';
            }
        }

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

        $content = "📚 **{$typeName}課程清單**\n\n";
        $content .= "找到 " . count($courses) . " 門課程：\n\n";

        foreach (array_slice($courses, 0, 5) as $index => $course) {
            $num = $index + 1;
            $featured = isset($course['featured']) && $course['featured'] ? '⭐ ' : '';
            $content .= "{$num}. {$featured}{$course['course_name']}\n";
            $content .= "   時數：{$course['schedule']['total_hours']}小時\n";
            if (isset($course['schedule']['start_date'])) {
                $content .= "   開課：{$course['schedule']['start_date']}\n";
            }
            $content .= "\n";
        }

        if (count($courses) > 5) {
            $content .= "...還有 " . (count($courses) - 5) . " 門課程\n\n";
        }

        $content .= "💡 請輸入課程編號（1-" . count($courses) . "）查看詳情";

        // 設置 Session 上下文，以便識別後續的純數字輸入
        $this->session->setContext('last_action', 'course_list');

        return [
            'content' => $content,
            'quick_options' => ['精選課程', '搜尋課程', '補助資格', '如何報名']
        ];
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

        $content = "⭐ **精選熱門課程**\n\n";

        foreach ($courses as $index => $course) {
            $num = $index + 1;
            $typeName = $course['type'] === 'unemployed' ? '待業' : '在職';
            $content .= "{$num}. {$course['course_name']} ({$typeName})\n";
            $content .= "   時數：{$course['schedule']['total_hours']}小時\n";
            $content .= "   特色：" . implode('、', array_slice($course['keywords'], 0, 3)) . "\n\n";
        }

        $content .= "💡 請輸入編號查看詳情";

        // 設置 Session 上下文
        $this->session->setContext('last_action', 'featured_list');

        return [
            'content' => $content,
            'quick_options' => ['待業課程', '在職課程', '搜尋課程']
        ];
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

        $content = "🔍 **搜尋結果：{$keyword}**\n\n";
        $content .= "找到 " . count($courses) . " 門相關課程：\n\n";

        foreach (array_slice($courses, 0, 5) as $index => $course) {
            $num = $index + 1;
            $typeName = $course['type'] === 'unemployed' ? '待業' : '在職';
            $content .= "{$num}. {$course['course_name']} ({$typeName})\n";
            $content .= "   時數：{$course['schedule']['total_hours']}小時\n\n";
        }

        $content .= "💡 請輸入編號查看詳情";

        // 設置 Session 上下文
        $this->session->setContext('last_action', 'search_result');

        return [
            'content' => $content,
            'quick_options' => ['查看更多', '其他關鍵字', '補助資格']
        ];
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
     * 處理一般課程諮詢
     */
    protected function handleGeneralInquiry($message)
    {
        // 使用 OpenAI 結合上下文回答
        $context = [
            '課程資訊' => '虹宇職訓提供待業和在職兩類課程，涵蓋AI、程式設計、行銷、設計等領域。'
        ];

        $response = $this->generateResponse($message, $context);

        if ($response) {
            return [
                'content' => $response,
                'quick_options' => ['待業課程', '在職課程', '精選課程', '搜尋課程']
            ];
        }

        return [
            'content' => "我可以協助您：\n\n1️⃣ 查看待業課程清單\n2️⃣ 查看在職課程清單\n3️⃣ 搜尋特定課程\n4️⃣ 查看精選課程\n\n請问您想了解什麼呢？",
            'quick_options' => ['待業課程', '在職課程', '精選課程', '搜尋課程']
        ];
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
