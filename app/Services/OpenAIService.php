<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.openai.com/v1';
    protected $defaultModel = 'gpt-3.5-turbo';
    protected $timeout = 30;

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY');
    }

    /**
     * 調用 OpenAI Chat API
     *
     * @param array $messages 訊息陣列
     * @param string|null $model 模型名稱
     * @param array $options 額外選項
     * @return array
     */
    public function chat($messages, $model = null, $options = [])
    {
        if (empty($this->apiKey)) {
            throw new \Exception('OpenAI API Key not configured');
        }

        $model = $model ?? $this->defaultModel;

        $payload = array_merge([
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 1500,
        ], $options);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout($this->timeout)
            ->post($this->baseUrl . '/chat/completions', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'content' => $data['choices'][0]['message']['content'] ?? '',
                    'usage' => $data['usage'] ?? [],
                    'model' => $data['model'] ?? $model,
                ];
            }

            Log::error('OpenAI API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => 'API request failed: ' . $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI API Exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * 快速調用（單一用戶訊息）
     *
     * @param string $userMessage
     * @param string|null $systemPrompt
     * @param string|null $model
     * @return string|null
     */
    public function quickChat($userMessage, $systemPrompt = null, $model = null)
    {
        $messages = [];

        if ($systemPrompt) {
            $messages[] = [
                'role' => 'system',
                'content' => $systemPrompt
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        $result = $this->chat($messages, $model);

        return $result['success'] ? $result['content'] : null;
    }

    /**
     * 調用分類模型（使用較便宜的 GPT-3.5）
     *
     * @param string $userMessage
     * @param string $systemPrompt
     * @return array
     */
    public function classify($userMessage, $systemPrompt)
    {
        return $this->chat([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage]
        ], 'gpt-3.5-turbo', [
            'temperature' => 0.3,  // 降低溫度提高一致性
            'max_tokens' => 100,   // 分類只需要短回覆
        ]);
    }

    /**
     * 調用代理模型（使用 GPT-4 或 GPT-3.5）
     *
     * @param array $messages
     * @param string|null $model
     * @return array
     */
    public function agent($messages, $model = null)
    {
        $model = $model ?? env('OPENAI_AGENT_MODEL', 'gpt-3.5-turbo');

        return $this->chat($messages, $model, [
            'temperature' => 0.7,
            'max_tokens' => 1500,
        ]);
    }

    /**
     * 測試 API 連接
     *
     * @return bool
     */
    public function testConnection()
    {
        $result = $this->quickChat('Hello', 'You are a helpful assistant.');
        return $result !== null;
    }
}
