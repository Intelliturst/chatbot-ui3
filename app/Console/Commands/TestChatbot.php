<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SessionManager;
use App\Services\OpenAIService;
use App\Services\RAGService;
use App\Services\Agents\ClassificationAgent;

class TestChatbot extends Command
{
    protected $signature = 'chatbot:test {message=你好}';
    protected $description = '測試聊天機器人功能';

    public function handle()
    {
        $this->info('🤖 測試聊天機器人系統...');
        $this->newLine();

        // 測試 1: 服務初始化
        $this->info('📋 測試 1: 服務初始化');
        try {
            $sessionManager = app(SessionManager::class);
            $openAI = app(OpenAIService::class);
            $ragService = app(RAGService::class);
            $classificationAgent = app(ClassificationAgent::class);

            $this->line('✅ SessionManager 初始化成功');
            $this->line('✅ OpenAIService 初始化成功');
            $this->line('✅ RAGService 初始化成功');
            $this->line('✅ ClassificationAgent 初始化成功');
        } catch (\Exception $e) {
            $this->error('❌ 服務初始化失敗: ' . $e->getMessage());
            return 1;
        }

        $this->newLine();

        // 測試 2: Session 管理
        $this->info('📋 測試 2: Session 管理');
        try {
            $sessionManager->clearSession();
            $this->line('✅ Session 清除成功');

            $sessionManager->addMessage('user', '測試訊息');
            $this->line('✅ 添加用戶訊息成功');

            $history = $sessionManager->getHistory();
            $this->line('✅ 獲取對話歷史成功 (' . count($history) . ' 條訊息)');

            $sessionManager->setContext('test_key', 'test_value');
            $context = $sessionManager->getContext('test_key');
            $this->line('✅ Context 設定與讀取成功: ' . $context);
        } catch (\Exception $e) {
            $this->error('❌ Session 管理失敗: ' . $e->getMessage());
            return 1;
        }

        $this->newLine();

        // 測試 3: RAG 服務
        $this->info('📋 測試 3: RAG 服務');
        try {
            $courses = $ragService->queryCourses(['type' => 'unemployed']);
            $this->line('✅ 查詢待業課程成功 (' . count($courses) . ' 門課程)');

            $subsidyRules = $ragService->getSubsidyRules('employed');
            $this->line('✅ 獲取補助規則成功');

            $serviceInfo = $ragService->getServiceInfo();
            $this->line('✅ 獲取服務資訊成功');
        } catch (\Exception $e) {
            $this->error('❌ RAG 服務失敗: ' . $e->getMessage());
            return 1;
        }

        $this->newLine();

        // 測試 4: 分類代理處理
        $this->info('📋 測試 4: 分類代理處理');
        $userMessage = $this->argument('message');
        $this->line('用戶訊息: ' . $userMessage);

        try {
            $sessionManager->clearSession(); // 清除之前的測試訊息
            $response = $classificationAgent->handle($userMessage);

            $this->newLine();
            $this->info('🤖 AI 回覆:');
            $this->line($response['content']);

            if (!empty($response['quick_options'])) {
                $this->newLine();
                $this->info('⚡ 快速選項:');
                foreach ($response['quick_options'] as $option) {
                    $this->line('  • ' . $option);
                }
            }

            $this->newLine();
            $this->line('✅ 分類代理處理成功');

        } catch (\Exception $e) {
            $this->error('❌ 分類代理處理失敗: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        $this->newLine();
        $this->info('✅ 所有測試通過！');

        return 0;
    }
}
