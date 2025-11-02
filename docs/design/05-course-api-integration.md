# 虹宇職訓智能客服系統 - Course API對接設計

**文件版本**: 1.0
**最後更新**: 2025-10-24
**作者**: 虹宇職訓開發團隊

---

## 📋 目錄

1. [API設計概述](#api設計概述)
2. [資料表結構設計](#資料表結構設計)
3. [API端點定義](#api端點定義)
4. [CourseAPIService實現](#courseapiservice實現)
5. [從JSON遷移到API](#從json遷移到api)
6. [測試與驗證](#測試與驗證)

---

## API設計概述

### 設計目標

1. **即時資料同步**：課程資訊即時更新，無需重新部署
2. **靈活查詢**：支援多種篩選條件（類型、精選、關鍵字）
3. **高性能**：緩存機制，減少資料庫查詢
4. **易於維護**：統一的資料格式，易於擴展

### 資料流程

```
[智能客服] → CourseAPIService → API端點 → MySQL courses表 → 返回JSON
```

---

## 資料表結構設計

### courses表設計

```sql
CREATE TABLE `courses` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `course_code` VARCHAR(50) NOT NULL COMMENT '課程代碼',
  `course_name` VARCHAR(200) NOT NULL COMMENT '課程名稱',
  `full_name` VARCHAR(255) NOT NULL COMMENT '完整名稱（含日期）',
  `type` ENUM('employed', 'unemployed') NOT NULL COMMENT '課程類型',

  /* 排序與精選 */
  `featured` TINYINT(1) DEFAULT 0 COMMENT '是否為精選課程（0:否, 1:是）',
  `priority` INT DEFAULT 999 COMMENT '優先級（數字越小優先級越高）',
  `status` ENUM('active', 'inactive', 'full') DEFAULT 'active' COMMENT '課程狀態',

  /* 時間資訊 */
  `start_date` DATE NOT NULL COMMENT '開課日期',
  `end_date` DATE NULL COMMENT '結束日期',
  `class_time` VARCHAR(200) NULL COMMENT '上課時間',
  `total_hours` INT NULL COMMENT '總時數',
  `enrollment_deadline` DATETIME NULL COMMENT '報名截止時間',
  `enrollment_start` DATETIME NULL COMMENT '報名開始時間（在職課程）',
  `interview_date` DATETIME NULL COMMENT '甄試日期（待業課程）',

  /* 費用資訊 */
  `fee_amount` VARCHAR(100) NULL COMMENT '課程費用',
  `fee_note` TEXT NULL COMMENT '費用說明',
  `payment_method` VARCHAR(200) NULL COMMENT '繳費方式',
  `subsidy_capacity` INT NULL COMMENT '補助名額',

  /* 招生資訊 */
  `capacity` INT NULL COMMENT '招生人數',
  `requirements` TEXT NULL COMMENT '報名資格',

  /* 上課地點 */
  `location_address` VARCHAR(255) NULL COMMENT '上課地點',
  `location_map_url` TEXT NULL COMMENT '地圖連結',

  /* 課程內容 */
  `content` TEXT NULL COMMENT '課程內容描述',

  /* 其他資訊 */
  `course_url` TEXT NULL COMMENT '課程網址',
  `image_url` TEXT NULL COMMENT '課程圖片',
  `keywords` JSON NULL COMMENT '關鍵字（JSON陣列）',
  `related_questions` JSON NULL COMMENT '關聯問題（JSON陣列）',

  /* 時間戳記 */
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL COMMENT '軟刪除',

  /* 索引 */
  INDEX `idx_type` (`type`),
  INDEX `idx_featured` (`featured`),
  INDEX `idx_status` (`status`),
  INDEX `idx_start_date` (`start_date`),
  INDEX `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='課程資料表';
```

### 欄位說明

#### featured字段（精選課程）

- **用途**：標記熱門/推薦課程
- **值**：0（否）或 1（是）
- **應用**：當用戶詢問「熱門課程」或「推薦課程」時，查詢 `featured=1` 的課程

#### priority字段（優先級）

- **用途**：排序課程顯示順序
- **值**：數字越小優先級越高（1最高，999最低）
- **應用**：課程清單按 `priority ASC` 排序

#### status字段（課程狀態）

- **active**：正常招生中
- **inactive**：暫停招生
- **full**：已額滿

---

## API端點定義

### 1. 查詢所有課程

```
GET /api/courses
```

**Query參數**：

| 參數 | 類型 | 說明 | 範例 |
|------|------|------|------|
| type | string | 課程類型 | employed, unemployed |
| featured | boolean | 是否精選 | 1, 0 |
| status | string | 課程狀態 | active |
| keyword | string | 關鍵字搜尋 | AI, PMP |
| limit | int | 返回數量 | 10 |

**範例請求**：

```http
GET /api/courses?type=unemployed&status=active&limit=10
```

**範例回應**：

```json
{
  "success": true,
  "data": [
    {
      "id": 6,
      "course_code": "158352",
      "course_name": "AI生成影音行銷與直播技巧實戰班",
      "full_name": "待業-1141128 AI生成影音行銷與直播技巧實戰班",
      "type": "unemployed",
      "featured": 1,
      "priority": 1,
      "schedule": {
        "start_date": "2025-11-28",
        "end_date": "2026-01-30",
        "class_time": "週一至週五，上午9:00～下午17:00",
        "total_hours": 300,
        "enrollment_deadline": "2025-11-18 17:00",
        "interview_date": "2025-11-18 13:00"
      },
      "fee": {
        "amount": "政府補助100%",
        "note": "一般國民(從未加過勞保)、未具特定對象身分且參加工/農/漁會勞工保險者，需自行負擔20%"
      },
      "location": {
        "address": "桃園市中壢區復興路46號12樓（兆豐銀行樓上）"
      },
      "content": "涵蓋影像編修、AI生成式設計、商品視覺化、短影音製作、社群經營、直播企劃與平台策略等22個教學單元，總計約290小時專業課程。",
      "course_url": "https://www.hongyu.goblinlab.org/courses/6",
      "image_url": "/images/courses/course_6.jpg",
      "keywords": ["AI", "影音", "行銷", "直播"],
      "related_questions": ["報名截止時間", "上課地點", "課程內容", "如何報名"]
    }
  ],
  "meta": {
    "total": 5,
    "count": 5
  }
}
```

### 2. 查詢單一課程

```
GET /api/courses/{id}
```

**路徑參數**：

| 參數 | 類型 | 說明 |
|------|------|------|
| id | int | 課程ID |

**範例請求**：

```http
GET /api/courses/6
```

**範例回應**：

```json
{
  "success": true,
  "data": {
    "id": 6,
    "course_code": "158352",
    "course_name": "AI生成影音行銷與直播技巧實戰班",
    // ... 完整課程資訊
  }
}
```

### 3. 關鍵字搜尋

```
GET /api/courses/search
```

**Query參數**：

| 參數 | 類型 | 必填 | 說明 |
|------|------|------|------|
| q | string | 是 | 搜尋關鍵字 |
| type | string | 否 | 課程類型 |
| limit | int | 否 | 返回數量（預設10） |

**範例請求**：

```http
GET /api/courses/search?q=AI&type=unemployed
```

---

## CourseAPIService實現

### Service類別

```php
// src/main/php/Services/CourseAPIService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CourseAPIService
{
    protected $baseUrl;
    protected $cacheTime = 3600; // 1小時緩存

    public function __construct()
    {
        $this->baseUrl = config('chatbot.course_api_url', '/api/courses');
    }

    /**
     * 查詢課程列表
     *
     * @param array $filters ['type' => 'unemployed', 'featured' => 1, 'keyword' => 'AI']
     * @return array
     */
    public function getCourses($filters = [])
    {
        $cacheKey = 'courses_' . md5(json_encode($filters));

        return Cache::remember($cacheKey, $this->cacheTime, function() use ($filters) {
            $response = Http::get($this->baseUrl, $filters);

            if ($response->successful()) {
                return $response->json('data', []);
            }

            return [];
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
        $cacheKey = "course_{$id}";

        return Cache::remember($cacheKey, $this->cacheTime, function() use ($id) {
            $response = Http::get("{$this->baseUrl}/{$id}");

            if ($response->successful()) {
                return $response->json('data');
            }

            return null;
        });
    }

    /**
     * 關鍵字搜尋
     *
     * @param string $keyword
     * @param array $filters
     * @return array
     */
    public function searchCourses($keyword, $filters = [])
    {
        $filters['q'] = $keyword;

        $response = Http::get("{$this->baseUrl}/search", $filters);

        if ($response->successful()) {
            return $response->json('data', []);
        }

        return [];
    }

    /**
     * 取得精選課程
     *
     * @return array
     */
    public function getFeaturedCourses()
    {
        return $this->getCourses(['featured' => 1, 'status' => 'active']);
    }

    /**
     * 清除緩存
     *
     * @param int|null $courseId
     * @return void
     */
    public function clearCache($courseId = null)
    {
        if ($courseId) {
            Cache::forget("course_{$courseId}");
        } else {
            Cache::flush(); // 清除所有課程緩存
        }
    }
}
```

### API Controller

```php
// src/main/php/Http/Controllers/Api/CourseController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * 查詢課程列表
     */
    public function index(Request $request)
    {
        $query = Course::query()->where('status', 'active');

        // 類型篩選
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // 精選篩選
        if ($request->has('featured')) {
            $query->where('featured', $request->featured);
        }

        // 關鍵字搜尋
        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('course_name', 'LIKE', "%{$keyword}%")
                  ->orWhereJsonContains('keywords', $keyword);
            });
        }

        // 排序
        $query->orderBy('priority', 'asc')
              ->orderBy('start_date', 'desc');

        // 限制數量
        $limit = $request->input('limit', 20);
        $courses = $query->limit($limit)->get();

        // 格式化輸出
        $data = $courses->map(function($course) {
            return $this->formatCourse($course);
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'total' => $courses->count(),
                'count' => $data->count()
            ]
        ]);
    }

    /**
     * 查詢單一課程
     */
    public function show($id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => '找不到課程'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatCourse($course)
        ]);
    }

    /**
     * 關鍵字搜尋
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:1'
        ]);

        $keyword = $request->input('q');
        $query = Course::query()->where('status', 'active');

        // 搜尋課程名稱和關鍵字
        $query->where(function($q) use ($keyword) {
            $q->where('course_name', 'LIKE', "%{$keyword}%")
              ->orWhereJsonContains('keywords', $keyword);
        });

        // 類型篩選
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $limit = $request->input('limit', 10);
        $courses = $query->orderBy('priority', 'asc')->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $courses->map(fn($c) => $this->formatCourse($c))
        ]);
    }

    /**
     * 格式化課程資料
     */
    protected function formatCourse($course)
    {
        return [
            'id' => $course->id,
            'course_code' => $course->course_code,
            'course_name' => $course->course_name,
            'full_name' => $course->full_name,
            'type' => $course->type,
            'featured' => $course->featured,
            'priority' => $course->priority,
            'schedule' => [
                'start_date' => $course->start_date,
                'end_date' => $course->end_date,
                'class_time' => $course->class_time,
                'total_hours' => $course->total_hours,
                'enrollment_deadline' => $course->enrollment_deadline,
                'enrollment_start' => $course->enrollment_start,
                'interview_date' => $course->interview_date,
            ],
            'fee' => [
                'amount' => $course->fee_amount,
                'note' => $course->fee_note,
                'payment_method' => $course->payment_method,
                'subsidy_capacity' => $course->subsidy_capacity,
            ],
            'enrollment' => [
                'capacity' => $course->capacity,
                'requirements' => $course->requirements,
            ],
            'location' => [
                'address' => $course->location_address,
                'map_url' => $course->location_map_url,
            ],
            'content' => $course->content,
            'course_url' => $course->course_url,
            'image_url' => $course->image_url,
            'keywords' => $course->keywords,
            'related_questions' => $course->related_questions,
        ];
    }
}
```

---

## 從JSON遷移到API

### 遷移步驟

#### 步驟1：準備資料庫

```bash
# 執行Migration
php artisan migrate

# 匯入課程資料（從JSON轉換）
php artisan chatbot:import-courses
```

#### 步驟2：創建資料匯入指令

```php
// src/main/php/Console/Commands/ImportCoursesCommand.php

namespace App\Console\Commands;

use App\Models\Course;
use Illuminate\Console\Command;

class ImportCoursesCommand extends Command
{
    protected $signature = 'chatbot:import-courses {--file=}';
    protected $description = '從JSON文件匯入課程資料';

    public function handle()
    {
        $file = $this->option('file') ?: resource_path('config/chatbot/knowledge_base/courses/course_list.json');

        if (!file_exists($file)) {
            $this->error("文件不存在: {$file}");
            return 1;
        }

        $data = json_decode(file_get_contents($file), true);
        $courses = $data['courses'] ?? [];

        $this->info("準備匯入 " . count($courses) . " 門課程...");

        $bar = $this->output->createProgressBar(count($courses));

        foreach ($courses as $courseData) {
            Course::updateOrCreate(
                ['id' => $courseData['id']],
                [
                    'course_code' => $courseData['course_code'],
                    'course_name' => $courseData['course_name'],
                    'full_name' => $courseData['full_name'],
                    'type' => $courseData['type'],
                    'featured' => $courseData['featured'],
                    'priority' => $courseData['priority'],
                    'start_date' => $courseData['schedule']['start_date'],
                    'end_date' => $courseData['schedule']['end_date'] ?? null,
                    'class_time' => $courseData['schedule']['class_time'] ?? null,
                    'total_hours' => $courseData['schedule']['total_hours'] ?? null,
                    'enrollment_deadline' => $courseData['schedule']['enrollment_deadline'] ?? null,
                    'interview_date' => $courseData['schedule']['interview_date'] ?? null,
                    'fee_amount' => $courseData['fee']['amount'] ?? null,
                    'fee_note' => $courseData['fee']['note'] ?? null,
                    'capacity' => $courseData['enrollment']['capacity'] ?? null,
                    'requirements' => $courseData['enrollment']['requirements'] ?? null,
                    'location_address' => $courseData['location']['address'] ?? null,
                    'content' => $courseData['content'] ?? null,
                    'course_url' => $courseData['url'],
                    'image_url' => $courseData['image_url'] ?? null,
                    'keywords' => json_encode($courseData['keywords']),
                    'related_questions' => json_encode($courseData['related_questions']),
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n匯入完成！");

        return 0;
    }
}
```

#### 步驟3：修改RAGService

```php
// src/main/php/Services/RAGService.php

class RAGService
{
    protected $courseAPIService;
    protected $useAPI = false; // 切換開關

    public function __construct(CourseAPIService $courseAPIService)
    {
        $this->courseAPIService = $courseAPIService;
        $this->useAPI = config('chatbot.use_course_api', false);
    }

    public function queryCourses($filters = [])
    {
        if ($this->useAPI) {
            // 使用API
            return $this->courseAPIService->getCourses($filters);
        } else {
            // 使用JSON（原有邏輯）
            return $this->queryCoursesFromJSON($filters);
        }
    }

    protected function queryCoursesFromJSON($filters)
    {
        // 原有的JSON查詢邏輯
        $courses = $this->loadJSON('courses/course_list.json');
        // ... 篩選邏輯
        return $courses;
    }
}
```

#### 步驟4：配置切換

```php
// config/chatbot.php

return [
    // 是否使用Course API（false使用JSON）
    'use_course_api' => env('CHATBOT_USE_COURSE_API', false),

    // Course API URL
    'course_api_url' => env('CHATBOT_COURSE_API_URL', '/api/courses'),
];
```

```env
# .env

# 開發階段：使用JSON
CHATBOT_USE_COURSE_API=false

# 正式上線：使用API
CHATBOT_USE_COURSE_API=true
```

---

## 測試與驗證

### 單元測試

```php
// tests/Feature/CourseAPITest.php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Course;

class CourseAPITest extends TestCase
{
    public function test_get_all_courses()
    {
        $response = $this->getJson('/api/courses');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => ['id', 'course_name', 'type', 'schedule']
                     ]
                 ]);
    }

    public function test_filter_by_type()
    {
        $response = $this->getJson('/api/courses?type=unemployed');

        $response->assertStatus(200);
        $data = $response->json('data');

        foreach ($data as $course) {
            $this->assertEquals('unemployed', $course['type']);
        }
    }

    public function test_featured_courses()
    {
        $response = $this->getJson('/api/courses?featured=1');

        $response->assertStatus(200);
        $data = $response->json('data');

        foreach ($data as $course) {
            $this->assertEquals(1, $course['featured']);
        }
    }
}
```

### 手動測試

```bash
# 測試API端點
curl http://localhost:8000/api/courses

# 測試篩選
curl "http://localhost:8000/api/courses?type=unemployed&featured=1"

# 測試搜尋
curl "http://localhost:8000/api/courses/search?q=AI"
```

---

## 附錄

### 相關文件

- [02-knowledge-base-structure.md](./02-knowledge-base-structure.md) - JSON知識庫設計
- [06-laravel-development-guide.md](./06-laravel-development-guide.md) - Laravel開發規範

---

**文件結束**
