<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RAGService;

class ValidateKnowledgeBase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chatbot:validate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate JSON knowledge base files and test RAGService';

    protected $ragService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(RAGService $ragService)
    {
        parent::__construct();
        $this->ragService = $ragService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 Validating JSON Knowledge Base...');
        $this->newLine();

        $files = [
            'courses/course_list.json' => 'Course List',
            'courses/course_mapping.json' => 'Course Mapping',
            'subsidy/employed_rules.json' => 'Employed Subsidy Rules',
            'subsidy/unemployed_rules.json' => 'Unemployed Subsidy Rules',
            'subsidy/subsidy_faq.json' => 'Subsidy FAQ',
            'faq/general_faq.json' => 'General FAQ',
            'faq/enrollment_process.json' => 'Enrollment Process',
            'contacts/service_info.json' => 'Service Info',
            'greetings/default_responses.json' => 'Default Responses',
            'quick_options/button_config.json' => 'Quick Options',
        ];

        $errors = 0;
        foreach ($files as $file => $name) {
            $path = resource_path("data/chatbot/{$file}");

            if (!file_exists($path)) {
                $this->error("❌ {$name}: File not found");
                $errors++;
                continue;
            }

            $json = file_get_contents($path);
            $data = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error("❌ {$name}: " . json_last_error_msg());
                $errors++;
            } else {
                $this->info("✅ {$name}: Valid JSON");
            }
        }

        $this->newLine();
        $this->info('📊 Testing RAGService Methods...');
        $this->newLine();

        try {
            // Test 1: Query all courses
            $allCourses = $this->ragService->queryCourses();
            $this->info("✅ Query All Courses: Found " . count($allCourses) . " courses");

            // Test 2: Query unemployed courses
            $unemployedCourses = $this->ragService->queryCourses(['type' => 'unemployed']);
            $this->info("✅ Query Unemployed Courses: Found " . count($unemployedCourses) . " courses");

            // Test 3: Query featured courses
            $featuredCourses = $this->ragService->queryCourses(['featured' => true]);
            $this->info("✅ Query Featured Courses: Found " . count($featuredCourses) . " courses");

            // Test 4: Search by keyword
            $aiCourses = $this->ragService->queryCourses(['keyword' => 'AI']);
            $this->info("✅ Search by 'AI': Found " . count($aiCourses) . " courses");

            // Test 5: Get course by ID
            $course = $this->ragService->getCourseById(6);
            $courseName = $course ? $course['course_name'] : 'Not found';
            $this->info("✅ Get Course by ID (6): {$courseName}");

            // Test 6: Get subsidy rules
            $subsidyRules = $this->ragService->getSubsidyRules('employed');
            $this->info("✅ Get Employed Subsidy Rules: " . ($subsidyRules ? 'Loaded' : 'Failed'));

            // Test 7: Search FAQ
            $faqResults = $this->ragService->searchFAQ('報名');
            $this->info("✅ Search FAQ ('報名'): Found " . count($faqResults) . " results");

            // Test 8: Get service info
            $serviceInfo = $this->ragService->getServiceInfo();
            $orgName = $serviceInfo['organization']['name'] ?? 'Not found';
            $this->info("✅ Get Service Info: {$orgName}");

            // Test 9: Get default response
            $greeting = $this->ragService->getDefaultResponse('greetings', '你好');
            $this->info("✅ Get Greeting Response: " . ($greeting ? 'Loaded' : 'Failed'));

            // Test 10: Get quick options
            $quickOptions = $this->ragService->getQuickOptions('main_menu');
            $this->info("✅ Get Quick Options: Found " . count($quickOptions) . " options");

        } catch (\Exception $e) {
            $this->error("❌ RAGService Test Failed: " . $e->getMessage());
            $errors++;
        }

        $this->newLine();

        if ($errors === 0) {
            $this->info('🎉 All tests passed!');
            return 0;
        } else {
            $this->error("⚠️  {$errors} error(s) found");
            return 1;
        }
    }
}
