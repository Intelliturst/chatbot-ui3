<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class SessionManager
{
    protected $sessionKey = 'chatbot_session';
    protected $maxHistory = 20;

    /**
     * 初始化 Session
     */
    public function __construct()
    {
        if (!Session::has($this->sessionKey)) {
            $this->initializeSession();
        }
    }

    /**
     * 初始化新的 Session
     */
    protected function initializeSession()
    {
        Session::put($this->sessionKey, [
            'messages' => [],
            'context' => [],
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ]);
    }

    /**
     * 添加訊息到歷史記錄
     *
     * @param string $role 'user' 或 'assistant'
     * @param string $content 訊息內容
     * @return void
     */
    public function addMessage($role, $content)
    {
        $session = Session::get($this->sessionKey);

        $message = [
            'role' => $role,
            'content' => $content,
            'timestamp' => now()->toDateTimeString(),
        ];

        $session['messages'][] = $message;
        $session['updated_at'] = now()->toDateTimeString();

        // 限制歷史記錄數量
        if (count($session['messages']) > $this->maxHistory) {
            $session['messages'] = array_slice($session['messages'], -$this->maxHistory);
        }

        Session::put($this->sessionKey, $session);
    }

    /**
     * 獲取對話歷史
     *
     * @param int|null $limit 限制返回的訊息數量
     * @return array
     */
    public function getHistory($limit = null)
    {
        $session = Session::get($this->sessionKey);
        $messages = $session['messages'] ?? [];

        if ($limit !== null) {
            return array_slice($messages, -$limit);
        }

        return $messages;
    }

    /**
     * 設定上下文資訊
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setContext($key, $value)
    {
        $session = Session::get($this->sessionKey);
        $session['context'][$key] = $value;
        $session['updated_at'] = now()->toDateTimeString();
        Session::put($this->sessionKey, $session);
    }

    /**
     * 獲取上下文資訊
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function getContext($key = null, $default = null)
    {
        $session = Session::get($this->sessionKey);
        $context = $session['context'] ?? [];

        if ($key === null) {
            return $context;
        }

        return $context[$key] ?? $default;
    }

    /**
     * 清除整個 Session
     *
     * @return void
     */
    public function clearSession()
    {
        Session::forget($this->sessionKey);
        $this->initializeSession();
    }

    /**
     * 獲取 Session 資訊
     *
     * @return array
     */
    public function getSessionInfo()
    {
        $session = Session::get($this->sessionKey);

        return [
            'message_count' => count($session['messages'] ?? []),
            'created_at' => $session['created_at'] ?? null,
            'updated_at' => $session['updated_at'] ?? null,
            'has_context' => !empty($session['context']),
        ];
    }

    /**
     * 獲取最後一條訊息
     *
     * @return array|null
     */
    public function getLastMessage()
    {
        $messages = $this->getHistory();
        return !empty($messages) ? end($messages) : null;
    }

    /**
     * 獲取最後 N 條用戶訊息
     *
     * @param int $count
     * @return array
     */
    public function getLastUserMessages($count = 3)
    {
        $messages = $this->getHistory();
        $userMessages = array_filter($messages, function($msg) {
            return $msg['role'] === 'user';
        });

        return array_slice($userMessages, -$count);
    }
}
