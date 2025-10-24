<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SessionManager;
use App\Services\OpenAIService;
use App\Services\RAGService;
use App\Services\Agents\ClassificationAgent;
use App\Services\Agents\CourseAgent;
use App\Services\Agents\SubsidyAgent;
use App\Services\Agents\FAQAgent;
use App\Services\Agents\EnrollmentAgent;
use App\Services\Agents\HumanServiceAgent;

class ChatbotServiceProvider extends ServiceProvider
{
    /**
     * 註冊服務
     */
    public function register()
    {
        // 註冊 SessionManager 為單例
        $this->app->singleton(SessionManager::class, function ($app) {
            return new SessionManager();
        });

        // 註冊 OpenAIService 為單例
        $this->app->singleton(OpenAIService::class, function ($app) {
            return new OpenAIService();
        });

        // 註冊 RAGService 為單例
        $this->app->singleton(RAGService::class, function ($app) {
            return new RAGService();
        });

        // 註冊 ClassificationAgent 為單例
        $this->app->singleton(ClassificationAgent::class, function ($app) {
            return new ClassificationAgent(
                $app->make(OpenAIService::class),
                $app->make(SessionManager::class)
            );
        });

        // 註冊專業代理為單例
        $this->app->singleton(CourseAgent::class, function ($app) {
            return new CourseAgent(
                $app->make(OpenAIService::class),
                $app->make(SessionManager::class),
                $app->make(RAGService::class)
            );
        });

        $this->app->singleton(SubsidyAgent::class, function ($app) {
            return new SubsidyAgent(
                $app->make(OpenAIService::class),
                $app->make(SessionManager::class),
                $app->make(RAGService::class)
            );
        });

        $this->app->singleton(FAQAgent::class, function ($app) {
            return new FAQAgent(
                $app->make(OpenAIService::class),
                $app->make(SessionManager::class),
                $app->make(RAGService::class)
            );
        });

        $this->app->singleton(EnrollmentAgent::class, function ($app) {
            return new EnrollmentAgent(
                $app->make(OpenAIService::class),
                $app->make(SessionManager::class),
                $app->make(RAGService::class)
            );
        });

        $this->app->singleton(HumanServiceAgent::class, function ($app) {
            return new HumanServiceAgent(
                $app->make(OpenAIService::class),
                $app->make(SessionManager::class),
                $app->make(RAGService::class)
            );
        });
    }

    /**
     * 啟動服務
     */
    public function boot()
    {
        // 載入 Livewire 組件
        \Livewire\Livewire::component('chatbot-widget', \App\Http\Livewire\ChatbotWidget::class);
    }
}
