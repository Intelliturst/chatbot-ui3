<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>虹宇職訓智能客服系統 - Demo</title>

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <img src="/logo.png" alt="虹宇職訓" class="h-16 mx-auto mb-4">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">虹宇職訓智能客服系統</h1>
                <p class="text-gray-600">Phase 1 Demo - 彈出式聊天視窗</p>
            </div>

            <div class="prose max-w-none">
                <h2 class="text-xl font-semibold mb-4">功能展示</h2>
                <ul class="list-disc list-inside text-gray-700 space-y-2">
                    <li>✅ 彈出式聊天視窗（點擊右下角按鈕）</li>
                    <li>✅ 展開/收合動畫效果</li>
                    <li>✅ 響應式設計（RWD）支援</li>
                    <li>✅ Livewire 2.x 整合</li>
                    <li>✅ Tailwind CSS 3.x 樣式</li>
                    <li>✅ Alpine.js 互動效果</li>
                    <li>✅ 訊息顯示與輸入功能</li>
                    <li>✅ 快速選項按鈕</li>
                    <li>✅ 載入動畫效果</li>
                </ul>

                <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                    <h3 class="font-semibold text-blue-900 mb-2">使用說明</h3>
                    <p class="text-blue-800">點擊右下角的紫色圓形按鈕即可打開智能客服視窗。輸入問題後按 Enter 或點擊發送按鈕即可發送訊息。</p>
                </div>

                <div class="mt-4 p-4 bg-yellow-50 rounded-lg">
                    <h3 class="font-semibold text-yellow-900 mb-2">開發狀態</h3>
                    <p class="text-yellow-800">目前為 Phase 1 階段，OpenAI 整合將在 Phase 3 完成。現階段會返回測試回覆。</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Livewire 聊天機器人組件 -->
    @livewire('chatbot-widget')

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}"></script>

    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html>
