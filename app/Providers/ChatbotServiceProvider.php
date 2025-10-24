<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SessionManager;

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
