<?php

namespace App\Services\Agents;

use App\Services\RAGService;

class CourseAgent extends BaseAgent
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
        // 判断查询类型
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
     * 检测查询类型
     */
    protected function detectQueryType($message)
    {
        if (preg_match('/(待业|失业|待業|失業).*课程|课程.*(待业|失业)/ui', $message)) {
            return 'list_unemployed';
        }
        if (preg_match('/(在职|在職|產投|产投).*课程|课程.*(在职|在職)/ui', $message)) {
            return 'list_employed';
        }
        if (preg_match('/(精选|熱門|热门|推荐|精選|推薦)/ui', $message)) {
            return 'featured';
        }
        if (preg_match('/(搜尋|搜索|找|查).*课程/ui', $message)) {
            return 'search';
        }
        if (preg_match('/课程.*[0-9]+|[0-9]+.*课程|编号|編號/ui', $message)) {
            return 'specific';
        }
        return 'general';
    }

    /**
     * 处理课程清单查询
     */
    protected function handleCourseList($type)
    {
        $courses = $this->ragService->queryCourses(['type' => $type]);
        $typeName = $type === 'unemployed' ? '待业' : '在职';

        if (empty($courses)) {
            return [
                'content' => "目前没有{$typeName}课程资料。",
                'quick_options' => ['查看其他课程', '联络客服']
            ];
        }

        $content = "📚 **{$typeName}课程清单**\n\n";
        $content .= "找到 " . count($courses) . " 门课程：\n\n";

        foreach (array_slice($courses, 0, 5) as $index => $course) {
            $num = $index + 1;
            $featured = isset($course['featured']) && $course['featured'] ? '⭐ ' : '';
            $content .= "{$num}. {$featured}{$course['course_name']}\n";
            $content .= "   时数：{$course['schedule']['total_hours']}小时\n";
            if (isset($course['schedule']['start_date'])) {
                $content .= "   开课：{$course['schedule']['start_date']}\n";
            }
            $content .= "\n";
        }

        if (count($courses) > 5) {
            $content .= "...还有 " . (count($courses) - 5) . " 门课程\n\n";
        }

        $content .= "💡 请输入课程编号（1-" . count($courses) . "）查看详情";

        return [
            'content' => $content,
            'quick_options' => ['精选课程', '搜寻课程', '补助资格', '如何报名']
        ];
    }

    /**
     * 处理精选课程
     */
    protected function handleFeaturedCourses()
    {
        $courses = $this->ragService->queryCourses(['featured' => true]);

        if (empty($courses)) {
            return [
                'content' => "目前没有精选课程。",
                'quick_options' => ['查看所有课程', '联络客服']
            ];
        }

        $content = "⭐ **精选热门课程**\n\n";

        foreach ($courses as $index => $course) {
            $num = $index + 1;
            $typeName = $course['type'] === 'unemployed' ? '待业' : '在职';
            $content .= "{$num}. {$course['course_name']} ({$typeName})\n";
            $content .= "   时数：{$course['schedule']['total_hours']}小时\n";
            $content .= "   特色：" . implode('、', array_slice($course['keywords'], 0, 3)) . "\n\n";
        }

        $content .= "💡 请输入编号查看详情";

        return [
            'content' => $content,
            'quick_options' => ['待业课程', '在职课程', '搜寻课程']
        ];
    }

    /**
     * 处理课程搜索
     */
    protected function handleCourseSearch($message)
    {
        // 提取关键字
        $keyword = $this->extractKeyword($message);

        if (empty($keyword)) {
            return [
                'content' => "请告诉我您想搜寻什么课程？\n\n例如：AI课程、行销课程、Python课程等",
                'quick_options' => ['AI课程', '行销课程', '设计课程', '管理课程']
            ];
        }

        $courses = $this->ragService->queryCourses(['keyword' => $keyword]);

        if (empty($courses)) {
            return [
                'content' => "很抱歉，找不到与「{$keyword}」相关的课程。\n\n您可以：\n• 尝试其他关键字\n• 查看所有课程清单\n• 联络客服询问",
                'quick_options' => ['待业课程', '在职课程', '精选课程', '联络客服']
            ];
        }

        $content = "🔍 **搜寻结果：{$keyword}**\n\n";
        $content .= "找到 " . count($courses) . " 门相关课程：\n\n";

        foreach (array_slice($courses, 0, 5) as $index => $course) {
            $num = $index + 1;
            $typeName = $course['type'] === 'unemployed' ? '待业' : '在职';
            $content .= "{$num}. {$course['course_name']} ({$typeName})\n";
            $content .= "   时数：{$course['schedule']['total_hours']}小时\n\n";
        }

        $content .= "💡 请输入编号查看详情";

        return [
            'content' => $content,
            'quick_options' => ['查看更多', '其他关键字', '补助资格']
        ];
    }

    /**
     * 提取搜索关键字
     */
    protected function extractKeyword($message)
    {
        // 移除常见的查询词
        $cleanMessage = preg_replace('/(我想|想要|想学|想學|搜尋|搜索|查询|查詢|课程|課程|有没有|有沒有|找)/ui', '', $message);
        $cleanMessage = trim($cleanMessage);

        return $cleanMessage;
    }

    /**
     * 处理特定课程查询
     */
    protected function handleSpecificCourse($message)
    {
        // 提取课程编号
        preg_match('/[0-9]+/', $message, $matches);

        if (empty($matches)) {
            return $this->handleGeneralInquiry($message);
        }

        $number = (int)$matches[0];
        $courseId = $this->ragService->getCourseIdByNumber($number);

        if (!$courseId) {
            return [
                'content' => "找不到编号 {$number} 的课程。\n\n请输入正确的课程编号，或查看课程清单。",
                'quick_options' => ['待业课程', '在职课程', '精选课程']
            ];
        }

        $course = $this->ragService->getCourseById($courseId);

        if (!$course) {
            return [
                'content' => "无法载入课程资料，请稍后再试。",
                'quick_options' => ['联络客服']
            ];
        }

        return $this->formatCourseDetail($course);
    }

    /**
     * 格式化课程详情
     */
    protected function formatCourseDetail($course)
    {
        $typeName = $course['type'] === 'unemployed' ? '待业' : '在职';
        $featured = isset($course['featured']) && $course['featured'] ? '⭐ ' : '';

        $content = "📚 **{$featured}{$course['course_name']}**\n\n";
        $content .= "**课程类型**：{$typeName}课程\n\n";

        // 时间资讯
        $content .= "**⏰ 时间资讯**\n";
        $content .= "• 总时数：{$course['schedule']['total_hours']}小时\n";
        $content .= "• 上课时间：{$course['schedule']['class_time']}\n";

        if (isset($course['schedule']['start_date'])) {
            $content .= "• 开课日期：{$course['schedule']['start_date']}\n";
        }
        if (isset($course['schedule']['enrollment_deadline'])) {
            $content .= "• 报名截止：{$course['schedule']['enrollment_deadline']}\n";
        }
        $content .= "\n";

        // 费用资讯
        $content .= "**💰 费用资讯**\n";
        $content .= "• {$course['fee']['amount']}\n";
        if (isset($course['fee']['note'])) {
            $content .= "• {$course['fee']['note']}\n";
        }
        $content .= "\n";

        // 上课地点
        $content .= "**📍 上课地点**\n";
        $content .= "{$course['location']['address']}\n\n";

        // 课程内容
        if (isset($course['content'])) {
            $contentPreview = mb_substr($course['content'], 0, 150);
            if (mb_strlen($course['content']) > 150) {
                $contentPreview .= '...';
            }
            $content .= "**📖 课程内容**\n{$contentPreview}\n\n";
        }

        $content .= "🔗 详细资讯：{$course['url']}";

        $quickOptions = $course['related_questions'] ?? ['补助资格', '如何报名', '更多课程'];

        return [
            'content' => $content,
            'quick_options' => $quickOptions
        ];
    }

    /**
     * 处理一般课程咨询
     */
    protected function handleGeneralInquiry($message)
    {
        // 使用 OpenAI 结合上下文回答
        $context = [
            '课程资讯' => '虹宇职训提供待业和在职两类课程，涵盖AI、程式设计、行销、设计等领域。'
        ];

        $response = $this->generateResponse($message, $context);

        if ($response) {
            return [
                'content' => $response,
                'quick_options' => ['待业课程', '在职课程', '精选课程', '搜寻课程']
            ];
        }

        return [
            'content' => "我可以协助您：\n\n1️⃣ 查看待业课程清单\n2️⃣ 查看在职课程清单\n3️⃣ 搜寻特定课程\n4️⃣ 查看精选课程\n\n请问您想了解什么呢？",
            'quick_options' => ['待业课程', '在职课程', '精选课程', '搜寻课程']
        ];
    }

    /**
     * 获取系统提示词
     */
    protected function getSystemPrompt()
    {
        return <<<EOT
你是虹宇职训的课程咨询专员。你的职责是：
1. 协助学员了解课程资讯
2. 提供清晰、准确的课程说明
3. 引导学员找到适合的课程

虹宇职训课程特色：
- 待业课程：政府全额或部分补助，全日制密集训练
- 在职课程：周末上课，结训后可申请80%补助
- 课程领域：AI、程式设计、行销、设计、管理等

请用繁体中文回答，保持专业友善的语气。
EOT;
    }
}
