<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class RAGService
{
    protected $cacheDuration = 0; // 1小時緩存

    /**
     * 查詢課程資料
     *
     * @param array $filters 篩選條件 ['type' => 'unemployed/employed', 'featured' => true, 'keyword' => 'AI']
     * @return array
     */
    public function queryCourses($filters = [])
    {
        $data = $this->loadJSON('courses/course_list.json');
        $courses = $data['courses'] ?? [];

        // 篩選類型
        if (isset($filters['type'])) {
            $courses = array_filter($courses, function($course) use ($filters) {
                return $course['type'] === $filters['type'];
            });
        }

        // 篩選精選課程
        if (isset($filters['featured']) && $filters['featured']) {
            $courses = array_filter($courses, function($course) {
                return isset($course['featured']) && $course['featured'] === 1;
            });
        }

        // 關鍵字搜尋
        if (isset($filters['keyword'])) {
            $courses = $this->searchByKeyword($courses, $filters['keyword']);
        }

        // 按優先級排序
        usort($courses, function($a, $b) {
            $priorityA = $a['priority'] ?? 999;
            $priorityB = $b['priority'] ?? 999;
            return $priorityA <=> $priorityB;
        });

        return array_values($courses);
    }

    /**
     * 關鍵字搜索
     *
     * @param array $courses
     * @param string $keyword
     * @return array
     */
    protected function searchByKeyword($courses, $keyword)
    {
        return array_filter($courses, function($course) use ($keyword) {
            // 搜尋課程名稱
            if (stripos($course['course_name'], $keyword) !== false) {
                return true;
            }

            // 搜尋完整名稱
            if (isset($course['full_name']) && stripos($course['full_name'], $keyword) !== false) {
                return true;
            }

            // 搜尋內容
            if (isset($course['content']) && stripos($course['content'], $keyword) !== false) {
                return true;
            }

            // 搜尋關鍵字陣列
            if (isset($course['keywords'])) {
                foreach ($course['keywords'] as $kw) {
                    if (stripos($kw, $keyword) !== false) {
                        return true;
                    }
                }
            }

            return false;
        });
    }

    /**
     * 查詢單一課程
     *
     * @param int $id
     * @return array|null
     */
    public function getCourseById($id)
    {
        $data = $this->loadJSON('courses/course_list.json');
        $courses = $data['courses'] ?? [];

        foreach ($courses as $course) {
            if ($course['id'] == $id) {
                return $course;
            }
        }

        return null;
    }

    /**
     * 查詢課程編號對應
     *
     * @param int $number
     * @return int|null
     */
    public function getCourseIdByNumber($number)
    {
        $mapping = $this->loadJSON('courses/course_mapping.json');
        $numberToId = $mapping['number_to_id'] ?? [];

        return $numberToId[(string)$number] ?? null;
    }

    /**
     * 查詢補助規則
     *
     * @param string $type 'employed' 或 'unemployed'
     * @return array
     */
    public function getSubsidyRules($type)
    {
        $filename = $type === 'employed'
            ? 'subsidy/employed_rules.json'
            : 'subsidy/unemployed_rules.json';

        return $this->loadJSON($filename);
    }

    /**
     * 查詢補助常見問答
     *
     * @return array
     */
    public function getSubsidyFAQ()
    {
        return $this->loadJSON('subsidy/subsidy_faq.json');
    }

    /**
     * 查詢FAQ
     *
     * @param string|null $keyword
     * @return array
     */
    public function searchFAQ($keyword = null)
    {
        $faqData = $this->loadJSON('faq/general_faq.json');
        $faqs = $faqData['faqs'] ?? [];

        if ($keyword === null) {
            return $faqs;
        }

        $results = [];
        foreach ($faqs as $faq) {
            // 搜尋問題
            if (stripos($faq['question'], $keyword) !== false) {
                $results[] = $faq;
                continue;
            }

            // 搜尋答案
            if (stripos($faq['answer'], $keyword) !== false) {
                $results[] = $faq;
                continue;
            }

            // 搜尋關鍵字
            if (isset($faq['keywords'])) {
                foreach ($faq['keywords'] as $kw) {
                    if (stripos($kw, $keyword) !== false) {
                        $results[] = $faq;
                        break;
                    }
                }
            }
        }

        // 按優先級排序
        usort($results, function($a, $b) {
            $priorityA = $a['priority'] ?? 999;
            $priorityB = $b['priority'] ?? 999;
            return $priorityA <=> $priorityB;
        });

        return $results;
    }

    /**
     * 取得報名流程說明
     *
     * @param string $type 'employed' 或 'unemployed'
     * @return array
     */
    public function getEnrollmentProcess($type)
    {
        $data = $this->loadJSON('faq/enrollment_process.json');

        if ($type === 'employed') {
            return $data['employed_process'] ?? [];
        } elseif ($type === 'unemployed') {
            return $data['unemployed_process'] ?? [];
        }

        return $data;
    }

    /**
     * 取得聯絡資訊
     *
     * @return array
     */
    public function getServiceInfo()
    {
        return $this->loadJSON('contacts/service_info.json');
    }

    /**
     * 取得預設回應（打招呼/未知分類）
     *
     * @param string $category 'greetings' 或 'unknown'
     * @param string|null $trigger
     * @return array|null
     */
    public function getDefaultResponse($category, $trigger = null)
    {
        $responses = $this->loadJSON('greetings/default_responses.json');

        if ($category === 'greetings') {
            $greetingResponses = $responses['greetings']['responses'] ?? [];

            if ($trigger !== null) {
                foreach ($greetingResponses as $response) {
                    if (isset($response['trigger']) && in_array($trigger, $response['trigger'])) {
                        return $response;
                    }
                }
            }

            return $greetingResponses[0] ?? null;
        }

        if ($category === 'unknown') {
            return $responses['unknown']['responses'][0] ?? null;
        }

        return null;
    }

    /**
     * 取得快速選項按鈕配置
     *
     * @param string|null $menu 'main_menu', 'course_menu', 'subsidy_menu'
     * @return array
     */
    public function getQuickOptions($menu = null)
    {
        $data = $this->loadJSON('quick_options/button_config.json');
        $quickOptions = $data['quick_options'] ?? [];

        if ($menu !== null && isset($quickOptions[$menu])) {
            return $quickOptions[$menu];
        }

        return $quickOptions;
    }

    /**
     * 取得關聯問題
     *
     * @param string $type 'course', 'subsidy', 'enrollment'
     * @return array
     */
    public function getRelatedQuestions($type)
    {
        $data = $this->loadJSON('quick_options/button_config.json');
        $relatedQuestions = $data['related_questions'] ?? [];

        return $relatedQuestions[$type] ?? [];
    }

    /**
     * 載入JSON文件
     *
     * @param string $filename
     * @return array
     * @throws \Exception
     */
    protected function loadJSON($filename)
    {
        $cacheKey = 'chatbot_json_' . str_replace('/', '_', $filename);

        return Cache::remember($cacheKey, $this->cacheDuration, function() use ($filename) {
            $path = resource_path("data/chatbot/{$filename}");

            if (!file_exists($path)) {
                throw new \Exception("Knowledge base file not found: {$filename}");
            }

            $content = file_get_contents($path);
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("JSON decode error in {$filename}: " . json_last_error_msg());
            }

            return $data;
        });
    }

    /**
     * 清除緩存
     *
     * @return void
     */
    public function clearCache()
    {
        Cache::flush();
    }
}
