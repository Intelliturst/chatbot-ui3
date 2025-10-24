<div>
    {{-- 浮動按鈕（未展開狀態） --}}
    @if(!$isOpen)
    <button
        wire:click="toggleWidget"
        class="fixed bottom-6 right-6 w-16 h-16 bg-primary rounded-full shadow-lg
               hover:bg-primary-dark transition-all duration-300 flex items-center justify-center z-50">
        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
        </svg>
    </button>
    @endif

    {{-- 聊天視窗（展開狀態） --}}
    @if($isOpen)
    <div
        x-data="{ scrollToBottom() { this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight; } }"
        x-init="scrollToBottom()"
        @widget-opened.window="scrollToBottom()"
        @scroll-to-bottom.window="scrollToBottom()"
        class="fixed bottom-0 right-0 md:bottom-6 md:right-6 w-full md:w-[400px]
               h-screen md:h-auto md:max-h-[600px] bg-white rounded-none md:rounded-2xl
               shadow-2xl flex flex-col overflow-hidden z-50
               animate-slide-in-up md:animate-slide-in-right">

        {{-- Header --}}
        <div class="flex items-center justify-between p-4 bg-primary text-white
                    rounded-t-none md:rounded-t-2xl flex-shrink-0">
            <div class="flex items-center space-x-3">
                <img src="/logo.png" alt="虹宇職訓" class="h-6 md:h-8 w-auto">
                <h2 class="text-base md:text-lg font-semibold">虹宇職訓</h2>
            </div>

            <div class="flex space-x-2">
                <button
                    wire:click="clearSession"
                    class="w-8 h-8 rounded-full hover:bg-white/20 transition-colors"
                    title="清除對話記錄">
                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
                <button wire:click="toggleWidget" class="w-8 h-8 rounded-full hover:bg-white/20 transition-colors" title="關閉">
                    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Messages Area --}}
        <div x-ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
            @foreach($messages as $message)
                @if($message['role'] === 'user')
                    {{-- 用戶訊息 --}}
                    <div class="flex justify-end">
                        <div class="bg-gray-100 text-gray-900 px-4 py-3 rounded-2xl rounded-tr-sm max-w-[80%]">
                            <p class="text-sm whitespace-pre-line">{{ $message['content'] }}</p>
                            <span class="text-xs text-gray-500 mt-1 block">{{ $message['timestamp'] }}</span>
                        </div>
                    </div>
                @else
                    {{-- AI訊息 --}}
                    <div class="flex justify-start">
                        <div class="w-8 h-8 rounded-full flex-shrink-0 mr-2">
                            <img src="/agent.png" alt="AI助手" class="w-full h-full">
                        </div>
                        <div class="bg-primary/10 text-gray-900 px-4 py-3 rounded-2xl rounded-tl-sm max-w-[80%]">
                            <p class="text-sm whitespace-pre-line">{{ $message['content'] }}</p>
                            <span class="text-xs text-gray-500 mt-1 block">{{ $message['timestamp'] }}</span>

                            {{-- 快速選項按鈕 --}}
                            @if(!empty($message['quick_options']))
                                <div class="mt-3 space-y-2">
                                    @foreach($message['quick_options'] as $option)
                                        <button
                                            wire:click="selectOption('{{ $option }}')"
                                            class="w-full text-left bg-white hover:bg-gray-50 px-3 py-2
                                                   rounded-lg text-sm text-gray-700 transition-colors border border-gray-200">
                                            {{ $option }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach

            {{-- Loading --}}
            @if($isLoading)
                <div class="flex justify-start">
                    <div class="bg-primary/10 px-4 py-3 rounded-2xl rounded-tl-sm">
                        <div class="flex space-x-2">
                            <div class="w-2 h-2 bg-primary rounded-full animate-bounce"></div>
                            <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                            <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Input Area --}}
        <div class="p-4 bg-white border-t border-gray-200 rounded-b-none md:rounded-b-2xl flex-shrink-0">
            <div class="flex items-end space-x-2">
                <textarea
                    wire:model.defer="userInput"
                    rows="1"
                    placeholder="請輸入您的問題..."
                    class="flex-1 px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl
                           focus:outline-none focus:border-primary resize-none text-sm"
                    wire:keydown.enter.prevent="sendMessage"
                ></textarea>

                <button
                    wire:click="sendMessage"
                    wire:loading.attr="disabled"
                    class="w-12 h-12 bg-primary text-white rounded-xl hover:bg-primary-dark
                           transition-colors flex items-center justify-center flex-shrink-0
                           disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
