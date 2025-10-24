<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SessionManager;
use App\Services\OpenAIService;
use App\Services\Agents\ClassificationAgent;

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

        // 註冊 ClassificationAgent 為單例
        $this->app->singleton(ClassificationAgent::class, function ($app) {
            return new ClassificationAgent(
                $app->make(OpenAIService::class),
                $app->make(SessionManager::class)
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
