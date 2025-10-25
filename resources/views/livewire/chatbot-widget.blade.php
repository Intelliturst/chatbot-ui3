<div>
    {{-- 浮動按鈕（未展開狀態） --}}
    @if(!$isOpen)
    <button
        wire:click="toggleWidget"
        class="fixed bottom-6 right-6 w-16 h-16 bg-gradient-to-br from-primary to-primary-dark
               rounded-full shadow-lg hover:shadow-2xl hover:scale-110
               transition-all duration-300 flex items-center justify-center z-50
               animate-bounce hover:animate-none group">
        <svg class="w-8 h-8 text-white group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
        </svg>
        {{-- 通知小圓點 --}}
        <span class="absolute top-0 right-0 w-3 h-3 bg-red-500 rounded-full border-2 border-white animate-pulse"></span>
    </button>
    @endif

    {{-- 聊天視窗（展開狀態） --}}
    @if($isOpen)
    <div
        x-data="{
            userInput: '',
            scrollToBottom() {
                setTimeout(() => {
                    this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                }, 100);
            },
            formatMessage(content) {
                // 基本 Markdown 格式化
                let formatted = content
                    .replace(/\*\*(.*?)\*\*/g, '<strong class=\'font-semibold\'>$1</strong>')
                    .replace(/^• (.*?)$/gm, '<li class=\'ml-4\'>$1</li>')
                    .replace(/^(\d+)\. (.*?)$/gm, '<li class=\'ml-4 list-decimal\'>$2</li>')
                    .replace(/\n/g, '<br>');
                return formatted;
            },
            sendMessage() {
                if (!this.userInput.trim()) return;

                const message = this.userInput;
                this.userInput = '';

                // 直接調用後端，讓 Livewire 自動處理重新渲染
                $wire.call('sendMessage', message);
            }
        }"
        x-init="scrollToBottom()"
        @widget-opened.window="scrollToBottom()"
        @scroll-to-bottom.window="scrollToBottom()"
        class="fixed bottom-0 right-0 md:bottom-6 md:right-6 w-full md:w-[420px]
               h-screen md:h-auto md:max-h-[650px] bg-white rounded-none md:rounded-3xl
               shadow-2xl flex flex-col overflow-hidden z-50
               animate-slide-in-up md:animate-slide-in-right border border-gray-200">

        {{-- Header --}}
        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-primary to-primary-dark text-white
                    rounded-t-none md:rounded-t-3xl flex-shrink-0 shadow-md">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <img src="/logo.png" alt="虹宇職訓" class="h-8 md:h-10 w-auto">
                    <span class="absolute -top-1 -right-1 w-3 h-3 bg-green-400 rounded-full border-2 border-white"></span>
                </div>
                <div>
                    <h2 class="text-base md:text-lg font-bold">虹宇職訓</h2>
                    <p class="text-xs text-white/80">智能客服助手</p>
                </div>
            </div>

            <div class="flex space-x-1">
                <button
                    wire:click="clearSession"
                    class="w-9 h-9 rounded-full hover:bg-white/20 transition-all duration-200
                           hover:scale-110 active:scale-95"
                    title="清除對話記錄">
                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
                <button
                    wire:click="toggleWidget"
                    class="w-9 h-9 rounded-full hover:bg-white/20 transition-all duration-200
                           hover:scale-110 active:scale-95 hover:rotate-90"
                    title="關閉">
                    <svg class="w-5 h-5 mx-auto transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Messages Area --}}
        <div x-ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gradient-to-b from-gray-50 to-white">
            @foreach($messages as $index => $message)
                <div wire:key="message-{{ $index }}">
                @if($message['role'] === 'user')
                    {{-- 用戶訊息 --}}
                    <div class="flex justify-end animate-slide-in-right"
                         style="animation-delay: {{ $index * 0.05 }}s">
                        <div class="bg-gradient-to-br from-gray-100 to-gray-200 text-gray-900
                                    px-4 py-3 rounded-2xl rounded-tr-md max-w-[80%]
                                    shadow-sm hover:shadow-md transition-shadow">
                            <p class="text-sm leading-relaxed whitespace-pre-line">{{ $message['content'] }}</p>
                            <span class="text-xs text-gray-500 mt-1 block">{{ $message['timestamp'] }}</span>
                        </div>
                    </div>
                @else
                    {{-- AI訊息 --}}
                    <div class="flex justify-start animate-slide-in-left"
                         style="animation-delay: {{ $index * 0.05 }}s">
                        <div class="w-9 h-9 rounded-full flex-shrink-0 mr-2 shadow-md
                                    bg-gradient-to-br from-primary to-primary-dark p-0.5">
                            <img src="/agent.png" alt="AI助手" class="w-full h-full rounded-full bg-white p-0.5">
                        </div>
                        <div class="flex-1 max-w-[80%]">
                            <div class="bg-white text-gray-900 px-4 py-3 rounded-2xl rounded-tl-md
                                        shadow-md border border-gray-100 hover:shadow-lg transition-shadow">
                                <div class="text-sm leading-relaxed message-content"
                                     x-html="formatMessage(`{{ addslashes($message['content']) }}`)">
                                </div>
                                <span class="text-xs text-gray-400 mt-2 block">{{ $message['timestamp'] }}</span>
                            </div>

                            {{-- 快速選項按鈕（僅最後一條訊息顯示） --}}
                            @if($loop->last && !empty($message['quick_options']))
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach($message['quick_options'] as $optionIndex => $option)
                                        <button
                                            wire:click="sendMessage('{{ addslashes($option) }}')"
                                            wire:loading.attr="disabled"
                                            wire:target="sendMessage"
                                            class="group inline-flex items-center px-4 py-2
                                                   bg-primary text-white rounded-xl text-sm font-medium
                                                   hover:bg-primary-dark hover:shadow-lg
                                                   transition-all duration-300
                                                   transform hover:scale-105 active:scale-95
                                                   disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none
                                                   animate-slide-in-up"
                                            style="animation-delay: {{ $optionIndex * 0.1 }}s">
                                            <svg class="w-4 h-4 mr-2 opacity-80 group-hover:opacity-100 transition-opacity"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M9 5l7 7-7 7"></path>
                                            </svg>
                                            {{ $option }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
                </div>
            @endforeach

            {{-- AI 思考動畫（對話泡泡設計，無文字） - 使用 wire:loading 自動控制 --}}
            <div wire:loading.flex
                 wire:target="sendMessage"
                 class="justify-start hidden animate-fade-in">
                    {{-- AI 頭像 --}}
                    <div class="w-9 h-9 rounded-full flex-shrink-0 mr-2 shadow-md
                                bg-gradient-to-br from-primary to-primary-dark p-0.5">
                        <img src="/agent.png" alt="AI助手" class="w-full h-full rounded-full bg-white p-0.5">
                    </div>

                    {{-- 思考動畫泡泡（無文字） --}}
                    <div class="bg-white px-5 py-4 rounded-2xl rounded-tl-md
                                shadow-md border border-gray-100">
                        <div class="flex space-x-2">
                            <div class="w-2.5 h-2.5 bg-primary rounded-full animate-bounce"></div>
                            <div class="w-2.5 h-2.5 bg-primary rounded-full animate-bounce"
                                 style="animation-delay: 0.1s"></div>
                            <div class="w-2.5 h-2.5 bg-primary rounded-full animate-bounce"
                                 style="animation-delay: 0.2s"></div>
                        </div>
                    </div>
            </div>
        </div>

        {{-- Input Area --}}
        <div class="p-4 bg-white border-t-2 border-gray-100 rounded-b-none md:rounded-b-3xl flex-shrink-0 shadow-inner">
            <div class="flex items-end space-x-2">
                <textarea
                    x-model="userInput"
                    wire:loading.attr="disabled"
                    wire:target="sendMessage"
                    rows="1"
                    placeholder="請輸入您的問題..."
                    class="flex-1 px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl
                           focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20
                           resize-none text-sm leading-relaxed
                           hover:border-gray-300 transition-colors
                           disabled:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-500"
                    x-on:keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); sendMessage(); }"
                ></textarea>

                <button
                    x-on:click="sendMessage()"
                    wire:loading.attr="disabled"
                    wire:target="sendMessage"
                    class="w-12 h-12 bg-gradient-to-br from-primary to-primary-dark text-white
                           rounded-xl hover:shadow-lg active:scale-95
                           transition-all duration-300 flex items-center justify-center flex-shrink-0
                           disabled:opacity-50 disabled:cursor-not-allowed disabled:rotate-0
                           hover:rotate-12">
                    <svg class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </div>

            <p class="text-xs text-gray-400 mt-2 text-center">
                按 Enter 發送 • Shift + Enter 換行
            </p>
        </div>
    </div>
    @endif

    {{-- 自定義樣式 --}}
    <style>
        @keyframes slide-in-left {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fade-in {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .animate-slide-in-left {
            animation: slide-in-left 0.3s ease-out forwards;
        }

        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }

        .message-content strong {
            font-weight: 600;
            color: #4F46E5;
        }

        .message-content li {
            margin-left: 1rem;
            margin-top: 0.25rem;
        }

        /* 滾動條樣式 */
        div[x-ref="messagesContainer"]::-webkit-scrollbar {
            width: 6px;
        }

        div[x-ref="messagesContainer"]::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        div[x-ref="messagesContainer"]::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        div[x-ref="messagesContainer"]::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</div>
